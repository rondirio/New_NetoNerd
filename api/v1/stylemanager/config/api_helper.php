<?php
/**
 * StyleManager API - Helper Functions
 *
 * Funções auxiliares para a API REST.
 */

// Headers CORS e JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-API-Key, X-Requested-With');
header('Access-Control-Max-Age: 86400');

// Responde OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Carrega dependências
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/jwt.php';

/**
 * Resposta JSON padronizada
 */
function jsonResponse($data, int $statusCode = 200): void {
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Resposta de sucesso
 */
function successResponse($data = null, string $message = null, int $statusCode = 200): void {
    $response = ['success' => true];

    if ($message !== null) {
        $response['message'] = $message;
    }

    if ($data !== null) {
        $response['data'] = $data;
    }

    jsonResponse($response, $statusCode);
}

/**
 * Resposta de erro
 */
function errorResponse(string $message, int $statusCode = 400, $errors = null): void {
    $response = [
        'success' => false,
        'error' => $message
    ];

    if ($errors !== null) {
        $response['errors'] = $errors;
    }

    jsonResponse($response, $statusCode);
}

/**
 * Obtém corpo da requisição JSON
 */
function getJsonInput(): array {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    return is_array($data) ? $data : [];
}

/**
 * Obtém parâmetro da query string
 */
function getQueryParam(string $key, $default = null) {
    return $_GET[$key] ?? $default;
}

/**
 * Obtém API Key do header
 */
function getApiKey(): ?string {
    // Tenta header X-API-Key
    $apiKey = $_SERVER['HTTP_X_API_KEY'] ?? null;

    // Fallback para query string (não recomendado em produção)
    if (!$apiKey) {
        $apiKey = $_GET['api_key'] ?? null;
    }

    return $apiKey;
}

/**
 * Obtém token JWT do header Authorization
 */
function getBearerToken(): ?string {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;

    if (!$authHeader) {
        // Tenta de outras formas
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null;
        }
    }

    if (!$authHeader) {
        return null;
    }

    if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        return $matches[1];
    }

    return null;
}

/**
 * Requer autenticação via JWT
 * Retorna os dados do usuário autenticado
 */
function requireAuth(): array {
    $token = getBearerToken();

    if (!$token) {
        errorResponse('Token de autenticação não fornecido', 401);
    }

    $jwt = new StyleManagerJWT();
    $payload = $jwt->validate($token);

    if (!$payload) {
        errorResponse('Token inválido ou expirado', 401);
    }

    // Obtém a conexão com o banco do estabelecimento
    $db = StyleManagerDatabase::getInstance($payload['api_key']);

    if (!$db) {
        errorResponse('Estabelecimento não encontrado', 401);
    }

    return [
        'user_id' => $payload['user_id'],
        'user_tipo' => $payload['user_tipo'],
        'user_nome' => $payload['user_nome'],
        'api_key' => $payload['api_key'],
        'estabelecimento' => $payload['estabelecimento'],
        'db' => $db
    ];
}

/**
 * Requer um tipo específico de usuário
 */
function requireUserType(array $allowedTypes): array {
    $auth = requireAuth();

    if (!in_array($auth['user_tipo'], $allowedTypes)) {
        errorResponse('Acesso não autorizado para este tipo de usuário', 403);
    }

    return $auth;
}

/**
 * Requer admin
 */
function requireAdmin(): array {
    return requireUserType(['admin']);
}

/**
 * Requer admin ou recepcionista
 */
function requireAdminOrRecepcionista(): array {
    return requireUserType(['admin', 'recepcionista']);
}

/**
 * Requer profissional, admin ou recepcionista
 */
function requireStaff(): array {
    return requireUserType(['admin', 'recepcionista', 'profissional']);
}

/**
 * Valida campos obrigatórios
 */
function validateRequired(array $data, array $fields): array {
    $errors = [];

    foreach ($fields as $field) {
        if (!isset($data[$field]) || trim($data[$field]) === '') {
            $errors[$field] = "O campo '$field' é obrigatório";
        }
    }

    return $errors;
}

/**
 * Valida email
 */
function validateEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Valida data no formato Y-m-d
 */
function validateDate(string $date): bool {
    $d = DateTime::createFromFormat('Y-m-d', $date);
    return $d && $d->format('Y-m-d') === $date;
}

/**
 * Valida hora no formato H:i ou H:i:s
 */
function validateTime(string $time): bool {
    $t = DateTime::createFromFormat('H:i', $time);
    if ($t && $t->format('H:i') === $time) return true;

    $t = DateTime::createFromFormat('H:i:s', $time);
    return $t && $t->format('H:i:s') === $time;
}

/**
 * Sanitiza string para prevenir XSS
 */
function sanitize(string $value): string {
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

/**
 * Formata valor monetário
 */
function formatMoney(float $value): string {
    return number_format($value, 2, ',', '.');
}

/**
 * Pagina resultados
 */
function paginate(mysqli $conn, string $baseQuery, array $params, string $types, int $page = 1, int $limit = 20): array {
    // Query para contar total
    $countQuery = "SELECT COUNT(*) as total FROM ($baseQuery) as subquery";
    $stmt = $conn->prepare($countQuery);

    if ($params) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // Query com paginação
    $offset = ($page - 1) * $limit;
    $paginatedQuery = "$baseQuery LIMIT ? OFFSET ?";

    $stmt = $conn->prepare($paginatedQuery);
    $newParams = array_merge($params, [$limit, $offset]);
    $newTypes = $types . 'ii';
    $stmt->bind_param($newTypes, ...$newParams);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return [
        'data' => $data,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => (int)$total,
            'total_pages' => ceil($total / $limit)
        ]
    ];
}

/**
 * Log de erro
 */
function logError(string $message, array $context = []): void {
    $logMessage = date('Y-m-d H:i:s') . " - $message";

    if ($context) {
        $logMessage .= " - " . json_encode($context);
    }

    error_log($logMessage);
}

/**
 * Verifica método HTTP
 */
function requireMethod(string ...$methods): void {
    $currentMethod = $_SERVER['REQUEST_METHOD'];

    if (!in_array($currentMethod, $methods)) {
        errorResponse("Método $currentMethod não permitido", 405);
    }
}

/**
 * Obtém ID do path (para rotas como /agendamentos/123)
 */
function getPathId(): ?int {
    $path = $_SERVER['PATH_INFO'] ?? '';
    $parts = explode('/', trim($path, '/'));

    if (count($parts) > 0 && is_numeric($parts[0])) {
        return (int)$parts[0];
    }

    return null;
}
