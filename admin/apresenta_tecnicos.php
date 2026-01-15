<?php
session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

// PROTEÃ‡ÃƒO: Apenas administradores podem acessar
requireAdmin();

// Verificar autenticação de admin
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: ../publics/login.php?erro=acesso_negado');
    exit();
}

$conn = getConnection();

// Buscar todos os técnicos com estatísticas
$sql = "
    SELECT
        t.*,
        COUNT(DISTINCT c.id) as total_chamados,
        COUNT(DISTINCT CASE WHEN c.status IN ('aberto', 'em andamento') THEN c.id END) as chamados_abertos,
        COUNT(DISTINCT CASE WHEN c.status = 'resolvido' THEN c.id END) as chamados_resolvidos,
        MAX(c.data_atualizacao) as ultima_atividade
    FROM tecnicos t
    LEFT JOIN chamados c ON t.id = c.tecnico_id
    GROUP BY t.id
    ORDER BY t.nome ASC
";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Técnicos - NetoNerd Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .main-container {
            padding: 30px 0;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            margin-bottom: 30px;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .stats-box {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 8px;
            text-align: center;
            margin: 5px;
        }
        .stats-box strong {
            display: block;
            font-size: 24px;
            color: #667eea;
        }
        .stats-box small {
            color: #666;
        }
        .tech-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <?php if(file_exists('../routes/header_admin.php')) include '../routes/header_admin.php'; ?>

    <div class="container main-container">
        <div class="row mb-4">
            <!-- Cards de Estatísticas Gerais -->
            <?php
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM tecnicos WHERE Ativo = 1");
            $stmt->execute();
            $total_ativos = $stmt->get_result()->fetch_assoc()['total'];
            $stmt->close();

            $stmt = $conn->prepare("SELECT COUNT(DISTINCT c.id) as total FROM chamados c WHERE c.tecnico_id IS NOT NULL");
            $stmt->execute();
            $total_atribuidos = $stmt->get_result()->fetch_assoc()['total'];
            $stmt->close();
            ?>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body stats-box">
                        <strong><?php echo $total_ativos; ?></strong>
                        <small>Técnicos Ativos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body stats-box">
                        <strong><?php echo $total_atribuidos; ?></strong>
                        <small>Chamados Atribuídos</small>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body stats-box">
                        <strong><?php echo $result->num_rows; ?></strong>
                        <small>Total de Técnicos</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">
                            <i class="fas fa-users"></i> Técnicos do Sistema
                        </h2>
                        <a href="adicionar_tecnico.php" class="btn btn-light">
                            <i class="fas fa-plus"></i> Adicionar Técnico
                        </a>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['sucesso'])): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="fas fa-check-circle"></i>
                                <?php
                                switch ($_GET['sucesso']) {
                                    case 'adicionado':
                                        echo 'Técnico adicionado com sucesso!';
                                        break;
                                    case 'atualizado':
                                        echo 'Técnico atualizado com sucesso!';
                                        break;
                                    case 'desativado':
                                        echo 'Técnico desativado!';
                                        break;
                                    case 'ativado':
                                        echo 'Técnico ativado!';
                                        break;
                                }
                                ?>
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        <?php endif; ?>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Técnico</th>
                                        <th>Matrícula</th>
                                        <th>Contato</th>
                                        <th>Estatísticas</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $result->data_seek(0); // Reset pointer ?>
                                    <?php while ($tecnico = $result->fetch_assoc()): ?>
                                        <?php
                                        $iniciais = '';
                                        $nome_partes = explode(' ', $tecnico['nome']);
                                        foreach (array_slice($nome_partes, 0, 2) as $parte) {
                                            $iniciais .= strtoupper(substr($parte, 0, 1));
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="tech-avatar"><?php echo $iniciais; ?></div>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($tecnico['nome']); ?></strong>
                                                        <?php if ($tecnico['carro_do_dia']): ?>
                                                            <br><small class="text-muted">
                                                                <i class="fas fa-car"></i> <?php echo htmlspecialchars($tecnico['carro_do_dia']); ?>
                                                            </small>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-secondary">
                                                    <?php echo htmlspecialchars($tecnico['matricula']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <small>
                                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($tecnico['email']); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="d-flex">
                                                    <div class="stats-box">
                                                        <strong><?php echo $tecnico['total_chamados']; ?></strong>
                                                        <small>Total</small>
                                                    </div>
                                                    <div class="stats-box">
                                                        <strong><?php echo $tecnico['chamados_abertos']; ?></strong>
                                                        <small>Abertos</small>
                                                    </div>
                                                    <div class="stats-box">
                                                        <strong><?php echo $tecnico['chamados_resolvidos']; ?></strong>
                                                        <small>Resolvidos</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($tecnico['Ativo']): ?>
                                                    <span class="badge badge-success">Ativo</span>
                                                <?php else: ?>
                                                    <span class="badge badge-danger">Inativo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="editar_tecnico.php?id=<?php echo $tecnico['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="../tecnico/painelTecnicoCliente.php?tecnico_id=<?php echo $tecnico['id']; ?>"
                                                   class="btn btn-sm btn-primary"
                                                   title="Ver chamados">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
