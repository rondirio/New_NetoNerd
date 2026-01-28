<?php
/**
 * Endpoint de Validação de Chave API - NetoNerd ITSM
 *
 * Este endpoint valida se uma chave API é válida para uso no aplicativo.
 *
 * Uso:
 * POST /api/validar_chave.php
 * Body JSON: { "api_key": "SUA_CHAVE_AQUI" }
 *
 * OU
 *
 * GET /api/validar_chave.php?api_key=SUA_CHAVE_AQUI
 *
 * Respostas:
 * - 200: Chave válida
 * - 401: Chave inválida, expirada ou revogada
 * - 400: Chave não fornecida
 * - 403: IP não autorizado
 */

// Headers para API REST
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

// Preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/bandoDeDados/conexao.php';

$conn = getConnection();

// Obter a chave API
$api_key = null;

// Verificar no header Authorization
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
    if (preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
        $api_key = $matches[1];
    }
}

// Verificar no header X-API-Key
if (!$api_key && isset($_SERVER['HTTP_X_API_KEY'])) {
    $api_key = $_SERVER['HTTP_X_API_KEY'];
}

// Verificar no body JSON (POST)
if (!$api_key && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    if (isset($data['api_key'])) {
        $api_key = $data['api_key'];
    }
}

// Verificar no query string (GET)
if (!$api_key && isset($_GET['api_key'])) {
    $api_key = $_GET['api_key'];
}

// Chave não fornecida
if (!$api_key) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'API key não fornecida',
        'code' => 'MISSING_API_KEY'
    ]);
    exit;
}

// Sanitizar a chave
$api_key = trim($api_key);

// Buscar a chave no banco
$stmt = $conn->prepare("SELECT id, chave, cliente_nome, status, data_expiracao, ip_permitido FROM api_keys WHERE chave = ?");
$stmt->bind_param("s", $api_key);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    // Chave não encontrada
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'API key inválida',
        'code' => 'INVALID_API_KEY'
    ]);
    $stmt->close();
    $conn->close();
    exit;
}

$key_data = $result->fetch_assoc();
$stmt->close();

// Verificar status
if ($key_data['status'] !== 'ativa') {
    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'API key ' . $key_data['status'],
        'code' => 'API_KEY_' . strtoupper($key_data['status'])
    ]);
    $conn->close();
    exit;
}

// Verificar expiração
if ($key_data['data_expiracao'] && strtotime($key_data['data_expiracao']) < time()) {
    // Atualizar status para expirada
    $stmt = $conn->prepare("UPDATE api_keys SET status = 'revogada' WHERE id = ?");
    $stmt->bind_param("i", $key_data['id']);
    $stmt->execute();
    $stmt->close();

    http_response_code(401);
    echo json_encode([
        'success' => false,
        'error' => 'API key expirada',
        'code' => 'API_KEY_EXPIRED'
    ]);
    $conn->close();
    exit;
}

// Verificar IP permitido
$client_ip = $_SERVER['REMOTE_ADDR'];
if (!empty($key_data['ip_permitido'])) {
    $ips_permitidos = array_map('trim', explode(',', $key_data['ip_permitido']));
    if (!in_array($client_ip, $ips_permitidos)) {
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'error' => 'IP não autorizado',
            'code' => 'IP_NOT_ALLOWED'
        ]);
        $conn->close();
        exit;
    }
}

// Atualizar último uso e contador
$stmt = $conn->prepare("UPDATE api_keys SET ultimo_uso = NOW(), total_requisicoes = total_requisicoes + 1 WHERE id = ?");
$stmt->bind_param("i", $key_data['id']);
$stmt->execute();
$stmt->close();

// Registrar log de acesso (se tabela existir)
$result = $conn->query("SHOW TABLES LIKE 'api_keys_log'");
if ($result->num_rows > 0) {
    $endpoint = $_SERVER['REQUEST_URI'] ?? '';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $stmt = $conn->prepare("INSERT INTO api_keys_log (api_key_id, ip_address, endpoint, user_agent, resposta_status) VALUES (?, ?, ?, ?, 200)");
    $stmt->bind_param("isss", $key_data['id'], $client_ip, $endpoint, $user_agent);
    $stmt->execute();
    $stmt->close();
}

// Chave válida!
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'API key válida',
    'code' => 'API_KEY_VALID',
    'data' => [
        'cliente' => $key_data['cliente_nome'] ?? null,
        'expira_em' => $key_data['data_expiracao'] ?? null
    ]
]);

$conn->close();
?>
