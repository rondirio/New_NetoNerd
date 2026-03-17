<?php
/**
 * Bootstrap da API
 * Inicializa configurações, conexão com banco e JWT Handler
 * * @author NetoNerd Development Team
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

// Incluir dependências
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../core/JWTHandler.php';

use NetoNerd\Core\JWTHandler;

// Função para enviar respostas JSON (usada por todos os endpoints)
function sendJsonResponse($data, $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

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

// Fechar conexão no final do script
register_shutdown_function(function() use ($db) {
    $db->close();
});