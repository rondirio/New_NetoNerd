<?php
/**
 * Exportação de dados do titular (M2 — portabilidade, art. 18 LGPD).
 */
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';
require_once '../controller/lgpd.php';

requireAdmin();

$conn = getConnection();

$cliente_id = intval($_GET['cliente_id'] ?? 0);

if ($cliente_id <= 0) {
    header('Location: lgpd_titulares.php?msg=erro');
    exit;
}

$dados = exportarDadosCliente($conn, $cliente_id);

if (!$dados['cliente']) {
    header('Location: lgpd_titulares.php?msg=nao_encontrado');
    exit;
}

registrarLogSistema($conn, $_SESSION['id'] ?? $_SESSION['usuario_id'] ?? 0, "Exportou dados do cliente #$cliente_id (LGPD)", 'cliente', $cliente_id);

$conn->close();

header('Content-Type: application/json; charset=utf-8');
header('Content-Disposition: attachment; filename="dados_cliente_' . $cliente_id . '_' . date('Ymd_His') . '.json"');
echo json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
exit;
