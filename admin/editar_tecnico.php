<?php
include '../config/bandoDeDados/conexao.php';

// Verificar se um ID foi passado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: listar_tecnicos.php');
    exit;
}

$id = intval($_GET['id']);

// Buscar dados do técnico
$sql = "SELECT id, nome, carro_do_dia, email, created_at, matricula, status_tecnico FROM tecnicos WHERE id = ?";
$stmt = $conexao->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header('Location: listar_tecnicos.php');
    exit;
}

$tecnico = $resultado->fetch_assoc();

// Processar atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $carro_do_dia = $_POST['carro_do_dia'] ?? '';
    $email = $_POST['email'] ?? '';
    $matricula = $_POST['matricula'] ?? '';
    $status_tecnico = $_POST['status_tecnico'] ?? '';

    $sql_update = "UPDATE tecnicos SET nome = ?, carro_do_dia = ?, email = ?, matricula = ?, status_tecnico = ? WHERE id = ?";
    $stmt_update = $conexao->prepare($sql_update);
    $stmt_update->bind_param("sssssi", $nome, $carro_do_dia, $email, $matricula, $status_tecnico, $id);

    if ($stmt_update->execute()) {
        header('Location: listar_tecnicos.php?msg=atualizado');
        exit;
    }
}
?>

<h1>Editar Técnico</h1>

<form method="POST">
    <label>Nome:</label>
    <input type="text" name="nome" value="<?php echo htmlspecialchars($tecnico['nome']); ?>" required>

    <label>Email:</label>
    <input type="email" name="email" value="<?php echo htmlspecialchars($tecnico['email']); ?>" required>

    <label>Matrícula:</label>
    <input type="text" name="matricula" value="<?php echo htmlspecialchars($tecnico['matricula']); ?>" required>

    <label>Carro do Dia:</label>
    <input type="text" name="carro_do_dia" value="<?php echo htmlspecialchars($tecnico['carro_do_dia']); ?>">

    <label>Status:</label>
    <select name="status_tecnico" required>
        <option value="ativo" <?php echo $tecnico['status_tecnico'] === 'ativo' ? 'selected' : ''; ?>>Ativo</option>
        <option value="inativo" <?php echo $tecnico['status_tecnico'] === 'inativo' ? 'selected' : ''; ?>>Inativo</option>
    </select>

    <button type="submit">Salvar Alterações</button>
    <a href="listar_tecnicos.php">Cancelar</a>
</form>