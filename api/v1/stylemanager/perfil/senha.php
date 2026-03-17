<?php
/**
 * StyleManager API - Alterar Senha
 *
 * PUT /api/v1/stylemanager/perfil/senha
 *
 * Request:
 * {
 *   "senha_atual": "senha123",
 *   "nova_senha": "novaSenha456"
 * }
 */

require_once __DIR__ . '/../config/api_helper.php';

requireMethod('PUT');

$auth = requireAuth();
$conn = $auth['db']->getConnection();
$userId = $auth['user_id'];

$input = getJsonInput();

$errors = validateRequired($input, ['senha_atual', 'nova_senha']);
if ($errors) {
    errorResponse('Dados inválidos', 400, $errors);
}

$senhaAtual = $input['senha_atual'];
$novaSenha = $input['nova_senha'];

// Valida tamanho mínimo
if (strlen($novaSenha) < 6) {
    errorResponse('A nova senha deve ter no mínimo 6 caracteres', 400);
}

// Busca senha atual
$stmt = $conn->prepare("SELECT senha FROM usuarios WHERE id = ?");
$stmt->bind_param('i', $userId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    errorResponse('Usuário não encontrado', 404);
}

// Verifica senha atual
if (!password_verify($senhaAtual, $user['senha'])) {
    errorResponse('Senha atual incorreta', 400);
}

// Atualiza senha
$novaSenhaHash = password_hash($novaSenha, PASSWORD_BCRYPT);
$stmt = $conn->prepare("UPDATE usuarios SET senha = ?, updated_at = NOW() WHERE id = ?");
$stmt->bind_param('si', $novaSenhaHash, $userId);

if (!$stmt->execute()) {
    errorResponse('Erro ao alterar senha', 500);
}

$stmt->close();

successResponse(null, 'Senha alterada com sucesso');
