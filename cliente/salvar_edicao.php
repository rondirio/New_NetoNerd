<?php
require_once '../controller/auth_middleware.php';
require_once '../controller/historico_chamados.php';
require_once '../config/bandoDeDados/conexao.php';

requireCliente();
requireCsrfToken();

$conn = getConnection();

// Verifica se os campos obrigatórios foram enviados
if (!isset($_POST['id'], $_POST['titulo'], $_POST['descricao'], $_POST['prioridade'], $_POST['status'])) {
    die("Dados incompletos.");
}

$chamado_id = $_POST['id'];
$titulo = $_POST['titulo'];
$descricao = $_POST['descricao'];
$prioridade = $_POST['prioridade'];
$status = $_POST['status'];
$usuario_id = $_SESSION['id'];

$stmt_atual = $conn->prepare("SELECT status FROM chamados WHERE id = ? AND cliente_id = ?");
$stmt_atual->bind_param("ii", $chamado_id, $usuario_id);
$stmt_atual->execute();
$status_anterior = $stmt_atual->get_result()->fetch_assoc()['status'] ?? null;
$stmt_atual->close();

// Atualiza o chamado no banco de dados
$stmt = $conn->prepare("UPDATE chamados SET titulo = ?, descricao = ?, prioridade = ?, status = ? WHERE id = ? AND cliente_id = ?");
$stmt->bind_param("ssssii", $titulo, $descricao, $prioridade, $status, $chamado_id, $usuario_id);

if ($stmt->execute()) {
    if ($status_anterior !== null && $status_anterior !== $status) {
        registrarHistoricoStatus($conn, $chamado_id, $usuario_id, $status_anterior, $status, 'Chamado editado pelo cliente');
    }
    echo "<script>alert('Chamado atualizado com sucesso!'); window.location.href='home.php';</script>";
} else {
    echo "<script>alert('Erro ao atualizar chamado.'); window.history.back();</script>";
}

$stmt->close();
$conn->close();
?>
