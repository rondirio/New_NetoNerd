<?php 
require_once "../controller/validador_acesso.php";
include '../config/bandoDeDados/conexao.php';

$conn = getConnection();
$usuario_id = $_SESSION['id'];

// Buscar informações do cliente
$stmt = $conn->prepare("SELECT nome, genero FROM clientes WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$cliente = $result->fetch_assoc();
$stmt->close();

// Buscar estatísticas
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM chamados WHERE cliente_id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$total_chamados = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM chamados WHERE cliente_id = ? AND status IN ('aberto', 'em andamento')");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$chamados_ativos = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM chamados WHERE cliente_id = ? AND status = 'resolvido'");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$chamados_resolvidos = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Filtros
$filtro_status = isset($_GET['status']) ? $_GET['status'] : '';
$filtro_prioridade = isset($_GET['prioridade']) ? $_GET['prioridade'] : '';
$filtro_busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';

// Construir query com filtros
$query = "SELECT id, titulo, descricao, prioridade, status, protocolo, categoria, data_abertura 
          FROM chamados WHERE cliente_id = ?";
$params = [$usuario_id];
$types = "i";

if ($filtro_status) {
    $query .= " AND status = ?";
    $params[] = $filtro_status;
    $types .= "s";
}

if ($filtro_prioridade) {
    $query .= " AND prioridade = ?";
    $params[] = $filtro_prioridade;
    $types .= "s";
}

if ($filtro_busca) {
    $query .= " AND (titulo LIKE ? OR descricao LIKE ? OR protocolo LIKE ?)";
    $busca_param = "%$filtro_busca%";
    $params[] = $busca_param;
    $params[] = $busca_param;
    $params[] = $busca_param;
    $types .= "sss";
}

$query .= " ORDER BY data_abertura DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result_chamados = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Chamados - NetoNerd</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    <style>
        .dashboard-welcome {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .welcome-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .welcome-subtitle {
            opacity: 0.95;
            font-size: 1.1rem;
        }
        
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-top: 4px solid #667eea;
            transition: all 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.15);
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #6c757d;
            font-size: 0.95rem;
            font-weight: 500;
        }
        
        .filters-section {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            margin-bottom: 25px;
        }
        
        .section-title {
            font-size: 1.4rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .section-title::before {
            content: "";
            width: 4px;
            height: 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin-right: 12px;
            border-radius: 2px;
        }
        
        .chamado-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border-left: 4px solid #667eea;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .chamado-card:hover {
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.15);
            transform: translateX(5px);
        }
        
        .chamado-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        
        .chamado-protocolo {
            font-size: 0.85rem;
            color: #667eea;
            font-weight: 700;
            background: #f0f3ff;
            padding: 5px 12px;
            border-radius: 20px;
        }
        
        .chamado-titulo {
            font-size: 1.2rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 8px;
        }
        
        .chamado-descricao {
            color: #6c757d;
            font-size: 0.95rem;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .chamado-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
        }
        
        .chamado-info {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .chamado-info i {
            margin-right: 5px;
        }
        
        .badge-custom {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        
        .badge-status-aberto {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .badge-status-em_andamento {
            background: #fff3e0;
            color: #f57c00;
        }
        
        .badge-status-aguardando_cliente {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        .badge-status-resolvido {
            background: #e8f5e9;
            color: #388e3c;
        }
        
        .badge-status-fechado {
            background: #e0e0e0;
            color: #616161;
        }
        
        .badge-status-cancelado {
            background: #ffebee;
            color: #d32f2f;
        }
        
        .badge-prioridade-baixa {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .badge-prioridade-media {
            background: #fff3e0;
            color: #ef6c00;
        }
        
        .badge-prioridade-alta {
            background: #ffebee;
            color: #c62828;
        }
        
        .badge-prioridade-urgente {
            background: #1a1a1a;
            color: #fff;
        }
        
        .btn-novo-chamado {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 15px 30px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-novo-chamado:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .empty-icon {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .empty-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #212529;
            margin-bottom: 10px;
        }
        
        .empty-text {
            color: #6c757d;
            margin-bottom: 25px;
        }
        
        .filter-badge {
            background: #667eea;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            margin-right: 8px;
            margin-bottom: 8px;
        }
        
        .filter-badge .remove {
            margin-left: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        
        @media (max-width: 768px) {
            .chamado-header {
                flex-direction: column;
            }
            
            .chamado-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom bg-primary">
        <a class="navbar-brand" href="home.php">
            <img class="logo" src="imagens/logoNetoNerd.jpg" alt="Logo NetoNerd">
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse LinksNav" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item active"><a class="nav-link" href="home.php">Meus Chamados</a></li>
                <li class="nav-item"><a class="nav-link" href="minha_conta.php">Minha Conta</a></li>
                <li class="nav-item"><a class="nav-link" href="suporte.php">Suporte</a></li>
                <li class="nav-item"><a class="nav-link btn btn-light text-white bg-dark ml-2" href="logoff.php">Sair</a></li>
            </ul>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Welcome Section -->
        <div class="dashboard-welcome">
            <div class="welcome-title">
                <?php 
                if ($cliente['genero'] === 'Masculino') {
                    echo "Bem-vindo, " . htmlspecialchars($cliente['nome']);
                } elseif ($cliente['genero'] === 'Feminino') {
                    echo "Bem-vinda, " . htmlspecialchars($cliente['nome']);
                } else {
                    echo "Bem-vindo(a), " . htmlspecialchars($cliente['nome']);
                }
                ?>
            </div>
            <div class="welcome-subtitle">
                Gerencie seus chamados de suporte de forma fácil e rápida
            </div>
        </div>

        <!-- Estatísticas -->
        <div class="stats-container">
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
            <div class="stat-card">
                <a href="abrir_chamado.php" class="btn btn-novo-chamado" style="width: 100%; text-decoration: none;">
                    + Novo Chamado
                </a>
            </div>
        </div>

        <!-- Filtros -->
        <div class="filters-section">
            <h3 class="section-title">Filtrar Chamados</h3>
            <form method="GET" action="" id="filterForm">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="status">Status</label>
                        <select name="status" id="status" class="form-control" onchange="document.getElementById('filterForm').submit()">
                            <option value="">Todos</option>
                            <option value="aberto" <?php echo $filtro_status === 'aberto' ? 'selected' : ''; ?>>Aberto</option>
                            <option value="em andamento" <?php echo $filtro_status === 'em andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                            <option value="aguardando_cliente" <?php echo $filtro_status === 'aguardando_cliente' ? 'selected' : ''; ?>>Aguardando Cliente</option>
                            <option value="resolvido" <?php echo $filtro_status === 'resolvido' ? 'selected' : ''; ?>>Resolvido</option>
                            <option value="fechado" <?php echo $filtro_status === 'fechado' ? 'selected' : ''; ?>>Fechado</option>
                            <option value="cancelado" <?php echo $filtro_status === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="prioridade">Prioridade</label>
                        <select name="prioridade" id="prioridade" class="form-control" onchange="document.getElementById('filterForm').submit()">
                            <option value="">Todas</option>
                            <option value="baixa" <?php echo $filtro_prioridade === 'baixa' ? 'selected' : ''; ?>>Baixa</option>
                            <option value="media" <?php echo $filtro_prioridade === 'media' ? 'selected' : ''; ?>>Média</option>
                            <option value="alta" <?php echo $filtro_prioridade === 'alta' ? 'selected' : ''; ?>>Alta</option>
                            <option value="urgente" <?php echo $filtro_prioridade === 'urgente' ? 'selected' : ''; ?>>Urgente</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="busca">Buscar</label>
                        <div class="input-group">
                            <input type="text" name="busca" id="busca" class="form-control" placeholder="Protocolo ou descrição..." value="<?php echo htmlspecialchars($filtro_busca); ?>">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">🔍</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            
            <!-- Filtros Ativos -->
            <?php if ($filtro_status || $filtro_prioridade || $filtro_busca): ?>
                <div class="mt-3">
                    <strong>Filtros ativos:</strong>
                    <?php if ($filtro_status): ?>
                        <span class="filter-badge">
                            Status: <?php echo ucfirst($filtro_status); ?>
                            <span class="remove" onclick="removeFilter('status')">×</span>
                        </span>
                    <?php endif; ?>
                    <?php if ($filtro_prioridade): ?>
                        <span class="filter-badge">
                            Prioridade: <?php echo ucfirst($filtro_prioridade); ?>
                            <span class="remove" onclick="removeFilter('prioridade')">×</span>
                        </span>
                    <?php endif; ?>
                    <?php if ($filtro_busca): ?>
                        <span class="filter-badge">
                            Busca: "<?php echo htmlspecialchars($filtro_busca); ?>"
                            <span class="remove" onclick="removeFilter('busca')">×</span>
                        </span>
                    <?php endif; ?>
                    <a href="home.php" style="margin-left: 10px; color: #dc3545; font-weight: 600;">Limpar todos</a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Lista de Chamados -->
        <div class="section-title">Seus Chamados</div>
        
        <?php if ($result_chamados->num_rows > 0): ?>
            <?php while ($chamado = $result_chamados->fetch_assoc()): ?>
                <div class="chamado-card" onclick="window.location.href='detalhe_chamado.php?id=<?php echo $chamado['id']; ?>'">
                    <div class="chamado-header">
                        <div>
                            <span class="chamado-protocolo">#<?php echo htmlspecialchars($chamado['protocolo']); ?></span>
                            <div class="chamado-titulo"><?php echo htmlspecialchars($chamado['titulo']); ?></div>
                        </div>
                        <div>
                            <span class="badge-custom badge-status-<?php echo str_replace(' ', '_', $chamado['status']); ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $chamado['status'])); ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="chamado-descricao">
                        <?php echo htmlspecialchars($chamado['descricao']); ?>
                    </div>
                    
                    <div class="chamado-meta">
                        <span class="badge-custom badge-prioridade-<?php echo $chamado['prioridade']; ?>">
                            <?php echo ucfirst($chamado['prioridade']); ?>
                        </span>
                        <span class="chamado-info">
                            📁 <?php echo htmlspecialchars($chamado['categoria']); ?>
                        </span>
                        <span class="chamado-info">
                            📅 <?php echo date('d/m/Y H:i', strtotime($chamado['data_abertura'])); ?>
                        </span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-icon">📭</div>
                <div class="empty-title">Nenhum chamado encontrado</div>
                <div class="empty-text">
                    <?php if ($filtro_status || $filtro_prioridade || $filtro_busca): ?>
                        Não encontramos chamados com os filtros aplicados. Tente ajustar os filtros ou <a href="home.php">limpar todos</a>.
                    <?php else: ?>
                        Você ainda não tem chamados. Que tal abrir o primeiro?
                    <?php endif; ?>
                </div>
                <a href="abrir_chamado.php" class="btn btn-novo-chamado">
                    + Abrir Novo Chamado
                </a>
            </div>
        <?php endif; ?>
    </div>

    <footer class="bg-primary text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-2">© 2025 NetoNerd - Todos os direitos reservados</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function removeFilter(filterName) {
            const form = document.getElementById('filterForm');
            const input = form.querySelector(`[name="${filterName}"]`);
            if (input) {
                input.value = '';
                form.submit();
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>