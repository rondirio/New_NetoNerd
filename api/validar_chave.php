<?php
/**
 * Endpoint de Validação de Chave API - NetoNerd ITSM
 *
 * Valida a chave API e retorna os dados de conexão do banco de dados do cliente.
 *
 * Uso:
 * POST /api/validar_chave.php
 * Body JSON: { "api_key": "SUA_CHAVE_AQUI" }
 *
 * OU via Header:
 * Authorization: Bearer SUA_CHAVE_AQUI
 * X-API-Key: SUA_CHAVE_AQUI
 *
 * Respostas:
 * - 200: Chave válida + dados de conexão do BD
 * - 401: Chave inválida, expirada ou revogada
 * - 400: Chave não fornecida
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once '../config/bandoDeDados/conexao.php';

$conn = getConnection();

// Obter a chave API
$api_key = null;

// Header Authorization
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
    if (preg_match('/Bearer\s+(.*)$/i', $auth_header, $matches)) {
        $api_key = $matches[1];
    }
}

// Header X-API-Key
if (!$api_key && isset($_SERVER['HTTP_X_API_KEY'])) {
    $api_key = $_SERVER['HTTP_X_API_KEY'];
}

// Body JSON (POST)
if (!$api_key && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    if (isset($data['api_key'])) {
        $api_key = $data['api_key'];
    }
}

// Query string (GET)
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

$api_key = trim($api_key);

// Buscar a chave no banco
$stmt = $conn->prepare("SELECT id, chave, cliente_nome, db_host, db_nome, db_usuario, db_senha, db_porta, status, data_expiracao, ip_permitido FROM api_keys WHERE chave = ?");
$stmt->bind_param("s", $api_key);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
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

// Descriptografar senha do banco
$db_senha = base64_decode($key_data['db_senha']);

// Chave válida - retornar dados de conexão do BD
http_response_code(200);
echo json_encode([
    'success' => true,
    'message' => 'API key válida',
    'code' => 'API_KEY_VALID',
    'cliente' => $key_data['cliente_nome'],
    'database' => [
        'host' => $key_data['db_host'],
        'name' => $key_data['db_nome'],
        'user' => $key_data['db_usuario'],
        'password' => $db_senha,
        'port' => intval($key_data['db_porta'])
    ],
    'expira_em' => $key_data['data_expiracao']
]);

$conn->close();
?>
