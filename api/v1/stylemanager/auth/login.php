<?php
/**
 * StyleManager API - Login Endpoint
 *
 * POST /api/v1/stylemanager/auth/login
 *
 * Autentica usuário e retorna token JWT.
 * O estabelecimento é identificado automaticamente pelo email do usuário
 * ou pela API Key fornecida no header.
 *
 * Request:
 * {
 *   "email": "usuario@email.com",
 *   "senha": "senha123"
 * }
 *
 * Response (sucesso):
 * {
 *   "success": true,
 *   "data": {
 *     "token": "eyJ...",
 *     "refresh_token": "eyJ...",
 *     "expires_in": 2592000,
 *     "usuario": { ... },
 *     "estabelecimento": { ... },
 *     "api_key": "abc123..."
 *   }
 * }
 */

require_once __DIR__ . '/../config/api_helper.php';

// Apenas POST
requireMethod('POST');

// Obtém dados da requisição
$input = getJsonInput();

// Valida campos obrigatórios
$errors = validateRequired($input, ['email', 'senha']);
if ($errors) {
    errorResponse('Dados inválidos', 400, $errors);
}

$email = strtolower(trim($input['email']));
$senha = $input['senha'];

// Valida formato do email
if (!validateEmail($email)) {
    errorResponse('Email inválido', 400);
}

// Verifica se foi fornecida API Key
$apiKey = getApiKey();

$user = null;
$estabelecimento = null;
$db = null;

if ($apiKey) {
    // Com API Key: conecta direto ao estabelecimento
    $db = StyleManagerDatabase::getInstance($apiKey);

    if (!$db) {
        errorResponse('Chave de API inválida ou estabelecimento não encontrado', 401);
    }

    $estabelecimento = $db->getEstabelecimento();
    $conn = $db->getConnection();

    // Busca usuário pelo email
    $stmt = $conn->prepare("
        SELECT id, nome, email, senha, tipo, telefone, foto, ativo,
               data_nascimento, observacoes
        FROM usuarios
        WHERE email = ?
        LIMIT 1
    ");

    if (!$stmt) {
        errorResponse('Erro interno do servidor', 500);
    }

    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

} else {
    // Sem API Key: busca usuário em todos os estabelecimentos
    $found = StyleManagerDatabase::findUserByEmail($email);

    if ($found) {
        $user = $found['user'];
        $apiKey = $found['api_key'];
        $estabelecimento = $found['estabelecimento'];
        $db = StyleManagerDatabase::getInstance($apiKey);
    }
}

// Verifica se encontrou o usuário
if (!$user) {
    errorResponse('Email ou senha inválidos', 401);
}

// Verifica se o usuário está ativo
if (!$user['ativo']) {
    errorResponse('Usuário desativado. Entre em contato com o administrador.', 403);
}

// Verifica senha
if (!password_verify($senha, $user['senha'])) {
    errorResponse('Email ou senha inválidos', 401);
}

// Remove a senha dos dados do usuário
unset($user['senha']);

// Gera tokens JWT
$jwt = new StyleManagerJWT();
$tokens = $jwt->generateTokenPair([
    'id' => $user['id'],
    'nome' => $user['nome'],
    'tipo' => $user['tipo'],
    'email' => $user['email'],
    'api_key' => $apiKey,
    'estabelecimento' => $estabelecimento
]);

// Atualiza último acesso
$conn = $db->getConnection();
$stmt = $conn->prepare("UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = ?");
if ($stmt) {
    $stmt->bind_param('i', $user['id']);
    $stmt->execute();
    $stmt->close();
}

// Retorna resposta de sucesso
successResponse([
    'token' => $tokens['token'],
    'refresh_token' => $tokens['refresh_token'],
    'expires_in' => $tokens['expires_in'],
    'usuario' => $user,
    'estabelecimento' => $estabelecimento,
    'api_key' => $apiKey
]);
