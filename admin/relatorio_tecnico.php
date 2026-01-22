<?php
/**
 * Relatório Individual do Técnico - NetoNerd ITSM v2.0
 * Estatísticas detalhadas e desempenho individual
 */
 require_once '../controller/configurar_log.php';

session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireAdmin();

$conn = getConnection();

// Verificar se um ID foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: apresenta_tecnicos.php?erro=id_invalido');
    exit();
}

$tecnico_id = intval($_GET['id']);

// Buscar dados do técnico
$stmt = $conn->prepare("SELECT * FROM tecnicos WHERE id = ?");
$stmt->bind_param("i", $tecnico_id);
$stmt->execute();
$tecnico = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$tecnico) {
    header('Location: apresenta_tecnicos.php?erro=tecnico_nao_encontrado');
    exit();
}

// Período para relatório (padrão: últimos 30 dias, mas permite customizar)
$data_inicio = $_GET['data_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
$data_fim = $_GET['data_fim'] ?? date('Y-m-d');
$data_fim_com_horario = $data_fim . " 23:59:59";

// Estatísticas gerais do técnico
$stmt = $conn->prepare("
    SELECT
        COUNT(*) as total_chamados,
        COUNT(CASE WHEN status = 'aberto' THEN 1 END) as abertos,
        COUNT(CASE WHEN status = 'em andamento' THEN 1 END) as em_andamento,
        COUNT(CASE WHEN status = 'pendente' THEN 1 END) as pendentes,
        COUNT(CASE WHEN status = 'resolvido' THEN 1 END) as resolvidos,
        COUNT(CASE WHEN status = 'cancelado' THEN 1 END) as cancelados,
        AVG(CASE WHEN tempo_atendimento_minutos IS NOT NULL THEN tempo_atendimento_minutos END) as tempo_medio_min
    FROM chamados
    WHERE tecnico_id = ?
        AND data_abertura BETWEEN ? AND ?
");

// 2. Use a nova variável aqui (o PHP agora consegue referenciar o local da memória)
$stmt->bind_param("iss", $tecnico_id, $data_inicio, $data_fim_com_horario);

$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

$total = (int) $stats['total_chamados'];
$resolvidos = (int) $stats['resolvidos'];

$taxa_resolucao = $total > 0
    ? round(($resolvidos / $total) * 100, 2)
    : 0;
    
$data_inicio = (string) $data_inicio;
$data_limite_horario = (string) ($data_fim . " 23:59:59");
$tecnico_id = (int) $tecnico_id;

$stmt = $conn->prepare("
    SELECT
        cat.nome AS categoria,
        cat.cor,
        cat.icone,
        COUNT(c.id) AS total
    FROM chamados c
    LEFT JOIN categorias_chamado cat ON c.categoria_id = cat.id
    WHERE c.tecnico_id = ?
      AND c.data_abertura BETWEEN ? AND ?
    GROUP BY cat.id, cat.nome, cat.cor, cat.icone
    ORDER BY total DESC
    LIMIT 10
");

$stmt->bind_param("iss", $tecnico_id, $data_inicio, $data_limite_horario);

$stmt->execute();
$categorias = $stmt->get_result();
$stmt->close();
$data_limite_horario = $data_fim . ' 23:59:59';

$stmt = $conn->prepare("
    SELECT
        prioridade,
        COUNT(*) AS total
    FROM chamados
    WHERE tecnico_id = ?
      AND data_abertura BETWEEN ? AND ?
    GROUP BY prioridade
    ORDER BY FIELD(prioridade, 'critica', 'alta', 'media', 'baixa')
");

$stmt->bind_param("iss", $tecnico_id, $data_inicio, $data_limite_horario);

$stmt->execute();
$prioridades = $stmt->get_result();
$stmt->close();

// Últimos chamados atendidos
$stmt = $conn->prepare("
    SELECT
        c.*,
        cl.nome as cliente_nome,
        cat.nome as categoria_nome,
        cat.cor as categoria_cor
    FROM chamados c
    INNER JOIN clientes cl ON c.cliente_id = cl.id
    LEFT JOIN categorias_chamado cat ON c.categoria_id = cat.id
    WHERE c.tecnico_id = ?
    ORDER BY c.data_abertura DESC
    LIMIT 10
");
$stmt->bind_param("i", $tecnico_id);
$stmt->execute();
$ultimos_chamados = $stmt->get_result();
$stmt->close();

$page_title = "Relatório - " . $tecnico['nome'] . " - NetoNerd ITSM";
require_once '../includes/header.php';
?>

<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <!-- Header -->
        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <div>
                    <h1 class="nn-card-title">
                        <i class="fas fa-user-chart"></i>
                        Relatório do Técnico
                    </h1>
                    <div class="mt-2">
                        <h4 class="mb-0"><?php echo htmlspecialchars($tecnico['nome']); ?></h4>
                        <small class="text-muted">
                            Matrícula: <?php echo htmlspecialchars($tecnico['matricula']); ?> |
                            <span class="nn-badge <?php echo $tecnico['status_tecnico'] === 'Active' ? 'nn-badge-success' : 'nn-badge-secondary'; ?>">
                                <?php echo $tecnico['status_tecnico']; ?>
                            </span>
                        </small>
                    </div>
                </div>
                <div>
                    <a href="apresenta_tecnicos.php" class="nn-btn nn-btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Voltar
                    </a>
                    <a href="editar_tecnico.php?id=<?php echo $tecnico['id']; ?>" class="nn-btn nn-btn-primary">
                        <i class="fas fa-edit"></i>
                        Editar
                    </a>
                </div>
            </div>
        </div>

        <!-- Filtro de Período -->
        <div class="nn-card nn-animate-slide">
            <div class="nn-card-body">
                <form method="GET" action="">
                    <input type="hidden" name="id" value="<?php echo $tecnico_id; ?>">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <div class="nn-form-group">
                                <label class="nn-form-label">Data Início</label>
                                <input type="date" name="data_inicio" class="nn-form-control" value="<?php echo $data_inicio; ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="nn-form-group">
                                <label class="nn-form-label">Data Fim</label>
                                <input type="date" name="data_fim" class="nn-form-control" value="<?php echo $data_fim; ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="nn-btn nn-btn-primary" style="width: 100%;">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="nn-stats-grid nn-animate-slide">
            <div class="nn-stat-card primary">
                <div class="nn-stat-icon primary">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="nn-stat-value"><?php echo $stats['total_chamados']; ?></div>
                <div class="nn-stat-label">Total de Chamados</div>
            </div>

            <div class="nn-stat-card success">
                <div class="nn-stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="nn-stat-value"><?php echo $stats['resolvidos']; ?></div>
                <div class="nn-stat-label">Resolvidos</div>
            </div>

            <div class="nn-stat-card warning">
                <div class="nn-stat-icon warning">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="nn-stat-value"><?php echo $taxa_resolucao; ?>%</div>
                <div class="nn-stat-label">Taxa de Resolução</div>
            </div>

            <div class="nn-stat-card info">
                <div class="nn-stat-icon info">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="nn-stat-value">
                    <?php
                    if ($stats['tempo_medio_min']) {
                        $horas = floor($stats['tempo_medio_min'] / 60);
                        $mins = round($stats['tempo_medio_min'] % 60);
                        echo $horas . "h " . $mins . "m";
                    } else {
                        echo "N/A";
                    }
                    ?>
                </div>
                <div class="nn-stat-label">Tempo Médio</div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Coluna Esquerda: Gráficos e Estatísticas -->
            <div class="col-lg-6">
                <!-- Status dos Chamados -->
                <div class="nn-card nn-animate-fade">
                    <div class="nn-card-header">
                        <h2 class="nn-card-title">
                            <i class="fas fa-chart-pie"></i>
                            Status dos Chamados
                        </h2>
                    </div>
                    <div class="nn-card-body">
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <span>Abertos</span>
                            <span class="nn-badge nn-badge-primary"><?php echo $stats['abertos']; ?></span>
                        </div>
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <span>Em Andamento</span>
                            <span class="nn-badge nn-badge-info"><?php echo $stats['em_andamento']; ?></span>
                        </div>
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <span>Pendentes</span>
                            <span class="nn-badge nn-badge-warning"><?php echo $stats['pendentes']; ?></span>
                        </div>
                        <div class="mb-3 d-flex justify-content-between align-items-center">
                            <span>Resolvidos</span>
                            <span class="nn-badge nn-badge-success"><?php echo $stats['resolvidos']; ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Cancelados</span>
                            <span class="nn-badge nn-badge-danger"><?php echo $stats['cancelados']; ?></span>
                        </div>
                    </div>
                </div>

                <!-- Chamados por Prioridade -->
                <div class="nn-card nn-animate-fade">
                    <div class="nn-card-header">
                        <h2 class="nn-card-title">
                            <i class="fas fa-exclamation-triangle"></i>
                            Chamados por Prioridade
                        </h2>
                    </div>
                    <div class="nn-card-body">
                        <?php if ($prioridades->num_rows > 0): ?>
                            <?php while ($prior = $prioridades->fetch_assoc()):
                                $prioridadeClass = match(strtolower($prior['prioridade'])) {
                                    'critica' => 'nn-badge-critical',
                                    'alta' => 'nn-badge-high',
                                    'media' => 'nn-badge-medium',
                                    'baixa' => 'nn-badge-low',
                                    default => 'nn-badge-secondary'
                                };
                            ?>
                                <div class="mb-3 d-flex justify-content-between align-items-center">
                                    <span><?php echo ucfirst($prior['prioridade']); ?></span>
                                    <span class="nn-badge <?php echo $prioridadeClass; ?>"><?php echo $prior['total']; ?></span>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted">Nenhum chamado no período</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Coluna Direita: Categorias -->
            <div class="col-lg-6">
                <div class="nn-card nn-animate-fade">
                    <div class="nn-card-header">
                        <h2 class="nn-card-title">
                            <i class="fas fa-tags"></i>
                            Top 10 Categorias Atendidas
                        </h2>
                    </div>
                    <div class="nn-card-body">
                        <?php if ($categorias->num_rows > 0): ?>
                            <?php while ($cat = $categorias->fetch_assoc()): ?>
                                <div class="mb-3 d-flex justify-content-between align-items-center">
                                    <div class="d-flex align-items-center">
                                        <div style="width: 30px; height: 30px; border-radius: 5px; background: <?php echo htmlspecialchars($cat['cor'] ?? '#007bff'); ?>; display: flex; align-items: center; justify-content: center; color: white; margin-right: 10px;">
                                            <i class="fas <?php echo htmlspecialchars($cat['icone'] ?? 'fa-tag'); ?>" style="font-size: 0.9rem;"></i>
                                        </div>
                                        <span><?php echo htmlspecialchars($cat['categoria'] ?? 'Sem categoria'); ?></span>
                                    </div>
                                    <span class="nn-badge nn-badge-primary"><?php echo $cat['total']; ?></span>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p class="text-muted">Nenhum chamado categorizado no período</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Últimos Chamados -->
        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h2 class="nn-card-title">
                    <i class="fas fa-history"></i>
                    Últimos 10 Chamados
                </h2>
            </div>
            <div class="nn-card-body">
                <?php if ($ultimos_chamados->num_rows > 0): ?>
                    <div class="nn-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Protocolo</th>
                                    <th>Cliente</th>
                                    <th>Título</th>
                                    <th>Categoria</th>
                                    <th>Prioridade</th>
                                    <th>Status</th>
                                    <th>Data</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($cham = $ultimos_chamados->fetch_assoc()):
                                    $prioridadeClass = match(strtolower($cham['prioridade'])) {
                                        'critica' => 'nn-badge-critical',
                                        'alta' => 'nn-badge-high',
                                        'media' => 'nn-badge-medium',
                                        'baixa' => 'nn-badge-low',
                                        default => 'nn-badge-secondary'
                                    };
                                    $statusClass = match(strtolower($cham['status'])) {
                                        'aberto' => 'nn-badge-primary',
                                        'em andamento' => 'nn-badge-info',
                                        'pendente' => 'nn-badge-warning',
                                        'resolvido' => 'nn-badge-success',
                                        'cancelado' => 'nn-badge-danger',
                                        default => 'nn-badge-secondary'
                                    };
                                ?>
                                    <tr>
                                        <td><code>#<?php echo htmlspecialchars($cham['protocolo']); ?></code></td>
                                        <td><?php echo htmlspecialchars($cham['cliente_nome']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($cham['titulo'], 0, 40)) . (strlen($cham['titulo']) > 40 ? '...' : ''); ?></td>
                                        <td>
                                            <?php if ($cham['categoria_nome']): ?>
                                                <span class="nn-badge" style="background-color: <?php echo htmlspecialchars($cham['categoria_cor']); ?>">
                                                    <?php echo htmlspecialchars($cham['categoria_nome']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="nn-badge <?php echo $prioridadeClass; ?>"><?php echo ucfirst($cham['prioridade']); ?></span></td>
                                        <td><span class="nn-badge <?php echo $statusClass; ?>"><?php echo ucfirst($cham['status']); ?></span></td>
                                        <td><small><?php echo date('d/m/Y', strtotime($cham['data_abertura'])); ?></small></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="nn-alert nn-alert-info">
                        <i class="fas fa-info-circle"></i>
                        Nenhum chamado encontrado para este técnico.
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php
$conn->close();
require_once '../includes/footer.php';
?>
