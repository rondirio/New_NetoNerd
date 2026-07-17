<?php
/**
 * Confirma a senha do admin logado para revelar dado sensível (CPF) — AJAX
 * NetoNerd ITSM
 */
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Método não permitido']);
    exit();
}

if (!isValidCsrfToken()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Token de segurança inválido. Recarregue a página.']);
    exit();
}

$senha = $_POST['password'] ?? '';
if (empty($senha)) {
    echo json_encode(['ok' => false, 'message' => 'Senha não informada.']);
    exit();
}

$conn = getConnection();
$admin_id = $_SESSION['id'];

$stmt = $conn->prepare("SELECT senha_hash FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

if (!$admin || !password_verify($senha, $admin['senha_hash'])) {
    echo json_encode(['ok' => false, 'message' => 'Senha incorreta.']);
    exit();
}

echo json_encode(['ok' => true]);
