<?php
/**
 * Chamados Ativos - NetoNerd ITSM v2.0
 * Listagem completa de todos os chamados com filtros avançados
 */

session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

// PROTEÇÃO: Apenas administradores podem acessar
requireAdmin();

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
    LEFT JOIN categorias_chamado cat ON c.categoria_id IS NOT NULL AND c.categoria_id = cat.id
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

// Armazena resultados em array
$chamados = [];
while ($row = $result->fetch_assoc()) {
    $chamados[] = $row;
}

// Buscar categorias para filtro
$categorias = $conn->query("SELECT * FROM categorias_chamado WHERE ativo = 1 ORDER BY nome");

// Buscar técnicos para filtro
$tecnicos = $conn->query("SELECT id, nome, matricula FROM tecnicos WHERE status_tecnico = 'Active' ORDER BY nome");

// Configuração da página
$page_title = "Chamados Ativos - NetoNerd ITSM";

// Incluir header
require_once '../includes/header.php';
?>

<!-- Conteúdo Principal -->
<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <!-- Cabeçalho da Página -->
        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h1 class="nn-card-title">
                    <i class="fas fa-ticket-alt"></i>
                    Todos os Chamados
                </h1>
                <div>
                    <button class="nn-btn nn-btn-secondary nn-btn-sm" onclick="location.href='atribuir_chamados.php'">
                        <i class="fas fa-user-plus"></i>
                        Atribuir Chamados
                    </button>
                    <button class="nn-btn nn-btn-primary nn-btn-sm" onclick="location.reload()">
                        <i class="fas fa-sync"></i>
                        Atualizar
                    </button>
                </div>
            </div>
        </div>

        <!-- Filtros Avançados -->
        <div class="nn-card nn-animate-slide">
            <div class="nn-card-header">
                <h2 class="nn-card-title">
                    <i class="fas fa-filter"></i>
                    Filtros de Busca
                </h2>
            </div>
            <div class="nn-card-body">
                <form method="GET" action="">
                    <div class="row g-3">
                        <!-- Busca por texto -->
                        <div class="col-md-4">
                            <div class="nn-form-group">
                                <label class="nn-form-label">
                                    <i class="fas fa-search"></i>
                                    Buscar
                                </label>
                                <input type="text" name="busca" class="nn-form-control"
                                       placeholder="Protocolo, título ou descrição..."
                                       value="<?php echo htmlspecialchars($busca); ?>">
                            </div>
                        </div>

                        <!-- Filtro de Status -->
                        <div class="col-md-2">
                            <div class="nn-form-group">
                                <label class="nn-form-label">
                                    <i class="fas fa-info-circle"></i>
                                    Status
                                </label>
                                <select name="status" class="nn-form-control">
                                    <option value="">Todos</option>
                                    <option value="aberto" <?php echo $filtro_status === 'aberto' ? 'selected' : ''; ?>>Aberto</option>
                                    <option value="em andamento" <?php echo $filtro_status === 'em andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                                    <option value="pendente" <?php echo $filtro_status === 'pendente' ? 'selected' : ''; ?>>Pendente</option>
                                    <option value="resolvido" <?php echo $filtro_status === 'resolvido' ? 'selected' : ''; ?>>Resolvido</option>
                                    <option value="cancelado" <?php echo $filtro_status === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                            </div>
                        </div>

                        <!-- Filtro de Prioridade -->
                        <div class="col-md-2">
                            <div class="nn-form-group">
                                <label class="nn-form-label">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Prioridade
                                </label>
                                <select name="prioridade" class="nn-form-control">
                                    <option value="">Todas</option>
                                    <option value="critica" <?php echo $filtro_prioridade === 'critica' ? 'selected' : ''; ?>>Crítica</option>
                                    <option value="alta" <?php echo $filtro_prioridade === 'alta' ? 'selected' : ''; ?>>Alta</option>
                                    <option value="media" <?php echo $filtro_prioridade === 'media' ? 'selected' : ''; ?>>Média</option>
                                    <option value="baixa" <?php echo $filtro_prioridade === 'baixa' ? 'selected' : ''; ?>>Baixa</option>
                                </select>
                            </div>
                        </div>

                        <!-- Filtro de Categoria -->
                        <div class="col-md-2">
                            <div class="nn-form-group">
                                <label class="nn-form-label">
                                    <i class="fas fa-tag"></i>
                                    Categoria
                                </label>
                                <select name="categoria" class="nn-form-control">
                                    <option value="">Todas</option>
                                    <?php
                                    $categorias->data_seek(0);
                                    while ($cat = $categorias->fetch_assoc()):
                                    ?>
                                        <option value="<?php echo $cat['id']; ?>" <?php echo $filtro_categoria == $cat['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['nome']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Filtro de Técnico -->
                        <div class="col-md-2">
                            <div class="nn-form-group">
                                <label class="nn-form-label">
                                    <i class="fas fa-user-cog"></i>
                                    Técnico
                                </label>
                                <select name="tecnico" class="nn-form-control">
                                    <option value="">Todos</option>
                                    <?php
                                    $tecnicos->data_seek(0);
                                    while ($tec = $tecnicos->fetch_assoc()):
                                    ?>
                                        <option value="<?php echo $tec['id']; ?>" <?php echo $filtro_tecnico == $tec['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($tec['nome']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Botões de Ação -->
                        <div class="col-12">
                            <button type="submit" class="nn-btn nn-btn-primary">
                                <i class="fas fa-search"></i>
                                Filtrar
                            </button>
                            <a href="chamados_ativos.php" class="nn-btn nn-btn-secondary">
                                <i class="fas fa-times"></i>
                                Limpar Filtros
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Resultado da Busca -->
        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h2 class="nn-card-title">
                    <i class="fas fa-list"></i>
                    Resultados: <?php echo count($chamados); ?> chamados encontrados
                </h2>
            </div>
            <div class="nn-card-body">
                <?php if (count($chamados) > 0): ?>
                    <?php foreach ($chamados as $chamado):
                        // Determinar classe de prioridade
                        $prioridadeClass = match(strtolower($chamado['prioridade'])) {
                            'critica' => 'nn-badge-critical',
                            'alta' => 'nn-badge-high',
                            'media' => 'nn-badge-medium',
                            'baixa' => 'nn-badge-low',
                            default => 'nn-badge-secondary'
                        };

                        // Determinar classe de status
                        $statusClass = match(strtolower($chamado['status'])) {
                            'aberto' => 'nn-badge-primary',
                            'em andamento' => 'nn-badge-info',
                            'pendente' => 'nn-badge-warning',
                            'resolvido' => 'nn-badge-success',
                            'cancelado' => 'nn-badge-danger',
                            default => 'nn-badge-secondary'
                        };

                        // Borda colorida por prioridade
                        $borderColor = match(strtolower($chamado['prioridade'])) {
                            'critica' => 'border-left: 5px solid var(--priority-critical)',
                            'alta' => 'border-left: 5px solid var(--priority-high)',
                            'media' => 'border-left: 5px solid var(--priority-medium)',
                            'baixa' => 'border-left: 5px solid var(--priority-low)',
                            default => 'border-left: 5px solid var(--secondary-gray)'
                        };
                    ?>
                        <div class="nn-chamado-item" style="<?php echo $borderColor; ?>">
                            <div class="row align-items-center">
                                <!-- Informações do Chamado -->
                                <div class="col-md-5">
                                    <h5 class="mb-2">
                                        <strong class="text-primary">#<?php echo htmlspecialchars($chamado['protocolo']); ?></strong> -
                                        <?php echo htmlspecialchars($chamado['titulo']); ?>
                                    </h5>
                                    <p class="text-muted mb-2">
                                        <?php echo htmlspecialchars(substr($chamado['descricao'], 0, 120)); ?><?php echo strlen($chamado['descricao']) > 120 ? '...' : ''; ?>
                                    </p>
                                    <?php if ($chamado['categoria_nome']): ?>
                                        <span class="nn-badge" style="background-color: <?php echo htmlspecialchars($chamado['categoria_cor']); ?>">
                                            <i class="fas <?php echo htmlspecialchars($chamado['categoria_icone']); ?>"></i>
                                            <?php echo htmlspecialchars($chamado['categoria_nome']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Cliente e Datas -->
                                <div class="col-md-3">
                                    <div class="mb-2">
                                        <i class="fas fa-user text-primary"></i>
                                        <strong>Cliente:</strong><br>
                                        <small><?php echo htmlspecialchars($chamado['cliente_nome']); ?></small>
                                    </div>
                                    <div class="mb-2">
                                        <i class="fas fa-calendar text-success"></i>
                                        <strong>Abertura:</strong><br>
                                        <small><?php echo date('d/m/Y H:i', strtotime($chamado['data_abertura'])); ?></small>
                                    </div>
                                    <?php if ($chamado['tecnico_nome']): ?>
                                        <div>
                                            <i class="fas fa-wrench text-info"></i>
                                            <strong>Técnico:</strong><br>
                                            <small><?php echo htmlspecialchars($chamado['tecnico_nome']); ?></small>
                                            <span class="nn-badge nn-badge-info nn-badge-sm">
                                                <?php echo htmlspecialchars($chamado['tecnico_matricula']); ?>
                                            </span>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-danger">
                                            <i class="fas fa-exclamation-circle"></i>
                                            <strong>Sem técnico atribuído</strong>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Status e Prioridade -->
                                <div class="col-md-2 text-center">
                                    <div class="mb-2">
                                        <span class="<?php echo $statusClass; ?>">
                                            <?php echo ucfirst($chamado['status']); ?>
                                        </span>
                                    </div>
                                    <div>
                                        <span class="<?php echo $prioridadeClass; ?>">
                                            <?php echo ucfirst($chamado['prioridade']); ?>
                                        </span>
                                    </div>
                                </div>

                                <!-- Ações -->
                                <div class="col-md-2 text-center">
                                    <a href="visualizar_chamado.php?id=<?php echo $chamado['id']; ?>"
                                       class="nn-btn nn-btn-primary nn-btn-sm mb-1">
                                        <i class="fas fa-eye"></i>
                                        Visualizar
                                    </a>
                                    <?php if (!$chamado['tecnico_id']): ?>
                                        <a href="atribuir_chamados.php?chamado=<?php echo $chamado['id']; ?>"
                                           class="nn-btn nn-btn-success nn-btn-sm">
                                            <i class="fas fa-user-plus"></i>
                                            Atribuir
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="nn-alert nn-alert-info nn-animate-fade">
                        <i class="fas fa-info-circle"></i>
                        Nenhum chamado encontrado com os filtros selecionados.
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<style>
/* Estilo específico para itens de chamado */
.nn-chamado-item {
    background: #f8f9fa;
    padding: 20px;
    margin-bottom: 15px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.nn-chamado-item:hover {
    transform: translateX(5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}
</style>

<?php
// Incluir footer
$stmt->close();
$conn->close();
require_once '../includes/footer.php';
?>