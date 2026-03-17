<?php
/**
 * Detalhes do Chamado - NetoNerd ITSM v2.0
 * Visualização completa de chamado para clientes
 */

session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireCliente();

$conn = getConnection();
$cliente_id = $_SESSION['usuario_id'];
$chamado_id = intval($_GET['id'] ?? 0);

if ($chamado_id === 0) {
    header('Location: meus_chamados.php?erro=id_invalido');
    exit();
}

// Buscar dados completos do chamado
$stmt = $conn->prepare("
    SELECT
        c.*,
        t.nome as tecnico_nome,
        t.matricula as tecnico_matricula,
        t.telefone as tecnico_telefone,
        cat.nome as categoria_nome,
        cat.cor as categoria_cor,
        cat.icone as categoria_icone
    FROM chamados c
    LEFT JOIN tecnicos t ON c.tecnico_id = t.id
    LEFT JOIN categorias_chamado cat ON c.categoria_id = cat.id
    WHERE c.id = ? AND c.cliente_id = ?
");
$stmt->bind_param("ii", $chamado_id, $cliente_id);
$stmt->execute();
$chamado = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$chamado) {
    header('Location: meus_chamados.php?erro=chamado_nao_encontrado');
    exit();
}

// Buscar atualizações/histórico
$stmt = $conn->prepare("
    SELECT * FROM chamado_atualizacoes
    WHERE chamado_id = ?
    ORDER BY data_atualizacao DESC
");
$stmt->bind_param("i", $chamado_id);
$stmt->execute();
$atualizacoes = $stmt->get_result();

// Buscar fotos
$stmt = $conn->prepare("
    SELECT * FROM chamado_fotos
    WHERE chamado_id = ?
    ORDER BY data_upload DESC
");
$stmt->bind_param("i", $chamado_id);
$stmt->execute();
$fotos = $stmt->get_result();

$page_title = "Chamado #" . $chamado['protocolo'] . " - NetoNerd ITSM";
require_once '../includes/header.php';
?>

<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <!-- Header -->
        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h1 class="nn-card-title">
                    <i class="fas fa-ticket-alt"></i>
                    Chamado #<?php echo htmlspecialchars($chamado['protocolo']); ?>
                </h1>
                <div>
                    <a href="meus_chamados.php" class="nn-btn nn-btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Voltar
                    </a>
                </div>
            </div>
        </div>

        <?php if (!empty($_GET['sucesso']) && $_GET['sucesso'] === 'resposta_adicionada'): ?>
        <div class="nn-alert nn-alert-success nn-animate-fade" style="margin-bottom: 16px;">
            <i class="fas fa-check-circle"></i>
            <strong>Resposta enviada com sucesso!</strong>
        </div>
        <?php endif; ?>

        <?php if (!empty($_GET['erro'])): ?>
        <div class="nn-alert nn-alert-danger nn-animate-fade" style="margin-bottom: 16px;">
            <i class="fas fa-exclamation-circle"></i>
            <?php
            $erros_msg = [
                'resposta_vazia'  => 'A resposta não pode estar vazia.',
                'resposta_curta'  => 'A resposta precisa ter pelo menos 10 caracteres.',
                'resposta_longa'  => 'A resposta não pode ter mais de 5000 caracteres.',
                'chamado_fechado' => 'Este chamado está fechado e não aceita mais respostas.',
                'erro_servidor'   => 'Ocorreu um erro ao enviar a resposta. Tente novamente.',
            ];
            echo htmlspecialchars($erros_msg[$_GET['erro']] ?? 'Ocorreu um erro.');
            ?>
        </div>
        <?php endif; ?>

        <div class="row g-3">
            <!-- Coluna Esquerda: Informações do Chamado -->
            <div class="col-lg-8">
                <!-- Informações Principais -->
                <div class="nn-card nn-animate-slide">
                    <div class="nn-card-header">
                        <h2 class="nn-card-title">
                            <i class="fas fa-info-circle"></i>
                            Informações do Chamado
                        </h2>
                    </div>
                    <div class="nn-card-body">
                        <h4 class="mb-3"><?php echo htmlspecialchars($chamado['titulo']); ?></h4>

                        <div class="mb-3">
                            <?php
                            $prioridadeClass = match(strtolower($chamado['prioridade'])) {
                                'critica' => 'nn-badge-critical',
                                'alta' => 'nn-badge-high',
                                'media' => 'nn-badge-medium',
                                'baixa' => 'nn-badge-low',
                                default => 'nn-badge-secondary'
                            };

                            $statusClass = match(strtolower($chamado['status'])) {
                                'aberto' => 'nn-badge-primary',
                                'em andamento' => 'nn-badge-info',
                                'pendente' => 'nn-badge-warning',
                                'resolvido' => 'nn-badge-success',
                                'cancelado' => 'nn-badge-danger',
                                default => 'nn-badge-secondary'
                            };
                            ?>
                            <span class="nn-badge <?php echo $prioridadeClass; ?>">
                                <?php echo ucfirst($chamado['prioridade']); ?>
                            </span>
                            <span class="nn-badge <?php echo $statusClass; ?>">
                                <?php echo ucfirst($chamado['status']); ?>
                            </span>
                            <?php if ($chamado['categoria_nome']): ?>
                                <span class="nn-badge" style="background-color: <?php echo htmlspecialchars($chamado['categoria_cor']); ?>">
                                    <i class="fas <?php echo htmlspecialchars($chamado['categoria_icone']); ?>"></i>
                                    <?php echo htmlspecialchars($chamado['categoria_nome']); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="mb-3">
                            <strong>Descrição:</strong>
                            <p class="mt-2"><?php echo nl2br(htmlspecialchars($chamado['descricao'])); ?></p>
                        </div>

                        <?php if ($chamado['historico_atendimento'] && $chamado['status'] === 'resolvido'): ?>
                            <hr>
                            <div class="mb-3">
                                <strong><i class="fas fa-file-alt text-success"></i> Relatório do Atendimento:</strong>
                                <div class="nn-alert nn-alert-success mt-2">
                                    <?php echo nl2br(htmlspecialchars($chamado['historico_atendimento'])); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($chamado['stylemanager_software']): ?>
                            <div class="nn-alert nn-alert-info">
                                <i class="fas fa-code"></i>
                                <strong>Serviço StyleManager (Software)</strong> - Sem cobrança
                            </div>
                        <?php elseif ($chamado['pagamento_forma']): ?>
                            <div class="mb-3">
                                <strong><i class="fas fa-credit-card text-primary"></i> Forma de Pagamento:</strong>
                                <span class="nn-badge nn-badge-success"><?php echo htmlspecialchars($chamado['pagamento_forma']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Atualizações/Histórico -->
                <?php if ($atualizacoes->num_rows > 0): ?>
                    <div class="nn-card nn-animate-fade">
                        <div class="nn-card-header">
                            <h2 class="nn-card-title">
                                <i class="fas fa-history"></i>
                                Histórico de Atualizações (<?php echo $atualizacoes->num_rows; ?>)
                            </h2>
                        </div>
                        <div class="nn-card-body">
                            <?php while ($atu = $atualizacoes->fetch_assoc()): ?>
                                <div class="nn-alert nn-alert-secondary mb-2">
                                    <div class="d-flex justify-content-between">
                                        <strong><?php echo ucfirst(str_replace('_', ' ', $atu['tipo_atualizacao'])); ?></strong>
                                        <small class="text-muted">
                                            <?php echo date('d/m/Y H:i', strtotime($atu['data_atualizacao'])); ?>
                                        </small>
                                    </div>
                                    <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($atu['descricao'])); ?></p>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Fotos -->
                <?php if ($fotos->num_rows > 0): ?>
                    <div class="nn-card nn-animate-fade">
                        <div class="nn-card-header">
                            <h2 class="nn-card-title">
                                <i class="fas fa-images"></i>
                                Fotos do Serviço (<?php echo $fotos->num_rows; ?>)
                            </h2>
                        </div>
                        <div class="nn-card-body">
                            <div class="row g-3">
                                <?php while ($foto = $fotos->fetch_assoc()): ?>
                                    <div class="col-md-4">
                                        <a href="<?php echo htmlspecialchars($foto['caminho_arquivo']); ?>" target="_blank">
                                            <img src="<?php echo htmlspecialchars($foto['caminho_arquivo']); ?>"
                                                 class="img-fluid rounded"
                                                 alt="Foto do serviço"
                                                 style="cursor: pointer; max-height: 200px; object-fit: cover; width: 100%;">
                                        </a>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Coluna Direita: Técnico e Timeline -->
            <div class="col-lg-4">
                <!-- Informações do Técnico -->
                <?php if ($chamado['tecnico_nome']): ?>
                    <div class="nn-card nn-animate-slide">
                        <div class="nn-card-header">
                            <h2 class="nn-card-title">
                                <i class="fas fa-user-cog"></i>
                                Técnico Responsável
                            </h2>
                        </div>
                        <div class="nn-card-body">
                            <div class="mb-3">
                                <strong>Nome:</strong><br>
                                <?php echo htmlspecialchars($chamado['tecnico_nome']); ?>
                            </div>
                            <div class="mb-3">
                                <strong>Matrícula:</strong><br>
                                <span class="nn-badge nn-badge-info"><?php echo htmlspecialchars($chamado['tecnico_matricula']); ?></span>
                            </div>
                            <?php if ($chamado['tecnico_telefone']): ?>
                                <div>
                                    <strong>Telefone:</strong><br>
                                    <a href="tel:<?php echo htmlspecialchars($chamado['tecnico_telefone']); ?>">
                                        <?php echo htmlspecialchars($chamado['tecnico_telefone']); ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="nn-card nn-animate-slide">
                        <div class="nn-card-body">
                            <div class="nn-alert nn-alert-warning">
                                <i class="fas fa-clock"></i>
                                Aguardando atribuição de técnico
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Timeline -->
                <div class="nn-card nn-animate-fade">
                    <div class="nn-card-header">
                        <h2 class="nn-card-title">
                            <i class="fas fa-clock"></i>
                            Timeline
                        </h2>
                    </div>
                    <div class="nn-card-body">
                        <div class="mb-3">
                            <strong>Abertura:</strong><br>
                            <small><?php echo date('d/m/Y H:i', strtotime($chamado['data_abertura'])); ?></small>
                        </div>
                        <?php if ($chamado['data_inicio_atendimento']): ?>
                            <div class="mb-3">
                                <strong>Início Atendimento:</strong><br>
                                <small><?php echo date('d/m/Y H:i', strtotime($chamado['data_inicio_atendimento'])); ?></small>
                            </div>
                        <?php endif; ?>
                        <?php if ($chamado['data_resolucao']): ?>
                            <div class="mb-3">
                                <strong>Resolução:</strong><br>
                                <small><?php echo date('d/m/Y H:i', strtotime($chamado['data_resolucao'])); ?></small>
                            </div>
                            <?php if ($chamado['tempo_atendimento_minutos']): ?>
                                <div>
                                    <strong>Tempo Total:</strong><br>
                                    <?php
                                    $horas = floor($chamado['tempo_atendimento_minutos'] / 60);
                                    $mins = $chamado['tempo_atendimento_minutos'] % 60;
                                    ?>
                                    <span class="nn-badge nn-badge-info">
                                        <?php echo $horas; ?>h <?php echo $mins; ?>min
                                    </span>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Status do Chamado -->
                <div class="nn-card nn-animate-fade">
                    <div class="nn-card-header">
                        <h2 class="nn-card-title">
                            <i class="fas fa-info-circle"></i>
                            Status
                        </h2>
                    </div>
                    <div class="nn-card-body">
                        <?php if ($chamado['status'] === 'aberto'): ?>
                            <div class="nn-alert nn-alert-primary">
                                <i class="fas fa-folder-open"></i>
                                <strong>Chamado Aberto</strong><br>
                                <small>Aguardando atribuição de técnico</small>
                            </div>
                        <?php elseif ($chamado['status'] === 'em andamento'): ?>
                            <div class="nn-alert nn-alert-info">
                                <i class="fas fa-spinner fa-spin"></i>
                                <strong>Em Atendimento</strong><br>
                                <small>O técnico está trabalhando no seu chamado</small>
                            </div>
                        <?php elseif ($chamado['status'] === 'pendente'): ?>
                            <div class="nn-alert nn-alert-warning">
                                <i class="fas fa-pause-circle"></i>
                                <strong>Pendente</strong><br>
                                <small>Atendimento temporariamente pausado</small>
                            </div>
                        <?php elseif ($chamado['status'] === 'resolvido'): ?>
                            <div class="nn-alert nn-alert-success">
                                <i class="fas fa-check-circle"></i>
                                <strong>Chamado Resolvido</strong><br>
                                <small>Atendimento finalizado com sucesso</small>
                            </div>
                        <?php elseif ($chamado['status'] === 'cancelado'): ?>
                            <div class="nn-alert nn-alert-danger">
                                <i class="fas fa-times-circle"></i>
                                <strong>Chamado Cancelado</strong>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<?php
$conn->close();
require_once '../includes/footer.php';
?>
