<?php
/**
 * StyleManager API - Logout Endpoint
 *
 * POST /api/v1/stylemanager/auth/logout
 *
 * Invalida o token atual (client-side, já que JWT é stateless).
 * Em uma implementação mais robusta, adicionaria o token a uma blacklist.
 *
 * Response (sucesso):
 * {
 *   "success": true,
 *   "message": "Logout realizado com sucesso"
 * }
 */

require_once __DIR__ . '/../config/api_helper.php';

// Apenas POST
requireMethod('POST');

// Valida autenticação (opcional - apenas para confirmar que o token era válido)
$token = getBearerToken();

if ($token) {
    $jwt = new StyleManagerJWT();
    $payload = $jwt->validate($token);

    // Aqui poderíamos adicionar o token a uma blacklist se necessário
    // Por enquanto, o logout é apenas client-side (remove o token do dispositivo)
}

// Retorna sucesso
successResponse(null, 'Logout realizado com sucesso');
