<?php
/**
 * Processador de ações LGPD (M2/M3) — anonimização de titular.
 */
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';
require_once '../controller/lgpd.php';

requireAdmin();
requireCsrfToken();

$conn = getConnection();

$acao = $_POST['acao'] ?? '';
$cliente_id = intval($_POST['cliente_id'] ?? 0);

if ($acao !== 'anonimizar' || $cliente_id <= 0) {
    header('Location: lgpd_titulares.php?msg=erro');
    exit;
}

$stmt = $conn->prepare("SELECT id FROM clientes WHERE id = ?");
$stmt->bind_param("i", $cliente_id);
$stmt->execute();
$existe = $stmt->get_result()->num_rows > 0;
$stmt->close();

if (!$existe) {
    header('Location: lgpd_titulares.php?msg=nao_encontrado');
    exit;
}

$admin_id = $_SESSION['id'] ?? $_SESSION['usuario_id'] ?? 0;
$sucesso = anonimizarCliente($conn, $cliente_id, $admin_id);

$conn->close();
header('Location: lgpd_titulares.php?msg=' . ($sucesso ? 'anonimizado' : 'erro'));
exit;
