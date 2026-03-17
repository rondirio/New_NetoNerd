<?php
/**
 * StyleManager API - Validate License Endpoint
 *
 * GET/POST /api/v1/stylemanager/validate-license
 *
 * Valida uma API Key e retorna informações do estabelecimento.
 * Usado para configuração manual do app.
 *
 * Header:
 *   X-API-Key: abc123...
 *
 * Response (sucesso):
 * {
 *   "success": true,
 *   "data": {
 *     "valid": true,
 *     "estabelecimento": { ... }
 *   }
 * }
 */

require_once __DIR__ . '/config/api_helper.php';

// GET ou POST
requireMethod('GET', 'POST');

// Obtém API Key
$apiKey = getApiKey();

if (!$apiKey) {
    errorResponse('API Key não fornecida', 400);
}

// Tenta conectar ao estabelecimento
$db = StyleManagerDatabase::getInstance($apiKey);

if (!$db) {
    successResponse([
        'valid' => false,
        'message' => 'Chave de API inválida ou expirada'
    ]);
}

$estabelecimento = $db->getEstabelecimento();

successResponse([
    'valid' => true,
    'estabelecimento' => $estabelecimento
]);
