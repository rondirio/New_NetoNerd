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

// Período para relatório (padrão: últimos 30 dias)
$data_inicio = $_GET['data_inicio'] ?? date('Y-m-d', strtotime('-30 days'));
$data_fim = $_GET['data_fim'] ?? date('Y-m-d');

// Estatísticas gerais
$stats_gerais = $conn->query("
    SELECT
        COUNT(*) as total_chamados,
        COUNT(CASE WHEN status = 'aberto' THEN 1 END) as abertos,
        COUNT(CASE WHEN status = 'em andamento' THEN 1 END) as em_andamento,
        COUNT(CASE WHEN status = 'resolvido' THEN 1 END) as resolvidos,
        COUNT(CASE WHEN status = 'cancelado' THEN 1 END) as cancelados,
        COUNT(CASE WHEN prioridade = 'critica' THEN 1 END) as criticos,
        COUNT(CASE WHEN prioridade = 'alta' THEN 1 END) as altos,
        AVG(TIMESTAMPDIFF(HOUR, data_abertura, COALESCE(data_fechamento, NOW()))) as tempo_medio_horas
    FROM chamados
    WHERE data_abertura BETWEEN '$data_inicio' AND '$data_fim 23:59:59'
")->fetch_assoc();

// Chamados por categoria
$por_categoria = $conn->query("
    SELECT
        cat.nome,
        cat.cor,
        COUNT(c.id) as total
    FROM categorias_chamado cat
    LEFT JOIN chamados c ON cat.id = c.categoria_id
        AND c.data_abertura BETWEEN '$data_inicio' AND '$data_fim 23:59:59'
    GROUP BY cat.id, cat.nome, cat.cor
    ORDER BY total DESC
");

// Desempenho dos técnicos
$desempenho_tecnicos = $conn->query("
    SELECT
        t.nome,
        t.matricula,
        COUNT(c.id) as total_chamados,
        COUNT(CASE WHEN c.status = 'resolvido' THEN 1 END) as resolvidos,
        ROUND(COUNT(CASE WHEN c.status = 'resolvido' THEN 1 END) * 100.0 / NULLIF(COUNT(c.id), 0), 2) as taxa_resolucao,
        AVG(TIMESTAMPDIFF(HOUR, c.data_abertura, c.data_fechamento)) as tempo_medio_resolucao
    FROM tecnicos t
    LEFT JOIN chamados c ON t.id = c.tecnico_id
        AND c.data_abertura BETWEEN '$data_inicio' AND '$data_fim 23:59:59'
    WHERE t.Ativo = 1
    GROUP BY t.id, t.nome, t.matricula
    ORDER BY total_chamados DESC
");

// Chamados por dia (para gráfico)
$por_dia = $conn->query("
    SELECT
        DATE(data_abertura) as data,
        COUNT(*) as total
    FROM chamados
    WHERE data_abertura BETWEEN '$data_inicio' AND '$data_fim 23:59:59'
    GROUP BY DATE(data_abertura)
    ORDER BY data ASC
");

$dados_grafico = [];
while ($row = $por_dia->fetch_assoc()) {
    $dados_grafico[] = $row;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatórios - NetoNerd Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
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
        .stat-card {
            text-align: center;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .stat-card h3 {
            color: #667eea;
            font-size: 36px;
            margin: 10px 0;
        }
        .stat-card p {
            color: #666;
            margin: 0;
        }
        .chart-container {
            position: relative;
            height: 300px;
        }
    </style>
</head>
<body>
    <?php if(file_exists('../routes/header_admin.php')) include '../routes/header_admin.php'; ?>

    <div class="container-fluid main-container">
        <!-- Filtro de Período -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" class="form-inline">
                            <label class="mr-2">Período:</label>
                            <input type="date" name="data_inicio" class="form-control mr-2" value="<?php echo $data_inicio; ?>">
                            <label class="mr-2">até</label>
                            <input type="date" name="data_fim" class="form-control mr-2" value="<?php echo $data_fim; ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <a href="?data_inicio=<?php echo date('Y-m-d', strtotime('-7 days')); ?>&data_fim=<?php echo date('Y-m-d'); ?>" class="btn btn-secondary ml-2">Últimos 7 dias</a>
                            <a href="?data_inicio=<?php echo date('Y-m-d', strtotime('-30 days')); ?>&data_fim=<?php echo date('Y-m-d'); ?>" class="btn btn-secondary ml-2">Últimos 30 dias</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estatísticas Gerais -->
        <div class="row">
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-ticket-alt fa-2x text-primary"></i>
                    <h3><?php echo $stats_gerais['total_chamados']; ?></h3>
                    <p>Total de Chamados</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-check-circle fa-2x text-success"></i>
                    <h3><?php echo $stats_gerais['resolvidos']; ?></h3>
                    <p>Resolvidos</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-spinner fa-2x text-warning"></i>
                    <h3><?php echo $stats_gerais['em_andamento']; ?></h3>
                    <p>Em Andamento</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <i class="fas fa-clock fa-2x text-info"></i>
                    <h3><?php echo round($stats_gerais['tempo_medio_horas'], 1); ?>h</h3>
                    <p>Tempo Médio</p>
                </div>
            </div>
        </div>

        <!-- Gráfico de Chamados por Dia -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-chart-line"></i> Chamados por Dia</h4>
                    </div>
                    <div class="card-body">
                        <div class="chart-container">
                            <canvas id="graficoTendencia"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chamados por Categoria -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-tags"></i> Chamados por Categoria</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Categoria</th>
                                    <th class="text-right">Total</th>
                                    <th class="text-right">%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $por_categoria->data_seek(0);
                                while ($cat = $por_categoria->fetch_assoc()):
                                    $percentual = $stats_gerais['total_chamados'] > 0
                                        ? round(($cat['total'] / $stats_gerais['total_chamados']) * 100, 1)
                                        : 0;
                                ?>
                                    <tr>
                                        <td>
                                            <span style="display: inline-block; width: 15px; height: 15px; background: <?php echo $cat['cor']; ?>; border-radius: 3px; margin-right: 8px;"></span>
                                            <?php echo htmlspecialchars($cat['nome']); ?>
                                        </td>
                                        <td class="text-right"><strong><?php echo $cat['total']; ?></strong></td>
                                        <td class="text-right"><?php echo $percentual; ?>%</td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Desempenho dos Técnicos -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0"><i class="fas fa-users"></i> Desempenho dos Técnicos</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Técnico</th>
                                    <th class="text-right">Total</th>
                                    <th class="text-right">Resolvidos</th>
                                    <th class="text-right">Taxa %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($tec = $desempenho_tecnicos->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($tec['nome']); ?></td>
                                        <td class="text-right"><?php echo $tec['total_chamados']; ?></td>
                                        <td class="text-right"><?php echo $tec['resolvidos']; ?></td>
                                        <td class="text-right">
                                            <span class="badge badge-<?php echo $tec['taxa_resolucao'] >= 80 ? 'success' : ($tec['taxa_resolucao'] >= 50 ? 'warning' : 'danger'); ?>">
                                                <?php echo $tec['taxa_resolucao'] ?? 0; ?>%
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botões de Exportação -->
        <div class="row">
            <div class="col-12 text-center">
                <button onclick="window.print()" class="btn btn-primary btn-lg">
                    <i class="fas fa-print"></i> Imprimir Relatório
                </button>
                <button onclick="exportarPDF()" class="btn btn-success btn-lg ml-2">
                    <i class="fas fa-file-pdf"></i> Exportar PDF
                </button>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gráfico de Tendência
        const dadosGrafico = <?php echo json_encode($dados_grafico); ?>;
        const labels = dadosGrafico.map(d => {
            const data = new Date(d.data + 'T00:00:00');
            return data.toLocaleDateString('pt-BR', { day: '2-digit', month: '2-digit' });
        });
        const valores = dadosGrafico.map(d => d.total);

        const ctx = document.getElementById('graficoTendencia').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Chamados',
                    data: valores,
                    borderColor: '#667eea',
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        function exportarPDF() {
            alert('Funcionalidade de exportação PDF em desenvolvimento');
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
