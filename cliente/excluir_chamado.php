<?php
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireCliente();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método inválido.");
}

requireCsrfToken();

$conn = getConnection();

// Verifica se o ID do chamado foi enviado via POST
if (!isset($_POST['id']) || empty($_POST['id'])) {
    die("Chamado inválido.");
}

$chamado_id = $_POST['id'];
$usuario_id = $_SESSION['id'];

// Verifica se o chamado pertence ao usuário logado antes de excluir
$stmt = $conn->prepare("DELETE FROM chamados WHERE id = ? AND cliente_id = ?");
$stmt->bind_param("ii", $chamado_id, $usuario_id);

if ($stmt->execute()) {
    echo "<script>alert('Chamado excluído com sucesso!'); window.location.href='home.php';</script>";
} else {
    echo "<script>alert('Erro ao excluir chamado.'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
?>
