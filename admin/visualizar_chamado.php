<?php
/**
 * Visualizar Detalhes do Chamado - Admin - NetoNerd ITSM v2.0
 */
session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireAdmin();

$conn = getConnection();

// Validar ID do chamado
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: chamados_ativos.php?erro=chamado_invalido');
    exit();
}

$chamado_id = intval($_GET['id']);

// Buscar detalhes completos do chamado - AJUSTADO PARA AS COLUNAS REAIS
$sql = "
    SELECT
        c.*,
        IFNULL(cl.nome, c.nome_usuario) as cliente_nome,
        cl.email as cliente_email,
        cl.telefone as cliente_telefone,
        t.nome as tecnico_nome,
        t.matricula as tecnico_matricula,
        t.email as tecnico_email
    FROM chamados c
    LEFT JOIN clientes cl ON c.cliente_id = cl.id
    LEFT JOIN tecnicos t ON c.tecnico_id = t.id
    WHERE c.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $chamado_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: chamados_ativos.php?erro=chamado_nao_encontrado');
    exit();
}

$chamado = $result->fetch_assoc();

// Buscar histórico se a tabela existir (comentado para não quebrar)
$historico_items = [];
// Descomente se tiver a tabela historico_interacoes ou historico_atendimento
/*
$sql_historico = "
    SELECT 
        *
    FROM historico_atendimento
    WHERE chamado_id = ?
    ORDER BY created_at DESC
";
$stmt_historico = $conn->prepare($sql_historico);
$stmt_historico->bind_param('i', $chamado_id);
$stmt_historico->execute();
$historico = $stmt_historico->get_result();
while ($row = $historico->fetch_assoc()) {
    $historico_items[] = $row;
}
*/

$page_title = "Chamado #" . $chamado['protocolo'] . " - NetoNerd ITSM";
require_once '../includes/header.php';
?>

<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <!-- Cabeçalho -->
        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <div>
                    <h1 class="nn-card-title">
                        <i class="fas fa-ticket-alt"></i>
                        Chamado #<?= htmlspecialchars($chamado['protocolo']) ?>
                    </h1>
                    <p class="text-muted mb-0">Aberto em <?= date('d/m/Y \à\s H:i', strtotime($chamado['data_abertura'])) ?></p>
                </div>
                <div>
                    <a href="chamados_ativos.php" class="nn-btn nn-btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    <?php if (!$chamado['tecnico_id']): ?>
                        <a href="atribuir_chamados.php?chamado=<?= $chamado['id'] ?>" class="nn-btn nn-btn-success">
                            <i class="fas fa-user-plus"></i> Atribuir Técnico
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Coluna Esquerda - Detalhes Principais -->
            <div class="col-lg-8">
                
                <!-- Informações do Chamado -->
                <div class="nn-card nn-animate-slide">
                    <div class="nn-card-header">
                        <h2 class="nn-card-title">
                            <i class="fas fa-info-circle"></i>
                            Detalhes do Chamado
                        </h2>
                        <div>
                            <span class="nn-badge nn-badge-<?= 
                                $chamado['status'] === 'aberto' ? 'primary' : 
                                ($chamado['status'] === 'em andamento' ? 'info' : 
                                ($chamado['status'] === 'pendente' ? 'warning' : 
                                ($chamado['status'] === 'resolvido' ? 'success' : 'danger')))
                            ?>">
                                <?= ucfirst($chamado['status']) ?>
                            </span>
                            <span class="nn-badge nn-badge-<?= 
                                $chamado['prioridade'] === 'critica' ? 'critical' : 
                                ($chamado['prioridade'] === 'alta' ? 'high' : 
                                ($chamado['prioridade'] === 'media' ? 'medium' : 'low'))
                            ?>">
                                <?= ucfirst($chamado['prioridade']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="nn-card-body">
                        <div class="mb-4">
                            <h4 class="text-primary"><?= htmlspecialchars($chamado['titulo']) ?></h4>
                        </div>
                        
                        <div class="mb-4">
                            <strong><i class="fas fa-align-left"></i> Descrição:</strong>
                            <p class="mt-2"><?= nl2br(htmlspecialchars($chamado['descricao'])) ?></p>
                        </div>

                        <?php if ($chamado['categoria']): ?>
                            <div class="mb-3">
                                <strong><i class="fas fa-tag"></i> Categoria:</strong>
                                <span class="nn-badge nn-badge-info ms-2">
                                    <?= htmlspecialchars($chamado['categoria']) ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <?php if ($chamado['pagamento_forma']): ?>
                            <div class="mb-3">
                                <strong><i class="fas fa-credit-card"></i> Forma de Pagamento:</strong>
                                <span class="nn-badge nn-badge-secondary ms-2">
                                    <?= htmlspecialchars($chamado['pagamento_forma']) ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <?php if ($chamado['stylemanager_software']): ?>
                            <div class="mb-3">
                                <span class="nn-badge nn-badge-warning">
                                    <i class="fas fa-desktop"></i> Style Manager Software
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Histórico de Atendimento -->
                <?php if ($chamado['historico_atendimento']): ?>
                    <div class="nn-card nn-animate-slide">
                        <div class="nn-card-header">
                            <h2 class="nn-card-title">
                                <i class="fas fa-history"></i>
                                Histórico de Atendimento
                            </h2>
                        </div>
                        <div class="nn-card-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-marker"></div>
                                    <div class="timeline-content">
                                        <?= nl2br(htmlspecialchars($chamado['historico_atendimento'])) ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Interações Adicionais (se houver na tabela) -->
                <?php if (!empty($historico_items)): ?>
                    <div class="nn-card nn-animate-slide">
                        <div class="nn-card-header">
                            <h2 class="nn-card-title">
                                <i class="fas fa-comments"></i>
                                Interações
                            </h2>
                        </div>
                        <div class="nn-card-body">
                            <div class="timeline">
                                <?php foreach ($historico_items as $item): ?>
                                    <div class="timeline-item">
                                        <div class="timeline-marker"></div>
                                        <div class="timeline-content">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <strong>Atualização</strong>
                                                <small class="text-muted">
                                                    <?= date('d/m/Y H:i', strtotime($item['created_at'])) ?>
                                                </small>
                                            </div>
                                            <p class="mb-0"><?= nl2br(htmlspecialchars($item['descricao'] ?? $item['observacao'] ?? '')) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

            </div>

            <!-- Coluna Direita - Informações Adicionais -->
            <div class="col-lg-4">
                
                <!-- Informações do Cliente -->
                <div class="nn-card nn-animate-slide">
                    <div class="nn-card-header">
                        <h3 class="nn-card-title">
                            <i class="fas fa-user"></i>
                            Cliente
                        </h3>
                    </div>
                    <div class="nn-card-body">
                        <div class="mb-3">
                            <strong>Nome:</strong>
                            <p class="mb-0"><?= htmlspecialchars($chamado['cliente_nome']) ?></p>
                        </div>
                        <?php if ($chamado['cliente_email']): ?>
                            <div class="mb-3">
                                <strong>Email:</strong>
                                <p class="mb-0">
                                    <a href="mailto:<?= htmlspecialchars($chamado['cliente_email']) ?>">
                                        <?= htmlspecialchars($chamado['cliente_email']) ?>
                                    </a>
                                </p>
                            </div>
                        <?php endif; ?>
                        <?php if ($chamado['cliente_telefone']): ?>
                            <div class="mb-3">
                                <strong>Telefone:</strong>
                                <p class="mb-0">
                                    <a href="tel:<?= htmlspecialchars($chamado['cliente_telefone']) ?>">
                                        <?= htmlspecialchars($chamado['cliente_telefone']) ?>
                                    </a>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Técnico Responsável -->
                <div class="nn-card nn-animate-slide">
                    <div class="nn-card-header">
                        <h3 class="nn-card-title">
                            <i class="fas fa-user-cog"></i>
                            Técnico Responsável
                        </h3>
                    </div>
                    <div class="nn-card-body">
                        <?php if ($chamado['tecnico_nome']): ?>
                            <div class="mb-3">
                                <strong>Nome:</strong>
                                <p class="mb-0"><?= htmlspecialchars($chamado['tecnico_nome']) ?></p>
                            </div>
                            <div class="mb-3">
                                <strong>Matrícula:</strong>
                                <p class="mb-0">
                                    <span class="nn-badge nn-badge-info">
                                        <?= htmlspecialchars($chamado['tecnico_matricula']) ?>
                                    </span>
                                </p>
                            </div>
                            <?php if ($chamado['tecnico_email']): ?>
                                <div class="mb-3">
                                    <strong>Email:</strong>
                                    <p class="mb-0">
                                        <a href="mailto:<?= htmlspecialchars($chamado['tecnico_email']) ?>">
                                            <?= htmlspecialchars($chamado['tecnico_email']) ?>
                                        </a>
                                    </p>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="nn-alert nn-alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                Chamado ainda não atribuído
                            </div>
                            <a href="atribuir_chamados.php?chamado=<?= $chamado['id'] ?>" class="nn-btn nn-btn-success w-100">
                                <i class="fas fa-user-plus"></i> Atribuir Agora
                            </a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Cronologia -->
                <div class="nn-card nn-animate-slide">
                    <div class="nn-card-header">
                        <h3 class="nn-card-title">
                            <i class="fas fa-clock"></i>
                            Cronologia
                        </h3>
                    </div>
                    <div class="nn-card-body">
                        <div class="mb-3">
                            <strong>Abertura:</strong>
                            <p class="mb-0"><?= date('d/m/Y H:i', strtotime($chamado['data_abertura'])) ?></p>
                        </div>
                        <?php if ($chamado['data_atualizacao']): ?>
                            <div class="mb-3">
                                <strong>Última Atualização:</strong>
                                <p class="mb-0"><?= date('d/m/Y H:i', strtotime($chamado['data_atualizacao'])) ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if ($chamado['data_inicio_atendimento']): ?>
                            <div class="mb-3">
                                <strong>Início do Atendimento:</strong>
                                <p class="mb-0"><?= date('d/m/Y H:i', strtotime($chamado['data_inicio_atendimento'])) ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if ($chamado['data_resolucao']): ?>
                            <div class="mb-3">
                                <strong>Resolução:</strong>
                                <p class="mb-0"><?= date('d/m/Y H:i', strtotime($chamado['data_resolucao'])) ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if ($chamado['data_fechamento']): ?>
                            <div class="mb-3">
                                <strong>Fechamento:</strong>
                                <p class="mb-0"><?= date('d/m/Y H:i', strtotime($chamado['data_fechamento'])) ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if ($chamado['tempo_atendimento_minutos']): ?>
                            <div class="mb-3">
                                <strong>Tempo de Atendimento:</strong>
                                <p class="mb-0">
                                    <span class="nn-badge nn-badge-info">
                                        <?= $chamado['tempo_atendimento_minutos'] ?> minutos
                                    </span>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<style>
/* Timeline de Histórico */
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-item:not(:last-child)::before {
    content: '';
    position: absolute;
    left: -24px;
    top: 20px;
    height: calc(100% + 10px);
    width: 2px;
    background: var(--border-color);
}

.timeline-marker {
    position: absolute;
    left: -30px;
    top: 5px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: var(--primary);
    border: 2px solid white;
    box-shadow: 0 0 0 2px var(--primary);
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid var(--primary);
}
</style>

<?php
$stmt->close();
$conn->close();
require_once '../includes/footer.php';
?>