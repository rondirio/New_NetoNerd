<?php
/**
 * Detalhes do Chamado - NetoNerd ITSM v2.0
 * Visualização completa de chamado para técnicos
 */

session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireTecnico();

$conn = getConnection();
$tecnico_id = $_SESSION['usuario_id'];
$chamado_id = intval($_GET['id'] ?? 0);

if ($chamado_id === 0) {
    header('Location: meus_chamados.php?erro=id_invalido');
    exit();
}

// Buscar dados completos do chamado
$stmt = $conn->prepare("
    SELECT
        c.*,
        cl.nome as cliente_nome,
        cl.email as cliente_email,
        cl.telefone as cliente_telefone,
        cl.endereco as cliente_endereco,
        t.nome as tecnico_nome,
        t.matricula as tecnico_matricula,
        cat.nome as categoria_nome,
        cat.cor as categoria_cor,
        cat.icone as categoria_icone
    FROM chamados c
    INNER JOIN clientes cl ON c.cliente_id = cl.id
    LEFT JOIN tecnicos t ON c.tecnico_id = t.id
    LEFT JOIN categorias_chamado cat ON c.categoria_id = cat.id
    WHERE c.id = ? AND c.tecnico_id = ?
");
$stmt->bind_param("ii", $chamado_id, $tecnico_id);
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

                        <?php if ($chamado['historico_atendimento']): ?>
                            <hr>
                            <div class="mb-3">
                                <strong><i class="fas fa-file-alt text-success"></i> Histórico do Atendimento:</strong>
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
                                                 style="cursor: pointer; max-height: 200px; object-fit: cover;">
                                        </a>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Coluna Direita: Cliente e Ações -->
            <div class="col-lg-4">
                <!-- Informações do Cliente -->
                <div class="nn-card nn-animate-slide">
                    <div class="nn-card-header">
                        <h2 class="nn-card-title">
                            <i class="fas fa-user"></i>
                            Cliente
                        </h2>
                    </div>
                    <div class="nn-card-body">
                        <div class="mb-3">
                            <strong>Nome:</strong><br>
                            <?php echo htmlspecialchars($chamado['cliente_nome']); ?>
                        </div>
                        <div class="mb-3">
                            <strong>Email:</strong><br>
                            <a href="mailto:<?php echo htmlspecialchars($chamado['cliente_email']); ?>">
                                <?php echo htmlspecialchars($chamado['cliente_email']); ?>
                            </a>
                        </div>
                        <div class="mb-3">
                            <strong>Telefone:</strong><br>
                            <a href="tel:<?php echo htmlspecialchars($chamado['cliente_telefone']); ?>">
                                <?php echo htmlspecialchars($chamado['cliente_telefone']); ?>
                            </a>
                        </div>
                        <?php if ($chamado['cliente_endereco']): ?>
                            <div>
                                <strong>Endereço:</strong><br>
                                <?php echo htmlspecialchars($chamado['cliente_endereco']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

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

                <!-- Ações -->
                <div class="nn-card nn-animate-fade">
                    <div class="nn-card-header">
                        <h2 class="nn-card-title">
                            <i class="fas fa-tools"></i>
                            Ações
                        </h2>
                    </div>
                    <div class="nn-card-body">
                        <?php if ($chamado['status'] === 'aberto'): ?>
                            <form action="processar_chamado.php" method="POST" class="mb-2">
                                <input type="hidden" name="chamado_id" value="<?php echo $chamado['id']; ?>">
                                <input type="hidden" name="acao" value="iniciar">
                                <button type="submit" class="nn-btn nn-btn-primary w-100">
                                    <i class="fas fa-play"></i>
                                    Iniciar Atendimento
                                </button>
                            </form>
                        <?php elseif ($chamado['status'] === 'em andamento'): ?>
                            <a href="resolver_chamado.php?id=<?php echo $chamado['id']; ?>" class="nn-btn nn-btn-success w-100 mb-2">
                                <i class="fas fa-check"></i>
                                Resolver Chamado
                            </a>
                            <button type="button" class="nn-btn nn-btn-warning w-100 mb-2" onclick="abrirModalAtualizar()">
                                <i class="fas fa-edit"></i>
                                Adicionar Atualização
                            </button>
                        <?php elseif ($chamado['status'] === 'resolvido'): ?>
                            <div class="nn-alert nn-alert-success">
                                <i class="fas fa-check-circle"></i>
                                Chamado Resolvido
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- Modal Atualizar -->
<div class="modal fade" id="modalAtualizar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--gradient-primary); color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-edit"></i>
                    Adicionar Atualização
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="processar_chamado.php" method="POST">
                <input type="hidden" name="chamado_id" value="<?php echo $chamado['id']; ?>">
                <input type="hidden" name="acao" value="atualizar">
                <div class="modal-body">
                    <div class="nn-form-group">
                        <label class="nn-form-label">Tipo de Atualização</label>
                        <select name="tipo_atualizacao" class="nn-form-control" required>
                            <option value="comentario">Comentário</option>
                            <option value="necessita_peca">Necessita Peça</option>
                            <option value="aguardando_cliente">Aguardando Cliente</option>
                        </select>
                    </div>
                    <div class="nn-form-group">
                        <label class="nn-form-label">Descrição</label>
                        <textarea name="descricao" class="nn-form-control" rows="4" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="nn-btn nn-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="nn-btn nn-btn-primary">
                        <i class="fas fa-save"></i>
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function abrirModalAtualizar() {
    new bootstrap.Modal(document.getElementById('modalAtualizar')).show();
}
</script>

<?php
$conn->close();
require_once '../includes/footer.php';
?>
