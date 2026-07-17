<?php
/**
 * Excluir Ordem de Serviço - NetoNerd ITSM v2.1
 */
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar_ordens_servico.php?erro=metodo_invalido');
    exit();
}

requireCsrfToken();

$conn = getConnection();

$os_id = intval($_POST['os_id']);

$stmt_check = $conn->prepare("SELECT id FROM ordens_servico WHERE id = ?");
$stmt_check->bind_param('i', $os_id);
$stmt_check->execute();
$existe = $stmt_check->get_result()->num_rows > 0;
$stmt_check->close();

if (!$existe) {
    $conn->close();
    header('Location: listar_ordens_servico.php?erro=' . urlencode('Ordem de serviço não encontrada'));
    exit();
}

$stmt = $conn->prepare("DELETE FROM ordens_servico WHERE id = ?");
$stmt->bind_param('i', $os_id);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    header('Location: listar_ordens_servico.php?sucesso=' . urlencode('Ordem de serviço excluída com sucesso'));
    exit();
} else {
    $erro = $stmt->error;
    $stmt->close();
    $conn->close();
    header('Location: visualizar_ordem_servico.php?id=' . $os_id . '&erro=' . urlencode('Erro ao excluir ordem: ' . $erro));
    exit();
}
