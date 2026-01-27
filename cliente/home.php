<?php
/**
 * Home do Cliente - NetoNerd ITSM v2.0
 * Dashboard principal para clientes
 */

require_once "../controller/validador_acesso.php";
require_once "../config/bandoDeDados/conexao.php";

$conn = getConnection();
$usuario_id = $_SESSION['id'];

// Busca dados do cliente
$stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$cliente = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Estatísticas do cliente
$stmt = $conn->prepare("
    SELECT
        COUNT(*) as total,
        SUM(CASE WHEN status IN ('aberto', 'em andamento') THEN 1 ELSE 0 END) as ativos,
        SUM(CASE WHEN status = 'resolvido' THEN 1 ELSE 0 END) as resolvidos,
        SUM(CASE WHEN status = 'cancelado' THEN 1 ELSE 0 END) as cancelados
    FROM chamados
    WHERE cliente_id = ?
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Configuração da página
$page_title = "Minha Conta - NetoNerd ITSM";

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
                        <?php
                        echo ($cliente['genero'] === 'Feminino' ? 'Bem-vinda, ' : 'Bem-vindo, ') .
                             htmlspecialchars(explode(' ', $cliente['nome'])[0]) . '!';
                        ?>
                    </h1>
                    <p class="text-muted mb-0">
                        <i class="fas fa-envelope"></i>
                        <?php echo htmlspecialchars($cliente['email']); ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Dashboard Stats -->
        <div class="nn-stats-grid nn-animate-slide">
            <div class="nn-stat-card primary">
                <div class="nn-stat-icon primary">
                    <i class="fas fa-ticket-alt"></i>
                </div>
                <div class="nn-stat-value"><?php echo $stats['total']; ?></div>
                <div class="nn-stat-label">Total de Chamados</div>
            </div>

            <div class="nn-stat-card warning">
                <div class="nn-stat-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="nn-stat-value"><?php echo $stats['ativos']; ?></div>
                <div class="nn-stat-label">Em Atendimento</div>
            </div>

            <div class="nn-stat-card success">
                <div class="nn-stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="nn-stat-value"><?php echo $stats['resolvidos']; ?></div>
                <div class="nn-stat-label">Resolvidos</div>
            </div>

            <div class="nn-stat-card danger">
                <div class="nn-stat-icon danger">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="nn-stat-value"><?php echo $stats['cancelados']; ?></div>
                <div class="nn-stat-label">Cancelados</div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Ações Rápidas -->
            <div class="col-lg-4">
                <div class="nn-card nn-animate-fade">
                    <div class="nn-card-header">
                        <h2 class="nn-card-title">
                            <i class="fas fa-bolt"></i>
                            Ações Rápidas
                        </h2>
                    </div>
                    <div class="nn-card-body">
                        <a href="abrir_chamado.php" class="nn-action-btn mb-3">
                            <div class="nn-action-icon primary">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div>
                                <strong>Novo Chamado</strong><br>
                                <small class="text-muted">Solicitar atendimento técnico</small>
                            </div>
                        </a>

                        <a href="meus_chamados.php" class="nn-action-btn mb-3">
                            <div class="nn-action-icon info">
                                <i class="fas fa-list"></i>
                            </div>
                            <div>
                                <strong>Meus Chamados</strong><br>
                                <small class="text-muted">Ver todos os atendimentos</small>
                            </div>
                        </a>

                        <a href="minha_conta.php" class="nn-action-btn mb-3">
                            <div class="nn-action-icon success">
                                <i class="fas fa-user-cog"></i>
                            </div>
                            <div>
                                <strong>Minha Conta</strong><br>
                                <small class="text-muted">Editar dados pessoais</small>
                            </div>
                        </a>

                        <a href="contato.php" class="nn-action-btn">
                            <div class="nn-action-icon warning">
                                <i class="fas fa-headset"></i>
                            </div>
                            <div>
                                <strong>Suporte</strong><br>
                                <small class="text-muted">Falar com nossa equipe</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Chamados Recentes -->
            <div class="col-lg-8">
                <div class="nn-card nn-animate-fade">
                    <div class="nn-card-header">
                        <h2 class="nn-card-title">
                            <i class="fas fa-history"></i>
                            Chamados Recentes
                        </h2>
                    </div>
                    <div class="nn-card-body">
                        <?php
                        $stmt = $conn->prepare("
                            SELECT * FROM chamados
                            WHERE cliente_id = ?
                            ORDER BY data_abertura DESC
                            LIMIT 5
                        ");
                        $stmt->bind_param("i", $usuario_id);
                        $stmt->execute();
                        $chamados = $stmt->get_result();

                        if ($chamados->num_rows > 0):
                            while ($chamado = $chamados->fetch_assoc()):
                                // Classes de status
                                $statusClass = match(strtolower($chamado['status'])) {
                                    'aberto' => 'nn-badge-primary',
                                    'em andamento' => 'nn-badge-info',
                                    'resolvido' => 'nn-badge-success',
                                    'cancelado' => 'nn-badge-danger',
                                    default => 'nn-badge-secondary'
                                };
                        ?>
                            <div class="nn-chamado-item" onclick="window.location.href='visualizar_chamado.php?id=<?php echo $chamado['id']; ?>'">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0">
                                        <?php echo htmlspecialchars($chamado['titulo']); ?>
                                    </h6>
                                    <span class="text-muted">
                                        <strong>#<?php echo htmlspecialchars($chamado['protocolo']); ?></strong>
                                    </span>
                                </div>
                                <div class="d-flex gap-2 flex-wrap">
                                    <span class="nn-badge <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($chamado['status']); ?>
                                    </span>
                                    <span class="text-muted">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('d/m/Y', strtotime($chamado['data_abertura'])); ?>
                                    </span>
                                    <?php if ($chamado['categoria']): ?>
                                        <span class="text-muted">
                                            <i class="fas fa-tag"></i>
                                            <?php echo htmlspecialchars($chamado['categoria']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php
                            endwhile;
                        else:
                        ?>
                            <div class="text-center py-5">
                                <i class="fas fa-inbox" style="font-size: 4rem; color: #ddd; margin-bottom: 1rem;"></i>
                                <h5 class="text-muted">Nenhum chamado ainda</h5>
                                <p class="text-muted">Você ainda não abriu nenhum chamado de suporte.</p>
                                <a href="abrir_chamado.php" class="nn-btn nn-btn-primary">
                                    <i class="fas fa-plus"></i>
                                    Abrir Primeiro Chamado
                                </a>
                            </div>
                        <?php endif; ?>

                        <?php if ($chamados->num_rows > 0): ?>
                            <div class="text-center mt-3">
                                <a href="meus_chamados.php" class="nn-btn nn-btn-secondary">
                                    Ver Todos os Chamados
                                    <i class="fas fa-arrow-right"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
/* Estilos específicos para a home do cliente */
.nn-action-btn {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    border-radius: 10px;
    border: 2px solid var(--border-color);
    background: white;
    color: var(--text-dark);
    text-decoration: none;
    transition: all 0.3s ease;
    width: 100%;
}

.nn-action-btn:hover {
    border-color: var(--primary-blue);
    background: #f8f9fa;
    transform: translateX(5px);
    text-decoration: none;
    color: var(--primary-blue);
}

.nn-action-icon {
    width: 50px;
    height: 50px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}

.nn-action-icon.primary {
    background: var(--gradient-primary);
    color: white;
}

.nn-action-icon.info {
    background: linear-gradient(135deg, #17a2b8, #117a8b);
    color: white;
}

.nn-action-icon.success {
    background: linear-gradient(135deg, #28a745, #1e7e34);
    color: white;
}

.nn-action-icon.warning {
    background: linear-gradient(135deg, #ffc107, #e0a800);
    color: white;
}

.nn-chamado-item {
    padding: 15px;
    border-radius: 8px;
    background: #f8f9fa;
    margin-bottom: 12px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.nn-chamado-item:hover {
    background: #e9ecef;
    transform: translateX(5px);
}

.nn-stat-card.danger .nn-stat-icon {
    background: linear-gradient(135deg, #dc3545, #c82333);
}
</style>

<?php
// Incluir footer
$stmt->close();
$conn->close();
require_once '../includes/footer.php';
?>
