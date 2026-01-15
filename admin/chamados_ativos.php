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

// Filtros
$filtro_status = $_GET['status'] ?? '';
$filtro_prioridade = $_GET['prioridade'] ?? '';
$filtro_categoria = $_GET['categoria'] ?? '';
$filtro_tecnico = $_GET['tecnico'] ?? '';
$busca = $_GET['busca'] ?? '';

// Construir query com filtros
$sql = "
    SELECT
        c.*,
        cl.nome as cliente_nome,
        cl.email as cliente_email,
        t.nome as tecnico_nome,
        t.matricula as tecnico_matricula,
        cat.nome as categoria_nome,
        cat.cor as categoria_cor,
        cat.icone as categoria_icone
    FROM chamados c
    INNER JOIN clientes cl ON c.cliente_id = cl.id
    LEFT JOIN tecnicos t ON c.tecnico_id = t.id
    LEFT JOIN categorias_chamado cat ON c.categoria_id = cat.id
    WHERE 1=1
";

$params = [];
$types = '';

if ($filtro_status) {
    $sql .= " AND c.status = ?";
    $params[] = $filtro_status;
    $types .= 's';
}

if ($filtro_prioridade) {
    $sql .= " AND c.prioridade = ?";
    $params[] = $filtro_prioridade;
    $types .= 's';
}

if ($filtro_categoria) {
    $sql .= " AND c.categoria_id = ?";
    $params[] = intval($filtro_categoria);
    $types .= 'i';
}

if ($filtro_tecnico) {
    $sql .= " AND c.tecnico_id = ?";
    $params[] = intval($filtro_tecnico);
    $types .= 'i';
}

if ($busca) {
    $sql .= " AND (c.titulo LIKE ? OR c.descricao LIKE ? OR c.protocolo LIKE ?)";
    $busca_like = "%$busca%";
    $params[] = $busca_like;
    $params[] = $busca_like;
    $params[] = $busca_like;
    $types .= 'sss';
}

$sql .= " ORDER BY
    CASE c.prioridade
        WHEN 'critica' THEN 1
        WHEN 'alta' THEN 2
        WHEN 'media' THEN 3
        WHEN 'baixa' THEN 4
    END,
    c.data_abertura DESC
";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Buscar categorias para filtro
$categorias = $conn->query("SELECT * FROM categorias_chamado WHERE ativo = 1 ORDER BY nome");

// Buscar técnicos para filtro
$tecnicos = $conn->query("SELECT id, nome, matricula FROM tecnicos WHERE Ativo = 1 ORDER BY nome");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chamados Ativos - NetoNerd Admin</title>
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
            margin-bottom: 20px;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .chamado-row {
            border-left: 5px solid #667eea;
            padding: 15px;
            margin: 10px 0;
            background: #f8f9fa;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .chamado-row:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        .prioridade-critica { border-left-color: #dc3545; }
        .prioridade-alta { border-left-color: #fd7e14; }
        .prioridade-media { border-left-color: #ffc107; }
        .prioridade-baixa { border-left-color: #28a745; }
        .categoria-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 15px;
            color: white;
            font-size: 12px;
        }
        .filtros-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <?php if(file_exists('../routes/header_admin.php')) include '../routes/header_admin.php'; ?>

    <div class="container-fluid main-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">
                            <i class="fas fa-ticket-alt"></i> Todos os Chamados
                        </h2>
                    </div>
                    <div class="card-body">
                        <!-- Filtros -->
                        <div class="filtros-box">
                            <form method="GET" class="row">
                                <div class="col-md-3">
                                    <input type="text" name="busca" class="form-control" placeholder="Buscar por protocolo, título..." value="<?php echo htmlspecialchars($busca); ?>">
                                </div>
                                <div class="col-md-2">
                                    <select name="status" class="form-control">
                                        <option value="">Todos os Status</option>
                                        <option value="aberto" <?php echo $filtro_status === 'aberto' ? 'selected' : ''; ?>>Aberto</option>
                                        <option value="em andamento" <?php echo $filtro_status === 'em andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                                        <option value="pendente" <?php echo $filtro_status === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                        <option value="resolvido" <?php echo $filtro_status === 'resolvido' ? 'selected' : ''; ?>>Resolvido</option>
                                        <option value="cancelado" <?php echo $filtro_status === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="prioridade" class="form-control">
                                        <option value="">Todas Prioridades</option>
                                        <option value="critica" <?php echo $filtro_prioridade === 'critica' ? 'selected' : ''; ?>>Crítica</option>
                                        <option value="alta" <?php echo $filtro_prioridade === 'alta' ? 'selected' : ''; ?>>Alta</option>
                                        <option value="media" <?php echo $filtro_prioridade === 'media' ? 'selected' : ''; ?>>Média</option>
                                        <option value="baixa" <?php echo $filtro_prioridade === 'baixa' ? 'selected' : ''; ?>>Baixa</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="categoria" class="form-control">
                                        <option value="">Todas Categorias</option>
                                        <?php while ($cat = $categorias->fetch_assoc()): ?>
                                            <option value="<?php echo $cat['id']; ?>" <?php echo $filtro_categoria == $cat['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($cat['nome']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <select name="tecnico" class="form-control">
                                        <option value="">Todos Técnicos</option>
                                        <?php while ($tec = $tecnicos->fetch_assoc()): ?>
                                            <option value="<?php echo $tec['id']; ?>" <?php echo $filtro_tecnico == $tec['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($tec['nome']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-filter"></i>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Lista de Chamados -->
                        <div class="mt-4">
                            <h5>Resultados: <?php echo $result->num_rows; ?> chamados encontrados</h5>
                            <?php while ($chamado = $result->fetch_assoc()): ?>
                                <div class="chamado-row prioridade-<?php echo strtolower($chamado['prioridade']); ?>">
                                    <div class="row align-items-center">
                                        <div class="col-md-6">
                                            <h5>
                                                <strong>#<?php echo $chamado['protocolo']; ?></strong> -
                                                <?php echo htmlspecialchars($chamado['titulo']); ?>
                                            </h5>
                                            <p class="mb-2"><?php echo htmlspecialchars(substr($chamado['descricao'], 0, 100)); ?>...</p>
                                            <div>
                                                <?php if ($chamado['categoria_nome']): ?>
                                                    <span class="categoria-badge" style="background: <?php echo $chamado['categoria_cor']; ?>">
                                                        <i class="fas <?php echo $chamado['categoria_icone']; ?>"></i>
                                                        <?php echo htmlspecialchars($chamado['categoria_nome']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <small class="text-muted">
                                                <i class="fas fa-user"></i> <?php echo htmlspecialchars($chamado['cliente_nome']); ?><br>
                                                <i class="fas fa-calendar"></i> <?php echo date('d/m/Y H:i', strtotime($chamado['data_abertura'])); ?>
                                            </small>
                                            <?php if ($chamado['tecnico_nome']): ?>
                                                <br><small class="text-primary">
                                                    <i class="fas fa-wrench"></i> <?php echo htmlspecialchars($chamado['tecnico_nome']); ?>
                                                </small>
                                            <?php else: ?>
                                                <br><small class="text-danger">
                                                    <i class="fas fa-exclamation-circle"></i> Sem técnico atribuído
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="col-md-2 text-center">
                                            <span class="badge badge-<?php
                                                echo match($chamado['status']) {
                                                    'aberto' => 'info',
                                                    'em andamento' => 'primary',
                                                    'pendente' => 'warning',
                                                    'resolvido' => 'success',
                                                    'cancelado' => 'danger',
                                                    default => 'secondary'
                                                };
                                            ?> p-2">
                                                <?php echo ucfirst($chamado['status']); ?>
                                            </span>
                                            <br>
                                            <span class="badge badge-<?php
                                                echo match($chamado['prioridade']) {
                                                    'critica' => 'danger',
                                                    'alta' => 'warning',
                                                    'media' => 'info',
                                                    'baixa' => 'success',
                                                    default => 'secondary'
                                                };
                                            ?> mt-2">
                                                <?php echo ucfirst($chamado['prioridade']); ?>
                                            </span>
                                        </div>
                                        <div class="col-md-1 text-center">
                                            <a href="../cliente/visualizar_chamado.php?id=<?php echo $chamado['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>

                            <?php if ($result->num_rows === 0): ?>
                                <div class="alert alert-info text-center">
                                    <i class="fas fa-info-circle"></i> Nenhum chamado encontrado com os filtros selecionados.
                                </div>
                            <?php endif; ?>
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
<?php
$stmt->close();
$conn->close();
?>
