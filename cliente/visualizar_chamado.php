<?php
require_once "../controller/validador_acesso.php";
require_once "../controller/auth_middleware.php";
include '../config/bandoDeDados/conexao.php';

requireCliente();

$conn = getConnection();
$usuario_id = $_SESSION['id'];

// Verificar se ID foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: home.php');
    exit();
}

$chamado_id = intval($_GET['id']);

// Buscar dados do chamado
$stmt = $conn->prepare("
    SELECT c.*,
           cl.nome as nome_cliente, cl.email as email_cliente, cl.telefone as telefone_cliente,
           COALESCE(t.nome, ta.nome) as nome_tecnico, COALESCE(t.email, ta.email) as email_tecnico,
           t.telefone as telefone_tecnico,
           DATE_FORMAT(c.data_abertura, '%d/%m/%Y às %H:%i') as data_abertura_formatada,
           DATE_FORMAT(c.data_fechamento, '%d/%m/%Y às %H:%i') as data_fechamento_formatada,
           TIMESTAMPDIFF(HOUR, c.data_abertura, COALESCE(c.data_fechamento, NOW())) as horas_decorridas
    FROM chamados c
    INNER JOIN clientes cl ON c.cliente_id = cl.id
    LEFT JOIN tecnicos t ON c.tecnico_id = t.id
    LEFT JOIN admins ta ON c.tecnico_id = ta.id
    WHERE c.id = ? AND c.cliente_id = ?
");

$stmt->bind_param("ii", $chamado_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: home.php?erro=chamado_nao_encontrado');
    exit();
}

$chamado = $result->fetch_assoc();
$stmt->close();

// Buscar respostas/comentários
$stmt = $conn->prepare("
    SELECT r.*,
           COALESCE(cl.nome, t.nome, ta.nome) as autor_nome,
           DATE_FORMAT(r.data_resposta, '%d/%m/%Y às %H:%i') as data_formatada,
           CASE
               WHEN r.usuario_id = c.cliente_id THEN 'cliente'
               ELSE 'tecnico'
           END as tipo_autor
    FROM respostas_chamado r
    LEFT JOIN clientes cl ON r.usuario_id = cl.id
    LEFT JOIN tecnicos t ON r.usuario_id = t.id
    LEFT JOIN admins ta ON r.usuario_id = ta.id
    INNER JOIN chamados c ON r.chamado_id = c.id
    WHERE r.chamado_id = ? AND r.tipo_resposta = 'publica'
    ORDER BY r.data_resposta ASC
");

$stmt->bind_param("i", $chamado_id);
$stmt->execute();
$respostas = $stmt->get_result();
$stmt->close();

// Buscar anexos (enviados na abertura do chamado)
$stmt = $conn->prepare("
    SELECT * FROM anexos_chamado
    WHERE chamado_id = ?
    ORDER BY data_upload DESC
");

$stmt->bind_param("i", $chamado_id);
$stmt->execute();
$anexos = $stmt->get_result();
$stmt->close();

// Buscar fotos do serviço (enviadas pelo técnico durante o atendimento)
$stmt = $conn->prepare("
    SELECT * FROM chamado_fotos
    WHERE chamado_id = ?
    ORDER BY data_upload DESC
");
$stmt->bind_param("i", $chamado_id);
$stmt->execute();
$fotos = $stmt->get_result();
$stmt->close();

$conn->close();

// Função helper para status
function getStatusBadgeClass($status) {
    $classes = [
        'aberto' => 'nn-badge-primary',
        'em andamento' => 'nn-badge-warning',
        'aguardando_cliente' => 'nn-badge-info',
        'resolvido' => 'nn-badge-success',
        'fechado' => 'nn-badge-secondary',
        'cancelado' => 'nn-badge-danger'
    ];
    return $classes[$status] ?? 'nn-badge-secondary';
}

function getPrioridadeBadgeClass($prioridade) {
    $classes = [
        'baixa' => 'nn-badge-low',
        'media' => 'nn-badge-medium',
        'alta' => 'nn-badge-high',
        'urgente' => 'nn-badge-critical'
    ];
    return $classes[$prioridade] ?? 'nn-badge-secondary';
}

$page_title = "Chamado #" . $chamado['protocolo'] . " - NetoNerd ITSM";
require_once '../includes/header.php';
?>

<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <?php if (!empty($_GET['sucesso'])): ?>
        <div class="nn-alert nn-alert-success nn-animate-fade">
            <i class="fas fa-check-circle"></i>
            <?php
            $sucessos_msg = [
                'resposta_adicionada' => 'Resposta enviada com sucesso!',
                'chamado_fechado'     => 'Chamado confirmado como fechado. Obrigado!',
            ];
            echo htmlspecialchars($sucessos_msg[$_GET['sucesso']] ?? 'Operação realizada com sucesso!');
            ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($_GET['erro'])): ?>
        <div class="nn-alert nn-alert-danger nn-animate-fade">
            <i class="fas fa-exclamation-circle"></i>
            <?php
            $erros_msg = [
                'resposta_vazia'   => 'A resposta não pode estar vazia.',
                'resposta_curta'   => 'A resposta precisa ter pelo menos 10 caracteres.',
                'resposta_longa'   => 'A resposta não pode ter mais de 5000 caracteres.',
                'chamado_fechado'  => 'Este chamado está fechado e não aceita mais respostas.',
                'status_invalido'  => 'Este chamado não pode ser fechado no status atual.',
                'erro_servidor'    => 'Ocorreu um erro ao processar sua solicitação. Tente novamente.',
                'erro_interno'     => 'Ocorreu um erro interno. Tente novamente.',
            ];
            echo htmlspecialchars($erros_msg[$_GET['erro']] ?? 'Ocorreu um erro.');
            ?>
        </div>
        <?php endif; ?>

        <!-- Actions Bar -->
        <div class="nn-d-flex nn-justify-between nn-align-center nn-mb-2" style="flex-wrap: wrap; gap: 15px;">
            <a href="home.php" class="nn-btn nn-btn-secondary">
                <i class="fas fa-arrow-left"></i>
                Voltar para Meus Chamados
            </a>

            <?php if (in_array($chamado['status'], ['aberto', 'em andamento'])): ?>
                <a href="editar_chamado.php?id=<?= $chamado['id'] ?>" class="nn-btn nn-btn-warning">
                    <i class="fas fa-pen"></i>
                    Editar Chamado
                </a>
            <?php endif; ?>
        </div>

        <!-- Cabeçalho do Chamado -->
        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <div>
                    <div class="nn-text-light" style="font-size: 0.85rem;">Protocolo: #<?= htmlspecialchars($chamado['protocolo']) ?></div>
                    <h1 class="nn-card-title" style="margin-top: 5px;">
                        <?= htmlspecialchars($chamado['titulo']) ?>
                    </h1>
                </div>
            </div>
            <div class="nn-card-body">
                <div class="nn-d-flex nn-gap-2 nn-align-center" style="flex-wrap: wrap;">
                    <span class="nn-badge <?= getStatusBadgeClass($chamado['status']) ?>">
                        <?= ucfirst(str_replace('_', ' ', $chamado['status'])) ?>
                    </span>
                    <span class="nn-badge <?= getPrioridadeBadgeClass($chamado['prioridade']) ?>">
                        Prioridade: <?= ucfirst($chamado['prioridade']) ?>
                    </span>
                    <span class="nn-text-medium">
                        <i class="fas fa-calendar"></i>
                        Aberto em: <?= $chamado['data_abertura_formatada'] ?>
                    </span>
                    <span class="nn-text-medium">
                        <i class="fas fa-clock"></i>
                        <?= $chamado['horas_decorridas'] ?> horas decorridas
                    </span>
                </div>
            </div>
        </div>

        <!-- Informações Gerais -->
        <div class="nn-card">
            <div class="nn-card-header">
                <h2 class="nn-card-title">
                    <i class="fas fa-clipboard-list"></i>
                    Informações do Chamado
                </h2>
            </div>
            <div class="nn-card-body">
                <div class="nn-stats-grid">
                    <div class="nn-stat-card">
                        <div class="nn-stat-label">Categoria</div>
                        <div class="nn-stat-value" style="font-size: 1.2rem;"><?= htmlspecialchars($chamado['categoria']) ?></div>
                    </div>
                    <div class="nn-stat-card">
                        <div class="nn-stat-label">Técnico Responsável</div>
                        <div class="nn-stat-value" style="font-size: 1.2rem;">
                            <?= $chamado['nome_tecnico'] ? htmlspecialchars($chamado['nome_tecnico']) : 'Aguardando atribuição' ?>
                        </div>
                        <?php if ($chamado['telefone_tecnico']): ?>
                            <div class="nn-text-light" style="font-size: 0.85rem;">
                                <a href="tel:<?= htmlspecialchars($chamado['telefone_tecnico']) ?>"><?= htmlspecialchars($chamado['telefone_tecnico']) ?></a>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="nn-stat-card">
                        <div class="nn-stat-label">Data de Abertura</div>
                        <div class="nn-stat-value" style="font-size: 1.2rem;"><?= $chamado['data_abertura_formatada'] ?></div>
                    </div>
                    <?php if ($chamado['data_fechamento']): ?>
                    <div class="nn-stat-card">
                        <div class="nn-stat-label">Data de Fechamento</div>
                        <div class="nn-stat-value" style="font-size: 1.2rem;"><?= $chamado['data_fechamento_formatada'] ?></div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Descrição -->
        <div class="nn-card">
            <div class="nn-card-header">
                <h2 class="nn-card-title">
                    <i class="fas fa-align-left"></i>
                    Descrição do Problema
                </h2>
            </div>
            <div class="nn-card-body">
                <div style="background: var(--bg-light); padding: 20px; border-radius: var(--radius-md); border-left: 4px solid var(--primary-blue); line-height: 1.8; white-space: pre-wrap; word-wrap: break-word;">
<?= htmlspecialchars($chamado['descricao']) ?>
                </div>
            </div>
        </div>

        <!-- Anexos -->
        <?php if ($anexos->num_rows > 0): ?>
        <div class="nn-card">
            <div class="nn-card-header">
                <h2 class="nn-card-title">
                    <i class="fas fa-paperclip"></i>
                    Anexos (<?= $anexos->num_rows ?>)
                </h2>
            </div>
            <div class="nn-card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                    <?php while ($anexo = $anexos->fetch_assoc()): ?>
                        <a href="<?= htmlspecialchars($anexo['caminho_arquivo']) ?>"
                           target="_blank" class="nn-card" style="text-align: center; margin-bottom: 0; text-decoration: none;">
                            <div style="font-size: 2.5rem; margin-bottom: 10px;">
                                <?php
                                $ext = pathinfo($anexo['nome_arquivo'], PATHINFO_EXTENSION);
                                echo in_array($ext, ['jpg', 'jpeg', 'png', 'gif']) ? '<i class="fas fa-image"></i>' : '<i class="fas fa-file"></i>';
                                ?>
                            </div>
                            <div style="font-weight: 600; word-break: break-all; margin-bottom: 5px;"><?= htmlspecialchars($anexo['nome_arquivo']) ?></div>
                            <div class="nn-text-light" style="font-size: 0.8rem;">
                                <?= date('d/m/Y', strtotime($anexo['data_upload'])) ?>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Fotos do Serviço -->
        <?php if ($fotos->num_rows > 0): ?>
        <div class="nn-card">
            <div class="nn-card-header">
                <h2 class="nn-card-title">
                    <i class="fas fa-images"></i>
                    Fotos do Serviço (<?= $fotos->num_rows ?>)
                </h2>
            </div>
            <div class="nn-card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">
                    <?php while ($foto = $fotos->fetch_assoc()): ?>
                        <a href="<?= htmlspecialchars($foto['caminho_arquivo']) ?>" target="_blank">
                            <img src="<?= htmlspecialchars($foto['caminho_arquivo']) ?>"
                                 alt="Foto do serviço"
                                 style="width: 100%; max-height: 200px; object-fit: cover; border-radius: var(--radius-md); cursor: pointer;">
                        </a>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Timeline de Interações -->
        <div class="nn-card">
            <div class="nn-card-header">
                <h2 class="nn-card-title">
                    <i class="fas fa-comments"></i>
                    Histórico de Interações
                </h2>
            </div>
            <div class="nn-card-body">
                <?php if ($respostas->num_rows > 0): ?>
                    <?php while ($resposta = $respostas->fetch_assoc()): ?>
                        <div class="nn-d-flex nn-gap-2 nn-mb-2" style="align-items: flex-start;">
                            <div style="width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; flex-shrink: 0; background: <?= $resposta['tipo_autor'] === 'cliente' ? 'var(--success)' : 'var(--primary-blue)' ?>;">
                                <i class="fas <?= $resposta['tipo_autor'] === 'cliente' ? 'fa-user' : 'fa-user-cog' ?>"></i>
                            </div>
                            <div class="nn-card" style="flex: 1; margin-bottom: 0;">
                                <div class="nn-d-flex nn-justify-between" style="flex-wrap: wrap; gap: 10px; margin-bottom: 10px;">
                                    <strong>
                                        <?= htmlspecialchars($resposta['autor_nome']) ?>
                                        <span class="nn-text-light" style="font-weight: normal; font-size: 0.9rem;">
                                            (<?= $resposta['tipo_autor'] === 'cliente' ? 'Você' : 'Técnico' ?>)
                                        </span>
                                    </strong>
                                    <span class="nn-text-light" style="font-size: 0.85rem;"><?= $resposta['data_formatada'] ?></span>
                                </div>
                                <div style="white-space: pre-wrap; word-wrap: break-word;"><?= htmlspecialchars($resposta['resposta']) ?></div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="nn-text-center" style="padding: 40px; color: var(--text-light);">
                        <i class="fas fa-comment-slash" style="font-size: 2.5rem; margin-bottom: 15px; opacity: 0.5;"></i>
                        <p>Ainda não há interações neste chamado.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Formulário de Resposta -->
        <?php if (!in_array($chamado['status'], ['fechado', 'cancelado'])): ?>
        <div class="nn-card">
            <div class="nn-card-header">
                <h2 class="nn-card-title">
                    <i class="fas fa-pen-nib"></i>
                    Adicionar Comentário
                </h2>
            </div>
            <div class="nn-card-body">
                <form method="POST" action="adicionar_resposta.php" id="formResposta">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="chamado_id" value="<?= $chamado['id'] ?>">

                    <div class="nn-form-group">
                        <textarea name="resposta" class="nn-form-control" rows="5"
                                  placeholder="Digite sua mensagem aqui..."
                                  required maxlength="5000"></textarea>
                        <small class="nn-text-light">
                            Use este espaço para adicionar informações, fazer perguntas ou enviar atualizações.
                        </small>
                    </div>

                    <div class="nn-d-flex nn-justify-between nn-align-center" style="flex-wrap: wrap; gap: 15px;">
                        <div>
                            <?php if ($chamado['status'] === 'resolvido'): ?>
                                <form method="POST" action="fechar_chamado.php" id="formFecharChamado" style="display:inline;">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="id" value="<?= $chamado['id'] ?>">
                                    <button type="button" class="nn-btn nn-btn-success" onclick="confirmarResolucao()">
                                        <i class="fas fa-check-circle"></i>
                                        Confirmar Resolução
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <button type="submit" class="nn-btn nn-btn-primary">
                            <i class="fas fa-paper-plane"></i>
                            Enviar Comentário
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php else: ?>
            <div class="nn-alert nn-alert-info">
                <i class="fas fa-info-circle"></i>
                Este chamado está <?= $chamado['status'] ?>. Não é possível adicionar novos comentários.
            </div>
        <?php endif; ?>

    </div>
</div>

<?php
$extra_js = '<script>
    function confirmarResolucao() {
        if (confirm("Confirma que o problema foi resolvido? O chamado será marcado como fechado.")) {
            document.getElementById("formFecharChamado").submit();
        }
    }

    document.getElementById("formResposta").addEventListener("submit", function(e) {
        const textarea = this.querySelector("textarea");
        if (textarea.value.trim().length < 10) {
            e.preventDefault();
            alert("Por favor, escreva uma mensagem com pelo menos 10 caracteres.");
        }
    });
</script>';
require_once '../includes/footer.php';
?>
