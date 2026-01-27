<?php
/**
 * Editar Técnico - NetoNerd ITSM v2.0
 * Formulário de edição de dados do técnico
 */
 include('../controller/configurar_log.php');

session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireAdmin();

$conn = getConnection();

// Verificar se um ID foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: apresenta_tecnicos.php?erro=id_invalido');
    exit();
}

$id = intval($_GET['id']);

// Buscar dados do técnico
$stmt = $conn->prepare("
    SELECT id, nome, carro_do_dia, email, telefone, created_at, matricula, status_tecnico
    FROM tecnicos
    WHERE id = ?
");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header('Location: apresenta_tecnicos.php?erro=tecnico_nao_encontrado');
    exit();
}

$tecnico = $resultado->fetch_assoc();
$stmt->close();

// Processar atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $carro_do_dia = trim($_POST['carro_do_dia'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');
    $matricula = trim($_POST['matricula'] ?? '');
    $status_tecnico = $_POST['status_tecnico'] ?? '';

    // Validações
    if (empty($nome) || empty($email) || empty($matricula) || empty($status_tecnico)) {
        $erro = "Todos os campos obrigatórios devem ser preenchidos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = "Email inválido.";
    } else {
        $stmt = $conn->prepare("
            UPDATE tecnicos
            SET nome = ?, carro_do_dia = ?, email = ?, telefone = ?, matricula = ?, status_tecnico = ?
            WHERE id = ?
        ");
        $stmt->bind_param("ssssssi", $nome, $carro_do_dia, $email, $telefone, $matricula, $status_tecnico, $id);

        if ($stmt->execute()) {
            $stmt->close();
            $conn->close();
            header('Location: apresenta_tecnicos.php?sucesso=tecnico_atualizado');
            exit();
        } else {
            $erro = "Erro ao atualizar técnico: " . $conn->error;
        }
        $stmt->close();
    }
}

$page_title = "Editar Técnico - NetoNerd ITSM";
require_once '../includes/header.php';
?>

<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h1 class="nn-card-title">
                    <i class="fas fa-user-edit"></i>
                    Editar Técnico
                </h1>
                <div>
                    <a href="apresenta_tecnicos.php" class="nn-btn nn-btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Voltar
                    </a>
                </div>
            </div>
        </div>

        <?php if (isset($erro)): ?>
            <div class="nn-alert nn-alert-danger nn-animate-slide">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($erro); ?>
            </div>
        <?php endif; ?>

        <div class="nn-card nn-animate-slide">
            <div class="nn-card-body">
                <form method="POST" action="">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="nn-form-group">
                                <label class="nn-form-label">Nome Completo *</label>
                                <input type="text" name="nome" class="nn-form-control"
                                       value="<?php echo htmlspecialchars($tecnico['nome']); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="nn-form-group">
                                <label class="nn-form-label">Email *</label>
                                <input type="email" name="email" class="nn-form-control"
                                       value="<?php echo htmlspecialchars($tecnico['email']); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="nn-form-group">
                                <label class="nn-form-label">Matrícula *</label>
                                <input type="text" name="matricula" class="nn-form-control"
                                       value="<?php echo htmlspecialchars($tecnico['matricula']); ?>" required>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="nn-form-group">
                                <label class="nn-form-label">Telefone</label>
                                <input type="tel" name="telefone" class="nn-form-control"
                                       value="<?php echo htmlspecialchars($tecnico['telefone'] ?? ''); ?>"
                                       placeholder="(00) 00000-0000">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="nn-form-group">
                                <label class="nn-form-label">Status *</label>
                                <select name="status_tecnico" class="nn-form-control" required>
                                    <option value="Active" <?php echo $tecnico['status_tecnico'] === 'Active' ? 'selected' : ''; ?>>Ativo</option>
                                    <option value="Inactive" <?php echo $tecnico['status_tecnico'] === 'Inactive' ? 'selected' : ''; ?>>Inativo</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="nn-form-group">
                                <label class="nn-form-label">Carro do Dia</label>
                                <input type="text" name="carro_do_dia" class="nn-form-control"
                                       value="<?php echo htmlspecialchars($tecnico['carro_do_dia'] ?? ''); ?>"
                                       placeholder="Ex: Fiat Uno Branco - ABC-1234">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted">
                            <small>
                                <i class="fas fa-clock"></i>
                                Cadastrado em: <?php echo date('d/m/Y H:i', strtotime($tecnico['created_at'])); ?>
                            </small>
                        </div>
                        <div>
                            <a href="apresenta_tecnicos.php" class="nn-btn nn-btn-secondary">
                                <i class="fas fa-times"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="nn-btn nn-btn-primary">
                                <i class="fas fa-save"></i>
                                Salvar Alterações
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<?php
$conn->close();
require_once '../includes/footer.php';
?>
