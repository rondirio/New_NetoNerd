<?php
session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

// PROTEÇÃO: Apenas administradores podem acessar
requireAdmin();

// Verificar autenticação de admin
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: ../publics/login.php?erro=acesso_negado');
    exit();
}

$conn = getConnection();

// Buscar todas as categorias
$sql = "SELECT * FROM categorias_chamado ORDER BY nome ASC";
$result = $conn->query($sql);

// Contar chamados por categoria
$sql_stats = "
    SELECT
        cat.id,
        cat.nome,
        COUNT(c.id) as total_chamados,
        COUNT(CASE WHEN c.status IN ('aberto', 'em andamento') THEN 1 END) as chamados_abertos
    FROM categorias_chamado cat
    LEFT JOIN chamados c ON cat.id = c.categoria_id
    GROUP BY cat.id, cat.nome
";
$stats = $conn->query($sql_stats);
$categoria_stats = [];
while ($stat = $stats->fetch_assoc()) {
    $categoria_stats[$stat['id']] = $stat;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Categorias - NetoNerd Admin</title>
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
        .categoria-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            color: white;
            font-weight: bold;
            margin-right: 10px;
        }
        .btn-categoria {
            border-radius: 20px;
            padding: 8px 20px;
            margin: 5px;
        }
        .stats-badge {
            background: #f8f9fa;
            padding: 8px 15px;
            border-radius: 10px;
            margin: 5px;
            display: inline-block;
        }
    </style>
</head>
<body>
    <?php include '../routes/header_admin.php'; ?>

    <div class="container main-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">
                            <i class="fas fa-tags"></i> Gerenciar Categorias
                        </h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['sucesso'])): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <?php
                                switch ($_GET['sucesso']) {
                                    case 'criada':
                                        echo '<i class="fas fa-check-circle"></i> Categoria criada com sucesso!';
                                        break;
                                    case 'atualizada':
                                        echo '<i class="fas fa-check-circle"></i> Categoria atualizada com sucesso!';
                                        break;
                                    case 'excluida':
                                        echo '<i class="fas fa-check-circle"></i> Categoria excluída com sucesso!';
                                        break;
                                    case 'ativada':
                                        echo '<i class="fas fa-check-circle"></i> Categoria ativada!';
                                        break;
                                    case 'desativada':
                                        echo '<i class="fas fa-check-circle"></i> Categoria desativada!';
                                        break;
                                }
                                ?>
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($_GET['erro'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-exclamation-triangle"></i>
                                <?php
                                switch ($_GET['erro']) {
                                    case 'nome_duplicado':
                                        echo 'Já existe uma categoria com este nome!';
                                        break;
                                    case 'em_uso':
                                        echo 'Não é possível excluir esta categoria pois existem chamados vinculados a ela!';
                                        break;
                                    default:
                                        echo 'Erro ao processar a operação. Tente novamente.';
                                }
                                ?>
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        <?php endif; ?>

                        <div class="mb-4">
                            <button class="btn btn-primary btn-categoria" data-toggle="modal" data-target="#modalNovaCategoria">
                                <i class="fas fa-plus"></i> Nova Categoria
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Categoria</th>
                                        <th>Descrição</th>
                                        <th>Estatísticas</th>
                                        <th>Status</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($categoria = $result->fetch_assoc()): ?>
                                        <?php
                                        $stats_cat = $categoria_stats[$categoria['id']] ?? ['total_chamados' => 0, 'chamados_abertos' => 0];
                                        ?>
                                        <tr>
                                            <td>
                                                <span class="categoria-badge" style="background: <?php echo htmlspecialchars($categoria['cor']); ?>">
                                                    <i class="fas <?php echo htmlspecialchars($categoria['icone']); ?>"></i>
                                                    <?php echo htmlspecialchars($categoria['nome']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo htmlspecialchars($categoria['descricao']); ?></td>
                                            <td>
                                                <div class="stats-badge">
                                                    <strong><?php echo $stats_cat['total_chamados']; ?></strong> total
                                                </div>
                                                <div class="stats-badge">
                                                    <strong><?php echo $stats_cat['chamados_abertos']; ?></strong> abertos
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($categoria['ativo']): ?>
                                                    <span class="badge badge-success">Ativo</span>
                                                <?php else: ?>
                                                    <span class="badge badge-secondary">Inativo</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-info" onclick="editarCategoria(<?php echo htmlspecialchars(json_encode($categoria)); ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($categoria['ativo']): ?>
                                                    <a href="processar_categoria.php?acao=desativar&id=<?php echo $categoria['id']; ?>"
                                                       class="btn btn-sm btn-warning"
                                                       onclick="return confirm('Desativar esta categoria?')">
                                                        <i class="fas fa-ban"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <a href="processar_categoria.php?acao=ativar&id=<?php echo $categoria['id']; ?>"
                                                       class="btn btn-sm btn-success">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($stats_cat['total_chamados'] == 0): ?>
                                                    <a href="processar_categoria.php?acao=excluir&id=<?php echo $categoria['id']; ?>"
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Tem certeza que deseja excluir esta categoria?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                <?php endif; ?>
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

    <!-- Modal Nova Categoria -->
    <div class="modal fade" id="modalNovaCategoria" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Categoria</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="processar_categoria.php" method="POST">
                    <input type="hidden" name="acao" value="criar">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nome *</label>
                            <input type="text" name="nome" class="form-control" required maxlength="100">
                        </div>
                        <div class="form-group">
                            <label>Descrição</label>
                            <textarea name="descricao" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Cor</label>
                                <input type="color" name="cor" class="form-control" value="#007bff">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Ícone (Font Awesome)</label>
                                <input type="text" name="icone" class="form-control" value="fa-ticket" placeholder="fa-ticket">
                                <small class="form-text text-muted">Ex: fa-desktop, fa-wifi, fa-envelope</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Criar Categoria</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar Categoria -->
    <div class="modal fade" id="modalEditarCategoria" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Categoria</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="processar_categoria.php" method="POST">
                    <input type="hidden" name="acao" value="editar">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Nome *</label>
                            <input type="text" name="nome" id="edit_nome" class="form-control" required maxlength="100">
                        </div>
                        <div class="form-group">
                            <label>Descrição</label>
                            <textarea name="descricao" id="edit_descricao" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label>Cor</label>
                                <input type="color" name="cor" id="edit_cor" class="form-control">
                            </div>
                            <div class="form-group col-md-6">
                                <label>Ícone (Font Awesome)</label>
                                <input type="text" name="icone" id="edit_icone" class="form-control" placeholder="fa-ticket">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editarCategoria(categoria) {
            $('#edit_id').val(categoria.id);
            $('#edit_nome').val(categoria.nome);
            $('#edit_descricao').val(categoria.descricao);
            $('#edit_cor').val(categoria.cor);
            $('#edit_icone').val(categoria.icone);
            $('#modalEditarCategoria').modal('show');
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
