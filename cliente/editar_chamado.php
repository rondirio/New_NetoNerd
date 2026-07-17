<?php
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireCliente();

$conn = getConnection();

// Verifica se o ID do chamado foi enviado via GET
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Chamado inválido.");
}

$chamado_id = $_GET['id'];
$usuario_id = $_SESSION['id'];

// Busca os dados do chamado para preencher o formulário
$stmt = $conn->prepare("SELECT titulo, descricao, prioridade, status FROM chamados WHERE id = ? AND cliente_id = ?");
$stmt->bind_param("ii", $chamado_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$chamado = $result->fetch_assoc();

if (!$chamado) {
    die("Chamado não encontrado.");
}

$stmt->close();
$conn->close();

$page_title = "Editar Chamado - NetoNerd ITSM";
require_once '../includes/header.php';
?>

<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h1 class="nn-card-title">
                    <i class="fas fa-edit"></i>
                    Editar Chamado
                </h1>
            </div>

            <div class="nn-card-body">
                <form action="salvar_edicao.php" method="POST">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="id" value="<?= htmlspecialchars($chamado_id) ?>">

                    <div class="nn-form-group">
                        <label class="nn-form-label" for="titulo">Título</label>
                        <input type="text" id="titulo" name="titulo" class="nn-form-control" value="<?= htmlspecialchars($chamado['titulo']) ?>" required>
                    </div>

                    <div class="nn-form-group">
                        <label class="nn-form-label" for="descricao">Descrição</label>
                        <textarea id="descricao" name="descricao" class="nn-form-control" rows="5" required><?= htmlspecialchars($chamado['descricao']) ?></textarea>
                    </div>

                    <div class="nn-form-group">
                        <label class="nn-form-label" for="prioridade">Prioridade</label>
                        <select id="prioridade" name="prioridade" class="nn-form-control">
                            <option value="baixa" <?= $chamado['prioridade'] == 'baixa' ? 'selected' : '' ?>>Baixa</option>
                            <option value="media" <?= $chamado['prioridade'] == 'media' ? 'selected' : '' ?>>Média</option>
                            <option value="alta" <?= $chamado['prioridade'] == 'alta' ? 'selected' : '' ?>>Alta</option>
                            <option value="critica" <?= $chamado['prioridade'] == 'critica' ? 'selected' : '' ?>>Crítica</option>
                        </select>
                    </div>

                    <div class="nn-form-group">
                        <label class="nn-form-label" for="status">Status</label>
                        <select id="status" name="status" class="nn-form-control">
                            <option value="aberto" <?= $chamado['status'] == 'aberto' ? 'selected' : '' ?>>Aberto</option>
                            <option value="em andamento" <?= $chamado['status'] == 'em andamento' ? 'selected' : '' ?>>Em Andamento</option>
                            <option value="pendente" <?= $chamado['status'] == 'pendente' ? 'selected' : '' ?>>Pendente</option>
                            <option value="resolvido" <?= $chamado['status'] == 'resolvido' ? 'selected' : '' ?>>Resolvido</option>
                            <option value="cancelado" <?= $chamado['status'] == 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                        </select>
                    </div>

                    <button type="submit" class="nn-btn nn-btn-primary nn-btn-lg">
                        <i class="fas fa-save"></i>
                        Salvar Alterações
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
