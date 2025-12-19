<?php 
require_once "../controller/validador_acesso.php";
include '../config/bandoDeDados/conexao.php';

$conn = getConnection();
$usuario_id = $_SESSION['id'];

// Buscar dados do cliente
$stmt = $conn->prepare("SELECT nome, email, telefone, endereco, complemento, cep, genero, data_criacao FROM clientes WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$cliente = $result->fetch_assoc();
$stmt->close();

// Processar atualização de dados
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_dados'])) {
    $nome = trim($_POST['nome']);
    $telefone = trim($_POST['telefone']);
    $endereco = trim($_POST['endereco']);
    $complemento = trim($_POST['complemento']);
    $cep = trim($_POST['cep']);
    
    $stmt = $conn->prepare("UPDATE clientes SET nome = ?, telefone = ?, endereco = ?, complemento = ?, cep = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $nome, $telefone, $endereco, $complemento, $cep, $usuario_id);
    
    if ($stmt->execute()) {
        $mensagem_sucesso = "Dados atualizados com sucesso!";
        // Recarregar dados
        header("Location: minha_conta.php?sucesso=1");
        exit();
    } else {
        $mensagem_erro = "Erro ao atualizar dados.";
    }
    $stmt->close();
}

// Processar alteração de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alterar_senha'])) {
    $senha_atual = $_POST['senha_atual'];
    $senha_nova = $_POST['senha_nova'];
    $senha_confirma = $_POST['senha_confirma'];
    
    // Verificar senha atual
    $stmt = $conn->prepare("SELECT senha_hash FROM clientes WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $dados = $result->fetch_assoc();
    
    if ($senha_atual === $dados['senha_hash']) {
        if ($senha_nova === $senha_confirma) {
            $senha_hash = password_hash($senha_nova, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE clientes SET senha_hash = ? WHERE id = ?");
            $stmt->bind_param("si", $senha_hash, $usuario_id);
            
            if ($stmt->execute()) {
                header("Location: minha_conta.php?senha_alterada=1");
                exit();
            }
        } else {
            $erro_senha = "As senhas não coincidem.";
        }
    } else {
        $erro_senha = "Senha atual incorreta.";
    }
    $stmt->close();
}

// $conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta - NetoNerd</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <style>
        .account-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .account-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .avatar {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 2rem;
            font-weight: bold;
            margin-right: 20px;
        }
        
        .info-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            border-left: 4px solid #667eea;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }
        
        .info-value {
            color: #212529;
            font-size: 1.1rem;
        }
        
        .section-title {
            color: #667eea;
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .section-title::before {
            content: "";
            width: 4px;
            height: 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin-right: 10px;
            border-radius: 2px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
            border-top: 3px solid #667eea;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 5px;
        }
        
        .alert-custom {
            border-radius: 10px;
            border: none;
            padding: 15px 20px;
        }
        
        .btn-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
            color: white;
        }

        /* nav-bar */
        .logo{
            width: 90px;
            height: 90px;
            /* object-fit: contain; */
            margin-bottom: 30px;
        }
        .top-navbar {
            background: linear-gradient(135deg, #007bff, #0056b3);
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .top-navbar .container {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            color: white;
        }
    </style>
</head>
<body>
    <div class="top-navbar">
        <div class="container">
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($cliente['nome'], 0, 0)); ?>
                </div>
                <div class="user-details">
                    <h6>
                        <?php 
                        echo ($cliente['genero'] === 'Feminino' ? 'Bem-vinda, ' : 'Bem-vindo, ') . 
                             htmlspecialchars(explode(' ', $cliente['nome'])[0]); 
                        ?>
                    </h6>
                    <small><?php echo htmlspecialchars($cliente['email']); ?></small>
                </div>
            </div>
            <div>
                <a href="logoff.php" class="btn btn-light btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <?php if (isset($_GET['sucesso'])): ?>
            <div class="alert alert-success alert-custom">
                ✓ Dados atualizados com sucesso!
            </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['senha_alterada'])): ?>
            <div class="alert alert-success alert-custom">
                ✓ Senha alterada com sucesso!
            </div>
        <?php endif; ?>
        
        <?php if (isset($erro_senha)): ?>
            <div class="alert alert-danger alert-custom">
                ✗ <?php echo htmlspecialchars($erro_senha); ?>
            </div>
        <?php endif; ?>

        <!-- Header da Conta -->
        <div class="account-section">
            <div class="account-header">
                <div class="avatar">
                    <?php echo strtoupper(substr($cliente['nome'], 0, 1)); ?>
                </div>
                <div>
                    <h2 style="margin: 0; color: #212529;"><?php echo htmlspecialchars($cliente['nome']); ?></h2>
                    <p style="margin: 0; color: #6c757d;">Cliente desde <?php echo date('d/m/Y', strtotime($cliente['data_criacao'])); ?></p>
                </div>
            </div>

            <!-- Estatísticas -->
            <?php
            $conn = getConnection();
            
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM chamados WHERE cliente_id = ?");
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $total_chamados = $stmt->get_result()->fetch_assoc()['total'];
            
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM chamados WHERE cliente_id = ? AND status = 'resolvido'");
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $chamados_resolvidos = $stmt->get_result()->fetch_assoc()['total'];
            
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM chamados WHERE cliente_id = ? AND status IN ('aberto', 'em andamento')");
            $stmt->bind_param("i", $usuario_id);
            $stmt->execute();
            $chamados_ativos = $stmt->get_result()->fetch_assoc()['total'];
            
            $stmt->close();
            $conn->close();
            ?>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_chamados; ?></div>
                    <div class="stat-label">Total de Chamados</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $chamados_ativos; ?></div>
                    <div class="stat-label">Chamados Ativos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $chamados_resolvidos; ?></div>
                    <div class="stat-label">Chamados Resolvidos</div>
                </div>
            </div>
        </div>

        <!-- Dados Pessoais -->
        <div class="account-section">
            <h3 class="section-title">Dados Pessoais</h3>
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="info-label">Nome Completo</label>
                            <input type="text" name="nome" class="form-control" value="<?php echo htmlspecialchars($cliente['nome']); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="info-label">Email</label>
                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($cliente['email']); ?>" disabled>
                            <small class="text-muted">O email não pode ser alterado</small>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="info-label">Telefone</label>
                            <input type="text" name="telefone" class="form-control" value="<?php echo htmlspecialchars($cliente['telefone']); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="info-label">CEP</label>
                            <input type="text" name="cep" class="form-control" value="<?php echo htmlspecialchars($cliente['cep']); ?>">
                        </div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="info-label">Endereço</label>
                    <input type="text" name="endereco" class="form-control" value="<?php echo htmlspecialchars($cliente['endereco']); ?>">
                </div>
                
                <div class="form-group">
                    <label class="info-label">Complemento</label>
                    <input type="text" name="complemento" class="form-control" value="<?php echo htmlspecialchars($cliente['complemento']); ?>">
                </div>
                
                <button type="submit" name="atualizar_dados" class="btn btn-custom">
                    Salvar Alterações
                </button>
            </form>
        </div>

        <!-- Alteração de Senha -->
        <div class="account-section">
            <h3 class="section-title">Segurança</h3>
            <form method="POST">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="info-label">Senha Atual</label>
                            <input type="password" name="senha_atual" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="info-label">Nova Senha</label>
                            <input type="password" name="senha_nova" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="info-label">Confirmar Nova Senha</label>
                            <input type="password" name="senha_confirma" class="form-control" required>
                        </div>
                    </div>
                </div>
                
                <button type="submit" name="alterar_senha" class="btn btn-custom">
                    Alterar Senha
                </button>
            </form>
        </div>
    </div>

    <footer class="bg-primary text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-2">© 2025 NetoNerd - Todos os direitos reservados</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>