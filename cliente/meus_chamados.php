<?php
/**
 * Meus Chamados - NetoNerd ITSM v2.0
 * Lista completa de chamados do cliente com filtros
 */

require_once "../controller/validador_acesso.php";
require_once "../config/bandoDeDados/conexao.php";

$conn = getConnection();
$usuario_id = $_SESSION['id'];

$dados_cliente = obterDadosCliente();

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

// Configuração da página
$page_title = "Meus Chamados - NetoNerd ITSM";

// Incluir header
require_once '../includes/header.php';
?>

<!-- Conteúdo Principal -->
<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <?php if (!empty($_GET['sucesso']) && $_GET['sucesso'] === 'chamado_criado'): ?>
        <div class="nn-alert nn-alert-success nn-animate-fade" style="margin-bottom: 16px;">
            <i class="fas fa-check-circle"></i>
            <strong>Chamado criado com sucesso!</strong>
            <?php if (!empty($_GET['protocolo'])): ?>
                Protocolo: <strong>#<?php echo htmlspecialchars($_GET['protocolo']); ?></strong>
            <?php endif; ?>
            — Nossa equipe em breve entrará em contato.
        </div>
        <?php endif; ?>

        <!-- Cabeçalho de Boas-Vindas -->
        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h1 class="nn-card-title">
                    <?php
                    echo ($dados_cliente['genero'] === 'Feminino' ? 'Bem-vinda, ' : 'Bem-vindo, ') .
                         htmlspecialchars($dados_cliente['nome']) . '!';
                    ?>
                </h1>
                <p class="text-muted mb-0">
                    Gerencie seus chamados de suporte de forma fácil e rápida
                </p>
            </div>
        </div>

        <!-- Dashboard Stats -->
        <div class="nn-stats-grid nn-animate-slide">
            <div class="nn-stat-card primary">
                <div class="nn-stat-icon primary">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="nn-stat-value"><?php echo $total_chamados; ?></div>
                <div class="nn-stat-label">Total de Chamados</div>
            </div>

            <div class="nn-stat-card warning">
                <div class="nn-stat-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="nn-stat-value"><?php echo $chamados_ativos; ?></div>
                <div class="nn-stat-label">Chamados Ativos</div>
            </div>

            <div class="nn-stat-card success">
                <div class="nn-stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="nn-stat-value"><?php echo $chamados_resolvidos; ?></div>
                <div class="nn-stat-label">Chamados Resolvidos</div>
            </div>

            <div class="nn-stat-card info">
                <div class="nn-stat-icon info">
                    <i class="fas fa-plus"></i>
                </div>
                <div style="padding-top: 10px;">
                    <a href="abrir_chamado.php" class="nn-btn nn-btn-primary" style="width: 100%;">
                        Novo Chamado
                    </a>
                </div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="nn-card nn-animate-slide">
            <div class="nn-card-header">
                <h2 class="nn-card-title">
                    <i class="fas fa-filter"></i>
                    Filtrar Chamados
                </h2>
            </div>
            <div class="nn-card-body">
                <form method="GET" action="" id="filterForm">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="nn-form-group">
                                <label class="nn-form-label">
                                    <i class="fas fa-info-circle"></i>
                                    Status
                                </label>
                                <select name="status" class="nn-form-control" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">Todos</option>
                                    <option value="aberto" <?php echo $filtro_status === 'aberto' ? 'selected' : ''; ?>>Aberto</option>
                                    <option value="em andamento" <?php echo $filtro_status === 'em andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                                    <option value="aguardando_cliente" <?php echo $filtro_status === 'aguardando_cliente' ? 'selected' : ''; ?>>Aguardando Cliente</option>
                                    <option value="resolvido" <?php echo $filtro_status === 'resolvido' ? 'selected' : ''; ?>>Resolvido</option>
                                    <option value="fechado" <?php echo $filtro_status === 'fechado' ? 'selected' : ''; ?>>Fechado</option>
                                    <option value="cancelado" <?php echo $filtro_status === 'cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="nn-form-group">
                                <label class="nn-form-label">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Prioridade
                                </label>
                                <select name="prioridade" class="nn-form-control" onchange="document.getElementById('filterForm').submit()">
                                    <option value="">Todas</option>
                                    <option value="baixa" <?php echo $filtro_prioridade === 'baixa' ? 'selected' : ''; ?>>Baixa</option>
                                    <option value="media" <?php echo $filtro_prioridade === 'media' ? 'selected' : ''; ?>>Média</option>
                                    <option value="alta" <?php echo $filtro_prioridade === 'alta' ? 'selected' : ''; ?>>Alta</option>
                                    <option value="urgente" <?php echo $filtro_prioridade === 'urgente' ? 'selected' : ''; ?>>Urgente</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="nn-form-group">
                                <label class="nn-form-label">
                                    <i class="fas fa-search"></i>
                                    Buscar
                                </label>
                                <div class="input-group">
                                    <input type="text" name="busca" class="nn-form-control" placeholder="Protocolo ou descrição..." value="<?php echo htmlspecialchars($filtro_busca); ?>">
                                    <button class="nn-btn nn-btn-primary" type="submit">
                                        <i class="fas fa-search"></i>
                                    </button>
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
                            <span class="nn-badge nn-badge-primary" style="cursor: pointer;" onclick="removeFilter('status')">
                                Status: <?php echo ucfirst($filtro_status); ?>
                                <i class="fas fa-times"></i>
                            </span>
                        <?php endif; ?>
                        <?php if ($filtro_prioridade): ?>
                            <span class="nn-badge nn-badge-primary" style="cursor: pointer;" onclick="removeFilter('prioridade')">
                                Prioridade: <?php echo ucfirst($filtro_prioridade); ?>
                                <i class="fas fa-times"></i>
                            </span>
                        <?php endif; ?>
                        <?php if ($filtro_busca): ?>
                            <span class="nn-badge nn-badge-primary" style="cursor: pointer;" onclick="removeFilter('busca')">
                                Busca: "<?php echo htmlspecialchars($filtro_busca); ?>"
                                <i class="fas fa-times"></i>
                            </span>
                        <?php endif; ?>
                        <a href="meus_chamados.php" class="nn-btn nn-btn-danger nn-btn-sm ms-2">
                            Limpar todos
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Lista de Chamados -->
        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h2 class="nn-card-title">
                    <i class="fas fa-list"></i>
                    Seus Chamados (<?php echo $result_chamados->num_rows; ?>)
                </h2>
            </div>
            <div class="nn-card-body">
                <?php if ($result_chamados->num_rows > 0): ?>
                    <?php while ($chamado = $result_chamados->fetch_assoc()):
                        // Classes de status
                        $statusClass = match(strtolower(str_replace(' ', '_', $chamado['status']))) {
                            'aberto' => 'nn-badge-primary',
                            'em_andamento' => 'nn-badge-info',
                            'aguardando_cliente' => 'nn-badge-warning',
                            'resolvido' => 'nn-badge-success',
                            'fechado' => 'nn-badge-secondary',
                            'cancelado' => 'nn-badge-danger',
                            default => 'nn-badge-secondary'
                        };

                        // Classes de prioridade
                        $prioridadeClass = match(strtolower($chamado['prioridade'])) {
                            'baixa' => 'nn-badge-low',
                            'media' => 'nn-badge-medium',
                            'alta' => 'nn-badge-high',
                            'urgente', 'critica' => 'nn-badge-critical',
                            default => 'nn-badge-secondary'
                        };
                    ?>
                        <div class="nn-chamado-cliente-item" onclick="window.location.href='detalhe_chamado.php?id=<?php echo $chamado['id']; ?>'">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="nn-protocolo">#<?php echo htmlspecialchars($chamado['protocolo']); ?></span>
                                    <h6 class="mb-1 mt-1"><strong><?php echo htmlspecialchars($chamado['titulo']); ?></strong></h6>
                                </div>
                                <span class="nn-badge <?php echo $statusClass; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $chamado['status'])); ?>
                                </span>
                            </div>

                            <p class="text-muted mb-2" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                <?php echo htmlspecialchars($chamado['descricao']); ?>
                            </p>

                            <div class="d-flex gap-2 flex-wrap align-items-center">
                                <span class="nn-badge <?php echo $prioridadeClass; ?>">
                                    <?php echo ucfirst($chamado['prioridade']); ?>
                                </span>
                                <?php if ($chamado['categoria']): ?>
                                    <span class="text-muted">
                                        <i class="fas fa-tag"></i>
                                        <?php echo htmlspecialchars($chamado['categoria']); ?>
                                    </span>
                                <?php endif; ?>
                                <span class="text-muted">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('d/m/Y H:i', strtotime($chamado['data_abertura'])); ?>
                                </span>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox" style="font-size: 4rem; color: #ddd; margin-bottom: 1rem;"></i>
                        <h5 class="text-muted">Nenhum chamado encontrado</h5>
                        <p class="text-muted">
                            <?php if ($filtro_status || $filtro_prioridade || $filtro_busca): ?>
                                Não encontramos chamados com os filtros aplicados. Tente ajustar os filtros ou limpar todos.
                            <?php else: ?>
                                Você ainda não tem chamados. Que tal abrir o primeiro?
                            <?php endif; ?>
                        </p>
                        <a href="abrir_chamado.php" class="nn-btn nn-btn-primary">
                            <i class="fas fa-plus"></i>
                            Abrir Novo Chamado
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<style>
/* Estilos específicos para lista de chamados do cliente */
.nn-chamado-cliente-item {
    padding: 20px;
    border-radius: 8px;
    background: #f8f9fa;
    margin-bottom: 15px;
    transition: all 0.3s ease;
    cursor: pointer;
    border-left: 4px solid var(--primary-blue);
}

.nn-chamado-cliente-item:hover {
    background: #e9ecef;
    transform: translateX(5px);
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
}

.nn-protocolo {
    background: var(--gradient-primary);
    color: white;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}
</style>

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

<?php
// Incluir footer
$conn->close();
require_once '../includes/footer.php';
?>
