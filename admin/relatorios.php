<?php
/**
 * Relatórios - NetoNerd ITSM v2.0
 * Página de relatórios e estatísticas detalhadas
 */

require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireAdmin();

$conn = getConnection();

// Período para relatório (padrão: últimos 30 dias)
function validarDataFiltro($valor, $padrao) {
    if (is_string($valor) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $valor)) {
        $partes = explode('-', $valor);
        if (checkdate((int)$partes[1], (int)$partes[2], (int)$partes[0])) {
            return $valor;
        }
    }
    return $padrao;
}

$data_inicio = validarDataFiltro($_GET['data_inicio'] ?? null, date('Y-m-d', strtotime('-30 days')));
$data_fim = validarDataFiltro($_GET['data_fim'] ?? null, date('Y-m-d'));

// Estatísticas gerais
$stmt_stats = $conn->prepare("
    SELECT
        COUNT(*) as total_chamados,
        COUNT(CASE WHEN status = 'aberto' THEN 1 END) as abertos,
        COUNT(CASE WHEN status = 'em andamento' THEN 1 END) as em_andamento,
        COUNT(CASE WHEN status = 'resolvido' THEN 1 END) as resolvidos,
        COUNT(CASE WHEN status = 'cancelado' THEN 1 END) as cancelados,
        AVG(TIMESTAMPDIFF(HOUR, data_abertura, COALESCE(data_resolucao, NOW()))) as tempo_medio_horas
    FROM chamados
    WHERE data_abertura BETWEEN ? AND CONCAT(?, ' 23:59:59')
");
$stmt_stats->bind_param('ss', $data_inicio, $data_fim);
$stmt_stats->execute();
$stats_gerais = $stmt_stats->get_result()->fetch_assoc();
$stmt_stats->close();

// Desempenho dos técnicos
$stmt_desempenho = $conn->prepare("
    SELECT
        t.nome,
        COUNT(c.id) as total,
        COUNT(CASE WHEN c.status = 'resolvido' THEN 1 END) as resolvidos,
        AVG(CASE WHEN c.tempo_atendimento_minutos IS NOT NULL THEN c.tempo_atendimento_minutos END) as tempo_medio
    FROM tecnicos t
    LEFT JOIN chamados c ON t.id = c.tecnico_id
        AND c.data_abertura BETWEEN ? AND CONCAT(?, ' 23:59:59')
    GROUP BY t.id, t.nome
    ORDER BY resolvidos DESC
    LIMIT 10
");
$stmt_desempenho->bind_param('ss', $data_inicio, $data_fim);
$stmt_desempenho->execute();
$desempenho_tecnicos = $stmt_desempenho->get_result();
$stmt_desempenho->close();

$page_title = "Relatórios - NetoNerd ITSM";
require_once '../includes/header.php';
?>

<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h1 class="nn-card-title">
                    <i class="fas fa-chart-bar"></i>
                    Relatórios e Estatísticas
                </h1>
            </div>
        </div>

        <!-- Filtro de Período -->
        <div class="nn-card nn-animate-slide">
            <div class="nn-card-body">
                <form method="GET" action="">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="nn-form-group">
                                <label class="nn-form-label" for="data_inicio">Data Início</label>
                                <input type="date" id="data_inicio" name="data_inicio" class="nn-form-control" value="<?php echo htmlspecialchars($data_inicio); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="nn-form-group">
                                <label class="nn-form-label" for="data_fim">Data Fim</label>
                                <input type="date" id="data_fim" name="data_fim" class="nn-form-control" value="<?php echo htmlspecialchars($data_fim); ?>">
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="nn-btn nn-btn-primary" style="width: 100%;">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Stats Gerais -->
        <div class="nn-stats-grid nn-animate-slide">
            <div class="nn-stat-card primary">
                <div class="nn-stat-icon primary">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="nn-stat-value"><?php echo $stats_gerais['total_chamados']; ?></div>
                <div class="nn-stat-label">Total de Chamados</div>
            </div>

            <div class="nn-stat-card warning">
                <div class="nn-stat-icon warning">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div class="nn-stat-value"><?php echo $stats_gerais['abertos']; ?></div>
                <div class="nn-stat-label">Abertos</div>
            </div>

            <div class="nn-stat-card info">
                <div class="nn-stat-icon info">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="nn-stat-value"><?php echo $stats_gerais['em_andamento']; ?></div>
                <div class="nn-stat-label">Em Andamento</div>
            </div>

            <div class="nn-stat-card success">
                <div class="nn-stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="nn-stat-value"><?php echo $stats_gerais['resolvidos']; ?></div>
                <div class="nn-stat-label">Resolvidos</div>
            </div>
        </div>

        <!-- Desempenho dos Técnicos -->
        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h2 class="nn-card-title">
                    <i class="fas fa-trophy"></i>
                    Top 10 Técnicos - Desempenho no Período
                </h2>
            </div>
            <div class="nn-card-body">
                <div class="nn-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Posição</th>
                                <th>Técnico</th>
                                <th class="text-center">Total Atribuídos</th>
                                <th class="text-center">Resolvidos</th>
                                <th class="text-center">Tempo Médio</th>
                                <th class="text-center">Taxa de Resolução</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $posicao = 1;
                            while ($tec = $desempenho_tecnicos->fetch_assoc()):
                                $taxa = $tec['total'] > 0 ? round(($tec['resolvidos'] / $tec['total']) * 100) : 0;
                            ?>
                                <tr>
                                    <td>
                                        <span class="nn-badge <?php echo $posicao <= 3 ? 'nn-badge-warning' : 'nn-badge-secondary'; ?>">
                                            #<?php echo $posicao; ?>
                                        </span>
                                    </td>
                                    <td><strong><?php echo htmlspecialchars($tec['nome']); ?></strong></td>
                                    <td class="text-center"><?php echo $tec['total']; ?></td>
                                    <td class="text-center">
                                        <span class="nn-badge nn-badge-success"><?php echo $tec['resolvidos']; ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php
                                        if ($tec['tempo_medio']) {
                                            $horas = floor($tec['tempo_medio'] / 60);
                                            $mins = $tec['tempo_medio'] % 60;
                                            echo $horas . "h " . round($mins) . "m";
                                        } else {
                                            echo 'N/A';
                                        }
                                        ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="nn-badge <?php echo $taxa >= 70 ? 'nn-badge-success' : ($taxa >= 50 ? 'nn-badge-warning' : 'nn-badge-danger'); ?>">
                                            <?php echo $taxa; ?>%
                                        </span>
                                    </td>
                                </tr>
                            <?php
                                $posicao++;
                            endwhile;
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
$conn->close();
require_once '../includes/footer.php';
?>
