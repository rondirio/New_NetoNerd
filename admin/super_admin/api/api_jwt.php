<?php
/**
 * API JWT Endpoints - Super Admin NetoNerd
 * Endpoints para geração, validação e gerenciamento de tokens JWT
 * 
 * @author NetoNerd Development Team
 * @version 1.0.0
 */

// Headers CORS e JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Projeto');

// Responder OPTIONS (preflight)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Incluir configuração do banco
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/core/JWTHandler.php';

use NetoNerd\Core\JWTHandler;

// Inicializar conexão com banco
$db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($db->connect_error) {
    sendJsonResponse(['error' => 'Erro de conexão com banco de dados'], 500);
}

$db->set_charset('utf8mb4');

// Inicializar JWT Handler
try {
    $jwtHandler = new JWTHandler($db);
} catch (Exception $e) {
    sendJsonResponse(['error' => 'Erro ao inicializar JWT Handler: ' . $e->getMessage()], 500);
}

// Roteamento da API
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$pathParts = explode('/', trim($path, '/'));

// Última parte da URL define a ação
$action = end($pathParts);

// Processar ações
switch ($action) {
    case 'generate':
        if ($method === 'POST') {
            generateToken($jwtHandler, $db);
        } else {
            sendJsonResponse(['error' => 'Método não permitido'], 405);
        }
        break;
        
    case 'validate':
        if ($method === 'POST') {
            validateToken($jwtHandler);
        } else {
            sendJsonResponse(['error' => 'Método não permitido'], 405);
        }
        break;
        
    case 'revoke':
        if ($method === 'POST') {
            revokeToken($jwtHandler);
        } else {
            sendJsonResponse(['error' => 'Método não permitido'], 405);
        }
        break;
        
    case 'list':
        if ($method === 'GET') {
            listTokens($jwtHandler);
        } else {
            sendJsonResponse(['error' => 'Método não permitido'], 405);
        }
        break;
        
    case 'stats':
        if ($method === 'GET') {
            getStatistics($jwtHandler);
        } else {
            sendJsonResponse(['error' => 'Método não permitido'], 405);
        }
        break;
        
    default:
        sendJsonResponse([
            'message' => 'API JWT - Super Admin NetoNerd',
            'version' => '1.0.0',
            'endpoints' => [
                'POST /api/jwt/generate' => 'Gerar novo token',
                'POST /api/jwt/validate' => 'Validar token existente',
                'POST /api/jwt/revoke' => 'Revogar token',
                'GET /api/jwt/list?tenant_id=XXX' => 'Listar tokens de um tenant',
                'GET /api/jwt/stats' => 'Obter estatísticas'
            ]
        ], 200);
}

/**
 * Gerar novo token JWT
 */
function generateToken($jwtHandler, $db)
{
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validar dados obrigatórios
    $required = ['nome_empresa', 'tipo_projeto', 'email_owner', 'plano'];
    $missing = [];
    
    foreach ($required as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        sendJsonResponse([
            'error' => 'Campos obrigatórios ausentes',
            'missing_fields' => $missing
        ], 400);
    }
    
    // Validar tipo de projeto
    $validProjects = ['myhealth', 'barbershop', 'suporte_ti'];
    if (!in_array($input['tipo_projeto'], $validProjects)) {
        sendJsonResponse([
            'error' => 'Tipo de projeto inválido',
            'valid_types' => $validProjects
        ], 400);
    }
    
    // Validar plano
    $validPlans = ['basico', 'profissional', 'premium', 'enterprise'];
    if (!in_array($input['plano'], $validPlans)) {
        sendJsonResponse([
            'error' => 'Plano inválido',
            'valid_plans' => $validPlans
        ], 400);
    }
    
    try {
        // Iniciar transação
        $db->begin_transaction();
        
        // Criar tenant no banco
        $stmt = $db->prepare(
            "INSERT INTO tenants (nome_empresa, tipo_projeto, email_owner, telefone, plano) 
             VALUES (?, ?, ?, ?, ?)"
        );
        
        $telefone = $input['telefone'] ?? null;
        
        $stmt->bind_param(
            'sssss',
            $input['nome_empresa'],
            $input['tipo_projeto'],
            $input['email_owner'],
            $telefone,
            $input['plano']
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao criar tenant: " . $stmt->error);
        }
        
        $tenantId = $stmt->insert_id;
        
        // Buscar tenant_id gerado pelo trigger
        $result = $db->query("SELECT tenant_id FROM tenants WHERE id = {$tenantId}");
        $tenant = $result->fetch_assoc();
        
        // Preparar dados para geração do token
        $tenantData = [
            'tenant_id' => $tenant['tenant_id'],
            'nome_empresa' => $input['nome_empresa'],
            'tipo_projeto' => $input['tipo_projeto'],
            'email_owner' => $input['email_owner'],
            'plano' => $input['plano']
        ];
        
        // Gerar token JWT
        $tokenData = $jwtHandler->generateToken($tenantData);
        
        // Salvar token no banco
        $jwtHandler->saveToken($tenantId, $tokenData);
        
        // Commit da transação
        $db->commit();
        
        // Resposta de sucesso
        sendJsonResponse([
            'success' => true,
            'message' => 'Token JWT gerado com sucesso',
            'data' => [
                'tenant_id' => $tenant['tenant_id'],
                'token' => $tokenData['token'],
                'token_hash' => $tokenData['token_hash'],
                'empresa' => $input['nome_empresa'],
                'projeto' => $input['tipo_projeto'],
                'plano' => $input['plano'],
                'issued_at' => $tokenData['issued_at'],
                'expires_at' => $tokenData['expiration_date']
            ]
        ], 201);
        
    } catch (Exception $e) {
        $db->rollback();
        sendJsonResponse([
            'error' => 'Erro ao gerar token',
            'message' => $e->getMessage()
        ], 500);
    }
}

/**
 * Validar token JWT
 */
function validateToken($jwtHandler)
{
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['token']) || empty($input['token'])) {
        sendJsonResponse(['error' => 'Token não fornecido'], 400);
    }
    
    $token = $input['token'];
    $payload = $jwtHandler->validateToken($token);
    
    if ($payload === false) {
        sendJsonResponse([
            'valid' => false,
            'message' => 'Token inválido, expirado ou revogado'
        ], 401);
    }
    
    sendJsonResponse([
        'valid' => true,
        'message' => 'Token válido',
        'data' => $payload
    ], 200);
}

/**
 * Revogar token
 */
function revokeToken($jwtHandler)
{
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($input['token']) || empty($input['token'])) {
        sendJsonResponse(['error' => 'Token não fornecido'], 400);
    }
    
    $token = $input['token'];
    $motivo = $input['motivo'] ?? 'Revogado via API';
    
    // Calcular hash do token
    $tokenHash = hash('sha256', $token);
    
    try {
        $success = $jwtHandler->revokeToken($tokenHash, $motivo);
        
        if ($success) {
            sendJsonResponse([
                'success' => true,
                'message' => 'Token revogado com sucesso'
            ], 200);
        } else {
            sendJsonResponse([
                'error' => 'Erro ao revogar token'
            ], 500);
        }
    } catch (Exception $e) {
        sendJsonResponse([
            'error' => 'Erro ao revogar token',
            'message' => $e->getMessage()
        ], 500);
    }
}

/**
 * Listar tokens de um tenant
 */
function listTokens($jwtHandler)
{
    $tenantId = $_GET['tenant_id'] ?? null;
    
    if (!$tenantId) {
        sendJsonResponse(['error' => 'tenant_id não fornecido'], 400);
    }
    
    try {
        $tokens = $jwtHandler->getTokensByTenant($tenantId);
        
        sendJsonResponse([
            'success' => true,
            'tenant_id' => $tenantId,
            'total' => count($tokens),
            'tokens' => $tokens
        ], 200);
        
    } catch (Exception $e) {
        sendJsonResponse([
            'error' => 'Erro ao listar tokens',
            'message' => $e->getMessage()
        ], 500);
    }
}

/**
 * Obter estatísticas
 */
function getStatistics($jwtHandler)
{
    try {
        $stats = $jwtHandler->getTokenStatistics();
        
        sendJsonResponse([
            'success' => true,
            'statistics' => $stats
        ], 200);
        
    } catch (Exception $e) {
        sendJsonResponse([
            'error' => 'Erro ao obter estatísticas',
            'message' => $e->getMessage()
        ], 500);
    }
}

/**
 * Enviar resposta JSON
 */
function sendJsonResponse($data, $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// Fechar conexão
$db->close();