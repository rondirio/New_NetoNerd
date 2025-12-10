<?php
/**
 * Funções da API JWT
 * Contém a lógica de negócio para cada endpoint.
 * * @author NetoNerd Development Team
 * @version 1.0.0
 */

/**
 * Gerar novo token JWT
 */
function generateToken($jwtHandler, $db)
{
    $input = json_decode(file_get_contents('php://input'), true);
    
    $required = ['nome_empresa', 'tipo_projeto', 'email_owner', 'plano'];
    $missing = [];
    foreach ($required as $field) {
        if (!isset($input[$field]) || empty(trim($input[$field]))) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        sendJsonResponse(['error' => 'Campos obrigatórios ausentes', 'missing_fields' => $missing], 400);
    }
    
    $validProjects = ['myhealth', 'barbershop', 'suporte_ti'];
    if (!in_array($input['tipo_projeto'], $validProjects)) {
        sendJsonResponse(['error' => 'Tipo de projeto inválido', 'valid_types' => $validProjects], 400);
    }
    
    $validPlans = ['basico', 'profissional', 'premium', 'enterprise'];
    if (!in_array($input['plano'], $validPlans)) {
        sendJsonResponse(['error' => 'Plano inválido', 'valid_plans' => $validPlans], 400);
    }
    
    try {
        $db->begin_transaction();
        
        $stmt = $db->prepare("INSERT INTO tenants (nome_empresa, tipo_projeto, email_owner, telefone, plano) VALUES (?, ?, ?, ?, ?)");
        $telefone = $input['telefone'] ?? null;
        $stmt->bind_param('sssss', $input['nome_empresa'], $input['tipo_projeto'], $input['email_owner'], $telefone, $input['plano']);
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao criar tenant: " . $stmt->error);
        }
        
        $tenantId = $stmt->insert_id;
        
        $result = $db->query("SELECT tenant_id FROM tenants WHERE id = {$tenantId}");
        $tenant = $result->fetch_assoc();
        
        $tenantData = [
            'tenant_id' => $tenant['tenant_id'],
            'nome_empresa' => $input['nome_empresa'],
            'tipo_projeto' => $input['tipo_projeto'],
            'email_owner' => $input['email_owner'],
            'plano' => $input['plano']
        ];
        
        $tokenData = $jwtHandler->generateToken($tenantData);
        $jwtHandler->saveToken($tenantId, $tokenData);
        
        $db->commit();
        
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
        sendJsonResponse(['error' => 'Erro ao gerar token', 'message' => $e->getMessage()], 500);
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
        sendJsonResponse(['valid' => false, 'message' => 'Token inválido, expirado ou revogado'], 401);
    }
    
    sendJsonResponse(['valid' => true, 'message' => 'Token válido', 'data' => $payload], 200);
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
    $tokenHash = hash('sha256', $token);
    
    try {
        if ($jwtHandler->revokeToken($tokenHash, $motivo)) {
            sendJsonResponse(['success' => true, 'message' => 'Token revogado com sucesso'], 200);
        } else {
            sendJsonResponse(['error' => 'Erro ao revogar token ou token não encontrado'], 500);
        }
    } catch (Exception $e) {
        sendJsonResponse(['error' => 'Erro ao revogar token', 'message' => $e->getMessage()], 500);
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
        sendJsonResponse(['success' => true, 'tenant_id' => $tenantId, 'total' => count($tokens), 'tokens' => $tokens], 200);
    } catch (Exception $e) {
        sendJsonResponse(['error' => 'Erro ao listar tokens', 'message' => $e->getMessage()], 500);
    }
}

/**
 * Obter estatísticas
 */
function getStatistics($jwtHandler)
{
    try {
        $stats = $jwtHandler->getTokenStatistics();
        sendJsonResponse(['success' => true, 'statistics' => $stats], 200);
    } catch (Exception $e) {
        sendJsonResponse(['error' => 'Erro ao obter estatísticas', 'message' => $e->getMessage()], 500);
    }
}