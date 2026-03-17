<?php
/**
 * Listar Ordens de Serviço - NetoNerd ITSM v2.0
 */
session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireAdmin();

$conn = getConnection();

// Filtros
$filtro_status = $_GET['status'] ?? '';
$filtro_tecnico = $_GET['tecnico'] ?? '';
$busca = $_GET['busca'] ?? '';

// Query principal
$sql = "
    SELECT 
        os.*,
        t.nome as tecnico_nome,
        t.matricula as tecnico_matricula,
        DATEDIFF(CURDATE(), DATE(os.data_criacao)) as dias_aberta
    FROM ordens_servico os
    INNER JOIN tecnicos t ON os.tecnico_id = t.id
    WHERE 1=1
";

$params = [];
$types = '';

if ($filtro_status) {
    $sql .= " AND os.status = ?";
    $params[] = $filtro_status;
    $types .= 's';
}

if ($filtro_tecnico) {
    $sql .= " AND os.tecnico_id = ?";
    $params[] = intval($filtro_tecnico);
    $types .= 'i';
}

if ($busca) {
    $sql .= " AND (os.numero_os LIKE ? OR os.cliente_nome LIKE ? OR os.problema_relatado LIKE ?)";
    $busca_like = "%{$busca}%";
    $params[] = $busca_like;
    $params[] = $busca_like;
    $params[] = $busca_like;
    $types .= 'sss';
}

$sql .= " ORDER BY os.data_criacao DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Armazena em array
$ordens = [];
while ($row = $result->fetch_assoc()) {
    $ordens[] = $row;
}

// Buscar técnicos para filtro
$tecnicos = $conn->query("SELECT id, nome FROM tecnicos WHERE Ativo = 1 ORDER BY nome");

// Estatísticas
$stats = $conn->query("
    SELECT 
        COUNT(*) as total,
        COUNT(CASE WHEN status = 'aberta' THEN 1 END) as abertas,
        COUNT(CASE WHEN status = 'em_andamento' THEN 1 END) as em_andamento,
        COUNT(CASE WHEN status = 'concluida' THEN 1 END) as concluidas,
        SUM(valor_total) as valor_total
    FROM ordens_servico
")->fetch_assoc();

$page_title = "Ordens de Serviço - NetoNerd ITSM";
require_once '../includes/header.php';
?>

<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <!-- Cabeçalho -->
        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h1 class="nn-card-title">
                    <i class="fas fa-file-invoice"></i>
                    Ordens de Serviço
                </h1>
                <div>
                    <a href="gerar_ordem_servico.php" class="nn-btn nn-btn-primary">
                        <i class="fas fa-plus"></i> Nova Ordem
                    </a>
                </div>
            </div>
        </div>

       <!-- Alertas -->
        <?php if (isset($_GET['sucesso'])): ?>
            <div class="nn-alert nn-alert-success nn-animate-fade">
                <i class="fas fa-check-circle"></i>
                <?php
                switch($_GET['sucesso']) {
                    case 'os_criada':
                        echo 'Ordem de Serviço ' . htmlspecialchars($_GET['numero'] ?? '') . ' criada com sucesso!';
                        if (isset($_GET['cliente_cadastrado'])) {
                            echo '<br><small>Cliente também foi cadastrado no sistema.</small>';
                        }
                        break;
                    default:
                        echo 'Operação realizada com sucesso!';
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- Estatísticas -->
        <div class="nn-stats-grid nn-animate-slide">
            <div class="nn-stat-card primary">
                <div class="nn-stat-icon primary">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="nn-stat-value"><?= $stats['total'] ?></div>
                <div class="nn-stat-label">Total de OS</div>
            </div>

            <div class="nn-stat-card warning">
                <div class="nn-stat-icon warning">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div class="nn-stat-value"><?= $stats['abertas'] ?></div>
                <div class="nn-stat-label">Abertas</div>
            </div>

            <div class="nn-stat-card info">
                <div class="nn-stat-icon info">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="nn-stat-value"><?= $stats['em_andamento'] ?></div>
                <div class="nn-stat-label">Em Andamento</div>
            </div>

            <div class="nn-stat-card success">
                <div class="nn-stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="nn-stat-value"><?= $stats['concluidas'] ?></div>
                <div class="nn-stat-label">Concluídas</div>
            </div>

            <div class="nn-stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="nn-stat-icon" style="background: rgba(255,255,255,0.2);">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="nn-stat-value" style="color: white;">R$ <?= number_format($stats['valor_total'], 2, ',', '.') ?></div>
                <div class="nn-stat-label" style="color: rgba(255,255,255,0.9);">Valor Total</div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="nn-card nn-animate-slide">
            <div class="nn-card-header">
                <h2 class="nn-card-title">
                    <i class="fas fa-filter"></i>
                    Filtros
                </h2>
            </div>
            <div class="nn-card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label class="nn-form-label">
                            <i class="fas fa-search"></i> Buscar
                        </label>
                        <input type="text" name="busca" class="nn-form-control" 
                               placeholder="Número, cliente, problema..." 
                               value="<?= htmlspecialchars($busca) ?>">
                    </div>

                    <div class="col-md-3">
                        <label class="nn-form-label">
                            <i class="fas fa-info-circle"></i> Status
                        </label>
                        <select name="status" class="nn-form-control">
                            <option value="">Todos</option>
                            <option value="aberta" <?= $filtro_status === 'aberta' ? 'selected' : '' ?>>Abertas</option>
                            <option value="em_andamento" <?= $filtro_status === 'em_andamento' ? 'selected' : '' ?>>Em Andamento</option>
                            <option value="concluida" <?= $filtro_status === 'concluida' ? 'selected' : '' ?>>Concluídas</option>
                            <option value="cancelada" <?= $filtro_status === 'cancelada' ? 'selected' : '' ?>>Canceladas</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="nn-form-label">
                            <i class="fas fa-user-cog"></i> Técnico
                        </label>
                        <select name="tecnico" class="nn-form-control">
                            <option value="">Todos</option>
                            <?php
                            $tecnicos->data_seek(0);
                            while ($tec = $tecnicos->fetch_assoc()):
                            ?>
                                <option value="<?= $tec['id'] ?>" <?= $filtro_tecnico == $tec['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($tec['nome']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="nn-btn nn-btn-primary w-100">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de Ordens -->
        <div class="nn-card nn-animate-fade">
            <div class="nn-card-body">
                <?php if (count($ordens) === 0): ?>
                    <div class="nn-alert nn-alert-info">
                        <i class="fas fa-info-circle"></i>
                        Nenhuma ordem de serviço encontrada.
                    </div>
                <?php else: ?>
                    <div class="nn-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Número OS</th>
                                    <th>Cliente</th>
                                    <th>Problema</th>
                                    <th>Técnico</th>
                                    <th>Valor</th>
                                    <th>Data</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ordens as $os): ?>
                                    <tr>
                                        <td>
                                            <strong class="text-primary"><?= htmlspecialchars($os['numero_os']) ?></strong>
                                            <?php if ($os['chamado_id']): ?>
                                                <br><small class="text-muted">
                                                    <i class="fas fa-link"></i> Chamado vinculado
                                                </small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($os['cliente_nome']) ?></strong>
                                            <?php if ($os['cliente_telefone']): ?>
                                                <br><small><?= htmlspecialchars($os['cliente_telefone']) ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?= htmlspecialchars(substr($os['problema_relatado'], 0, 60)) ?>...</small>
                                        </td>
                                        <td>
                                            <?= htmlspecialchars($os['tecnico_nome']) ?>
                                            <br><small class="nn-badge nn-badge-info"><?= htmlspecialchars($os['tecnico_matricula']) ?></small>
                                        </td>
                                        <td>
                                            <strong style="color: var(--success);">
                                                R$ <?= number_format($os['valor_total'], 2, ',', '.') ?>
                                            </strong>
                                        </td>
                                        <td>
                                            <?= date('d/m/Y', strtotime($os['data_criacao'])) ?>
                                            <?php if ($os['dias_aberta'] > 0): ?>
                                                <br><small class="text-muted">(<?= $os['dias_aberta'] ?> dias)</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="nn-badge nn-badge-<?= 
                                                $os['status'] === 'aberta' ? 'primary' :
                                                ($os['status'] === 'em_andamento' ? 'info' :
                                                ($os['status'] === 'concluida' ? 'success' : 'danger'))
                                            ?>">
                                                <?= ucfirst(str_replace('_', ' ', $os['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="imprimir_ordem_servico.php?id=<?= $os['id'] ?>" 
                                               class="nn-btn nn-btn-primary nn-btn-sm" 
                                               target="_blank"
                                               title="Imprimir">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            <a href="visualizar_ordem_servico.php?id=<?= $os['id'] ?>" 
                                               class="nn-btn nn-btn-info nn-btn-sm"
                                               title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['sucesso'])): ?>
    <div class="nn-alert nn-alert-success nn-animate-fade">
        <i class="fas fa-check-circle"></i>
        <?php
        switch($_GET['sucesso']) {
            case 'os_criada':
                echo 'Ordem de Serviço ' . htmlspecialchars($_GET['numero'] ?? '') . ' criada com sucesso!';
                if (isset($_GET['cliente_cadastrado'])) {
                    echo '<br><small>Cliente também foi cadastrado no sistema.</small>';
                }
                break;
            case 'os_excluida':
                echo 'Ordem de Serviço ' . htmlspecialchars($_GET['numero'] ?? '') . ' excluída com sucesso!';
                break;
            default:
                echo 'Operação realizada com sucesso!';
        }
        ?>
    </div>
<?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php
$stmt->close();
$conn->close();
require_once '../includes/footer.php';
?>