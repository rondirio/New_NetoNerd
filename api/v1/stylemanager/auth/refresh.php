<?php
/**
 * StyleManager API - Refresh Token Endpoint
 *
 * POST /api/v1/stylemanager/auth/refresh
 *
 * Renova o token de acesso usando o refresh token.
 *
 * Request:
 * {
 *   "refresh_token": "eyJ..."
 * }
 *
 * Response (sucesso):
 * {
 *   "success": true,
 *   "data": {
 *     "token": "eyJ...",
 *     "refresh_token": "eyJ...",
 *     "expires_in": 2592000
 *   }
 * }
 */

require_once __DIR__ . '/../config/api_helper.php';

// Apenas POST
requireMethod('POST');

// Obtém dados da requisição
$input = getJsonInput();

// Valida campo obrigatório
if (empty($input['refresh_token'])) {
    errorResponse('Refresh token é obrigatório', 400);
}

$refreshToken = $input['refresh_token'];

// Renova o token
$jwt = new StyleManagerJWT();
$tokens = $jwt->refresh($refreshToken);

if (!$tokens) {
    errorResponse('Refresh token inválido ou expirado', 401);
}

// Retorna novos tokens
successResponse([
    'token' => $tokens['token'],
    'refresh_token' => $tokens['refresh_token'],
    'expires_in' => $tokens['expires_in']
]);
