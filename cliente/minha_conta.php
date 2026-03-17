<?php 
require_once "../controller/validador_acesso.php";
include '../config/bandoDeDados/conexao.php';

$conn = getConnection();
$usuario_id = $_SESSION['id'];

// 1. BUSCAR DADOS DO CLIENTE
$stmt = $conn->prepare("SELECT nome, email, telefone, endereco, complemento, cep, genero, data_criacao FROM clientes WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();
$stmt->close();

// 2. BUSCAR ESTATÍSTICAS DE CHAMADOS (Lógica que faltava)
$stats = ['total' => 0, 'ativos' => 0, 'resolvidos' => 0, 'cancelados' => 0];
$sql_stats = "SELECT status, COUNT(*) as qtd FROM chamados WHERE cliente_id = ? GROUP BY status";
$stmt_s = $conn->prepare($sql_stats);
$stmt_s->bind_param("i", $usuario_id);
$stmt_s->execute();
$res_stats = $stmt_s->get_result();
while ($row = $res_stats->fetch_assoc()) {
    $stats['total'] += $row['qtd'];
    if (in_array($row['status'], ['aberto', 'em andamento', 'pendente'])) $stats['ativos'] += $row['qtd'];
    if ($row['status'] === 'resolvido' || $row['status'] === 'concluido') $stats['resolvidos'] += $row['qtd'];
    if ($row['status'] === 'cancelado') $stats['cancelados'] += $row['qtd'];
}
$stmt_s->close();

// 3. PROCESSAR ATUALIZAÇÃO DE DADOS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_dados'])) {
    $nome = trim($_POST['nome']);
    $telefone = trim($_POST['telefone']);
    $endereco = trim($_POST['endereco']);
    $complemento = trim($_POST['complemento']);
    $cep = trim($_POST['cep']);
    
    $stmt = $conn->prepare("UPDATE clientes SET nome = ?, telefone = ?, endereco = ?, complemento = ?, cep = ? WHERE id = ?");
    $stmt->bind_param("sssssi", $nome, $telefone, $endereco, $complemento, $cep, $usuario_id);
    
    if ($stmt->execute()) {
        header("Location: minha_conta.php?sucesso=1");
        exit();
    } else {
        $mensagem_erro = "Erro ao atualizar dados.";
    }
    $stmt->close();
}

// 4. PROCESSAR ALTERAÇÃO DE SENHA (SEGURANÇA CORRIGIDA)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['alterar_senha'])) {
    $senha_atual = $_POST['senha_atual'];
    $senha_nova = $_POST['senha_nova'];
    $senha_confirma = $_POST['senha_confirma'];
    
    $stmt = $conn->prepare("SELECT senha_hash FROM clientes WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $dados = $stmt->get_result()->fetch_assoc();
    
    // IMPORTANTE: Usar password_verify para checar a senha criptografada
    if (password_verify($senha_atual, $dados['senha_hash'])) {
        if ($senha_nova === $senha_confirma) {
            $novo_hash = password_hash($senha_nova, PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE clientes SET senha_hash = ? WHERE id = ?");
            $upd->bind_param("si", $novo_hash, $usuario_id);
            if ($upd->execute()) {
                header("Location: minha_conta.php?senha_alterada=1");
                exit();
            }
        } else {
            $erro_senha = "As novas senhas não coincidem.";
        }
    } else {
        $erro_senha = "Senha atual incorreta.";
    }
}

require_once '../includes/header.php';
?>

<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <div class="nn-card nn-animate-fade mb-4">
            <div class="nn-card-header d-flex align-items-center">
                <div class="nn-avatar-circle me-3">
                    <?php echo strtoupper(substr($cliente['nome'], 0, 1)); ?>
                </div>
                <div>
                    <h1 class="nn-card-title mb-0">
                        <?php echo ($cliente['genero'] === 'Feminino' ? 'Bem-vinda, ' : 'Bem-vindo, ') . htmlspecialchars(explode(' ', $cliente['nome'])[0]); ?>!
                    </h1>
                    <p class="text-muted mb-0 small">
                        <i class="fas fa-calendar-alt"></i> Cliente desde <?php echo date('d/m/Y', strtotime($cliente['data_criacao'])); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="nn-stats-grid nn-animate-slide mb-4">
            <div class="nn-stat-card primary">
                <div class="nn-stat-icon primary"><i class="fas fa-ticket-alt"></i></div>
                <div class="nn-stat-value"><?php echo $stats['total']; ?></div>
                <div class="nn-stat-label">Total de Chamados</div>
            </div>
            <div class="nn-stat-card warning">
                <div class="nn-stat-icon warning"><i class="fas fa-clock"></i></div>
                <div class="nn-stat-value"><?php echo $stats['ativos']; ?></div>
                <div class="nn-stat-label">Em Aberto</div>
            </div>
            <div class="nn-stat-card success">
                <div class="nn-stat-icon success"><i class="fas fa-check-circle"></i></div>
                <div class="nn-stat-value"><?php echo $stats['resolvidos']; ?></div>
                <div class="nn-stat-label">Resolvidos</div>
            </div>
            <div class="nn-stat-card danger">
                <div class="nn-stat-icon danger"><i class="fas fa-times-circle"></i></div>
                <div class="nn-stat-value"><?php echo $stats['cancelados']; ?></div>
                <div class="nn-stat-label">Cancelados</div>
            </div>
        </div>

        <?php if (isset($_GET['sucesso'])): ?>
            <div class="alert nn-alert-success"><i class="fas fa-check"></i> Dados atualizados com sucesso!</div>
        <?php endif; ?>
        <?php if (isset($erro_senha)): ?>
            <div class="alert nn-alert-danger"><i class="fas fa-exclamation-triangle"></i> <?php echo $erro_senha; ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="nn-card">
                    <div class="nn-card-header">
                        <h3 class="nn-card-title"><i class="fas fa-user-edit"></i> Meus Dados Pessoais</h3>
                    </div>
                    <div class="nn-card-body">
                        <form method="POST">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="nn-form-label">Nome Completo</label>
                                    <input type="text" name="nome" class="nn-form-control" value="<?php echo htmlspecialchars($cliente['nome']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="nn-form-label">Email (Login)</label>
                                    <input type="email" class="nn-form-control bg-light" value="<?php echo htmlspecialchars($cliente['email']); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="nn-form-label">Telefone</label>
                                    <input type="text" name="telefone" class="nn-form-control" value="<?php echo htmlspecialchars($cliente['telefone']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="nn-form-label">CEP</label>
                                    <input type="text" name="cep" class="nn-form-control" value="<?php echo htmlspecialchars($cliente['cep']); ?>">
                                </div>
                                <div class="col-12">
                                    <label class="nn-form-label">Endereço</label>
                                    <input type="text" name="endereco" class="nn-form-control" value="<?php echo htmlspecialchars($cliente['endereco']); ?>">
                                </div>
                                <div class="col-12">
                                    <label class="nn-form-label">Complemento</label>
                                    <input type="text" name="complemento" class="nn-form-control" value="<?php echo htmlspecialchars($cliente['complemento']); ?>">
                                </div>
                            </div>
                            <button type="submit" name="atualizar_dados" class="nn-btn nn-btn-primary mt-4">
                                <i class="fas fa-save"></i> Salvar Alterações
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="nn-card border-danger">
                    <div class="nn-card-header">
                        <h3 class="nn-card-title"><i class="fas fa-shield-alt"></i> Segurança</h3>
                    </div>
                    <div class="nn-card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label class="nn-form-label">Senha Atual</label>
                                <input type="password" name="senha_atual" class="nn-form-control" required>
                            </div>
                            <hr>
                            <div class="mb-3">
                                <label class="nn-form-label">Nova Senha</label>
                                <input type="password" name="senha_nova" class="nn-form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="nn-form-label">Confirmar Nova Senha</label>
                                <input type="password" name="senha_confirma" class="nn-form-control" required>
                            </div>
                            <button type="submit" name="alterar_senha" class="nn-btn nn-btn-secondary w-100">
                                <i class="fas fa-key"></i> Alterar Senha
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
/* Ajustes Finos de Estilo */
.nn-avatar-circle {
    width: 60px;
    height: 60px;
    background: var(--gradient-primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: bold;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
}
.nn-alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
.nn-alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
</style>

<?php require_once '../includes/footer.php'; ?>