<?php
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireAdmin();

header('Content-Type: application/json');

$termo = trim($_GET['termo'] ?? '');

if (mb_strlen($termo) < 3) {
    echo json_encode([]);
    exit();
}

$conn = getConnection();

$stmt = $conn->prepare("SELECT id, nome, email, telefone FROM clientes WHERE nome LIKE ? ORDER BY nome ASC LIMIT 20");
$like = '%' . $termo . '%';
$stmt->bind_param("s", $like);
$stmt->execute();
$result = $stmt->get_result();

$clientes = [];
while ($row = $result->fetch_assoc()) {
    $clientes[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode($clientes);
