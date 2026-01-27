<?php
/**
 * Painel do Técnico - NetoNerd ITSM v2.0
 * Dashboard principal para técnicos
 */

session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

// PROTEÇÃO: Apenas técnicos
requireTecnico();

$conn = getConnection();
$tecnico_id = $_SESSION['usuario_id'];

// Buscar informações do técnico
$sql_tecnico = "SELECT nome, matricula, carro_do_dia, email FROM tecnicos WHERE id = ?";
$stmt_tecnico = $conn->prepare($sql_tecnico);
$stmt_tecnico->bind_param("i", $tecnico_id);
$stmt_tecnico->execute();
$tecnico = $stmt_tecnico->get_result()->fetch_assoc();

// Estatísticas do técnico
$stats_sql = "
    SELECT
        COUNT(*) as total,
        COUNT(CASE WHEN status = 'aberto' THEN 1 END) as abertos,
        COUNT(CASE WHEN status = 'em andamento' THEN 1 END) as em_andamento,
        COUNT(CASE WHEN status = 'pendente' THEN 1 END) as pendentes,
        COUNT(CASE WHEN status = 'resolvido' THEN 1 END) as resolvidos,
        AVG(CASE WHEN tempo_atendimento_minutos IS NOT NULL THEN tempo_atendimento_minutos END) as tempo_medio,
        SUM(CASE WHEN DATE(data_abertura) = CURDATE() THEN 1 ELSE 0 END) as hoje
    FROM chamados
    WHERE tecnico_id = ? AND status != 'cancelado'
";
$stmt_stats = $conn->prepare($stats_sql);
$stmt_stats->bind_param("i", $tecnico_id);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();

// Chamados urgentes (críticos ou altos abertos há mais de 24h)
$urgentes_sql = "
    SELECT COUNT(*) as urgentes
    FROM chamados
    WHERE tecnico_id = ?
      AND status IN ('aberto', 'em andamento')
      AND prioridade IN ('critica', 'alta')
      AND TIMESTAMPDIFF(HOUR, data_abertura, NOW()) > 24
";
$stmt_urgentes = $conn->prepare($urgentes_sql);
$stmt_urgentes->bind_param("i", $tecnico_id);
$stmt_urgentes->execute();
$urgentes = $stmt_urgentes->get_result()->fetch_assoc()['urgentes'];

// Buscar chamados recentes (últimos 5)
$recentes_sql = "
    SELECT
        c.id,
        c.protocolo,
        c.titulo,
        c.status,
        c.prioridade,
        c.data_abertura,
        cl.nome as cliente_nome,
        TIMESTAMPDIFF(HOUR, c.data_abertura, NOW()) as horas_abertas
    FROM chamados c
    INNER JOIN clientes cl ON c.cliente_id = cl.id
    WHERE c.tecnico_id = ?
      AND c.status != 'resolvido'
      AND c.status != 'cancelado'
    ORDER BY
        CASE c.prioridade
            WHEN 'critica' THEN 1
            WHEN 'alta' THEN 2
            WHEN 'media' THEN 3
            WHEN 'baixa' THEN 4
        END,
        c.data_abertura ASC
    LIMIT 5
";
$stmt_recentes = $conn->prepare($recentes_sql);
$stmt_recentes->bind_param("i", $tecnico_id);
$stmt_recentes->execute();
$recentes = $stmt_recentes->get_result();

// Configuração da página
$page_title = "Painel do Técnico - NetoNerd ITSM";

// Incluir header
require_once '../includes/header.php';
?>

<!-- Conteúdo Principal -->
<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <!-- Cabeçalho de Boas-Vindas -->
        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <div>
                    <h1 class="nn-card-title mb-2">
                        <i class="fas fa-user-cog"></i>
                        Bem-vindo, <?php echo htmlspecialchars($tecnico['nome']); ?>!
                    </h1>
                    <div>
                        <span class="nn-badge nn-badge-info">
                            <i class="fas fa-id-card"></i>
                            Matrícula: <?php echo htmlspecialchars($tecnico['matricula']); ?>
                        </span>
                        <?php if ($tecnico['carro_do_dia']): ?>
                            <span class="nn-badge nn-badge-success">
                                <i class="fas fa-car"></i>
                                Veículo: <?php echo htmlspecialchars($tecnico['carro_do_dia']); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                <div>
                    <button class="nn-btn nn-btn-primary" onclick="location.href='meus_chamados.php'">
                        <i class="fas fa-list"></i>
                        Ver Todos os Chamados
                    </button>
                </div>
            </div>
        </div>

        <!-- Alerta de Chamados Urgentes -->
        <?php if ($urgentes > 0): ?>
            <div class="nn-alert nn-alert-danger nn-animate-fade">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Atenção!</strong> Você tem <?php echo $urgentes; ?> chamado(s) urgente(s) com mais de 24 horas em aberto!
                <a href="meus_chamados.php?prioridade=critica" class="nn-btn nn-btn-danger nn-btn-sm" style="float: right;">
                    Ver Urgentes
                </a>
            </div>
        <?php endif; ?>

        <!-- Dashboard Stats -->
        <div class="nn-stats-grid nn-animate-slide">
            <!-- Abertos -->
            <div class="nn-stat-card primary">
                <div class="nn-stat-icon primary">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div class="nn-stat-value"><?php echo $stats['abertos']; ?></div>
                <div class="nn-stat-label">Abertos</div>
            </div>

            <!-- Em Andamento -->
            <div class="nn-stat-card info">
                <div class="nn-stat-icon info">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="nn-stat-value"><?php echo $stats['em_andamento']; ?></div>
                <div class="nn-stat-label">Em Andamento</div>
            </div>

            <!-- Pendentes -->
            <div class="nn-stat-card warning">
                <div class="nn-stat-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="nn-stat-value"><?php echo $stats['pendentes']; ?></div>
                <div class="nn-stat-label">Pendentes</div>
            </div>

            <!-- Resolvidos -->
            <div class="nn-stat-card success">
                <div class="nn-stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="nn-stat-value"><?php echo $stats['resolvidos']; ?></div>
                <div class="nn-stat-label">Resolvidos</div>
            </div>
        </div>

        <!-- Estatísticas Adicionais -->
        <div class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="nn-card">
                    <div class="nn-card-body text-center">
                        <i class="fas fa-ticket-alt text-primary mb-2" style="font-size: 2rem;"></i>
                        <div class="nn-stat-value"><?php echo $stats['total']; ?></div>
                        <div class="nn-stat-label">Total de Chamados Ativos</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="nn-card">
                    <div class="nn-card-body text-center">
                        <i class="fas fa-calendar-day text-success mb-2" style="font-size: 2rem;"></i>
                        <div class="nn-stat-value"><?php echo $stats['hoje']; ?></div>
                        <div class="nn-stat-label">Recebidos Hoje</div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="nn-card">
                    <div class="nn-card-body text-center">
                        <i class="fas fa-clock text-info mb-2" style="font-size: 2rem;"></i>
                        <div class="nn-stat-value">
                            <?php
                            if ($stats['tempo_medio']) {
                                $horas = floor($stats['tempo_medio'] / 60);
                                $minutos = $stats['tempo_medio'] % 60;
                                echo $horas . "h " . $minutos . "m";
                            } else {
                                echo "N/A";
                            }
                            ?>
                        </div>
                        <div class="nn-stat-label">Tempo Médio de Resolução</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chamados Recentes / Prioritários -->
        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h2 class="nn-card-title">
                    <i class="fas fa-fire"></i>
                    Chamados Prioritários
                </h2>
                <div>
                    <button class="nn-btn nn-btn-primary nn-btn-sm" onclick="location.reload()">
                        <i class="fas fa-sync"></i>
                        Atualizar
                    </button>
                </div>
            </div>
            <div class="nn-card-body">
                <?php if ($recentes->num_rows > 0): ?>
                    <div class="nn-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Protocolo</th>
                                    <th>Título</th>
                                    <th>Cliente</th>
                                    <th>Prioridade</th>
                                    <th>Status</th>
                                    <th>Tempo Aberto</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($chamado = $recentes->fetch_assoc()):
                                    // Classes de prioridade
                                    $prioridadeClass = match(strtolower($chamado['prioridade'])) {
                                        'critica' => 'nn-badge-critical',
                                        'alta' => 'nn-badge-high',
                                        'media' => 'nn-badge-medium',
                                        'baixa' => 'nn-badge-low',
                                        default => 'nn-badge-secondary'
                                    };

                                    // Classes de status
                                    $statusClass = match(strtolower($chamado['status'])) {
                                        'aberto' => 'nn-badge-primary',
                                        'em andamento' => 'nn-badge-info',
                                        'pendente' => 'nn-badge-warning',
                                        default => 'nn-badge-secondary'
                                    };
                                ?>
                                    <tr>
                                        <td><strong>#<?php echo htmlspecialchars($chamado['protocolo']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($chamado['titulo']); ?></td>
                                        <td><?php echo htmlspecialchars($chamado['cliente_nome']); ?></td>
                                        <td>
                                            <span class="nn-badge <?php echo $prioridadeClass; ?>">
                                                <?php echo ucfirst($chamado['prioridade']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="nn-badge <?php echo $statusClass; ?>">
                                                <?php echo ucfirst($chamado['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $horas = $chamado['horas_abertas'];
                                            if ($horas >= 24) {
                                                echo floor($horas / 24) . " dia(s)";
                                            } else {
                                                echo $horas . "h";
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($chamado['status'] === 'aberto'): ?>
                                                <a href="processar_chamado.php?acao=iniciar&id=<?php echo $chamado['id']; ?>"
                                                   class="nn-btn nn-btn-success nn-btn-sm"
                                                   onclick="return confirm('Iniciar atendimento deste chamado?')">
                                                    <i class="fas fa-play"></i>
                                                    Iniciar
                                                </a>
                                            <?php elseif ($chamado['status'] === 'em andamento'): ?>
                                                <a href="resolver_chamado.php?id=<?php echo $chamado['id']; ?>"
                                                   class="nn-btn nn-btn-primary nn-btn-sm">
                                                    <i class="fas fa-check"></i>
                                                    Resolver
                                                </a>
                                            <?php else: ?>
                                                <a href="meus_chamados.php?id=<?php echo $chamado['id']; ?>"
                                                   class="nn-btn nn-btn-secondary nn-btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                    Ver
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="nn-alert nn-alert-success">
                        <i class="fas fa-check-circle"></i>
                        Parabéns! Você não tem chamados pendentes no momento.
                    </div>
                <?php endif; ?>

                <?php if ($recentes->num_rows > 0): ?>
                    <div class="text-center mt-3">
                        <a href="meus_chamados.php" class="nn-btn nn-btn-primary">
                            <i class="fas fa-list"></i>
                            Ver Todos os Chamados (<?php echo $stats['total']; ?>)
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<?php
// Incluir footer
$stmt_tecnico->close();
$stmt_stats->close();
$stmt_urgentes->close();
$stmt_recentes->close();
$conn->close();
require_once '../includes/footer.php';
?>
