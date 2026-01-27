<?php
/**
 * Buscar Cliente via AJAX - NetoNerd ITSM v2.0
 * Busca cliente por nome e telefone
 */
session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireAdmin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['erro' => 'Método inválido']);
    exit();
}

$nome = trim($_POST['nome'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');

if (empty($nome) || empty($telefone)) {
    echo json_encode(['encontrado' => false]);
    exit();
}

$conn = getConnection();

// Limpar telefone (remover caracteres especiais)
$telefone_limpo = preg_replace('/[^0-9]/', '', $telefone);

// Buscar cliente por nome E telefone
$sql = "
    SELECT 
        id, nome, telefone, email, endereco, cpf
    FROM clientes
    WHERE LOWER(nome) LIKE LOWER(?)
      AND (
          REPLACE(REPLACE(REPLACE(REPLACE(telefone, '(', ''), ')', ''), '-', ''), ' ', '') LIKE ?
          OR telefone LIKE ?
      )
    LIMIT 1
";

$stmt = $conn->prepare($sql);
$nome_like = "%{$nome}%";
$telefone_like = "%{$telefone_limpo}%";
$telefone_original = "%{$telefone}%";

$stmt->bind_param('sss', $nome_like, $telefone_like, $telefone_original);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $cliente = $result->fetch_assoc();
    
    echo json_encode([
        'encontrado' => true,
        'cliente' => [
            'id' => $cliente['id'],
            'nome' => $cliente['nome'],
            'telefone' => $cliente['telefone'],
            'email' => $cliente['email'],
            'endereco' => $cliente['endereco'],
            'cpf' => $cliente['cpf']
        ]
    ]);
} else {
    echo json_encode(['encontrado' => false]);
}

$stmt->close();
$conn->close();
?>