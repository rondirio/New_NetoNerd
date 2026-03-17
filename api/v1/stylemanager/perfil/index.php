<?php
/**
 * StyleManager API - Perfil Endpoint
 *
 * GET /api/v1/stylemanager/perfil - Obtém perfil do usuário
 * PUT /api/v1/stylemanager/perfil - Atualiza perfil
 */

require_once __DIR__ . '/../config/api_helper.php';

$auth = requireAuth();
$conn = $auth['db']->getConnection();
$userId = $auth['user_id'];

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        getPerfil($conn, $userId);
        break;

    case 'PUT':
        updatePerfil($conn, $userId);
        break;

    default:
        errorResponse('Método não permitido', 405);
}

function getPerfil(mysqli $conn, int $userId): void {
    $stmt = $conn->prepare("
        SELECT id, nome, email, telefone, tipo, foto, data_nascimento, observacoes, created_at
        FROM usuarios
        WHERE id = ?
    ");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$user) {
        errorResponse('Usuário não encontrado', 404);
    }

    $user['id'] = (int)$user['id'];

    successResponse($user);
}

function updatePerfil(mysqli $conn, int $userId): void {
    $input = getJsonInput();

    $updates = [];
    $params = [];
    $types = '';

    $allowedFields = ['nome', 'telefone', 'data_nascimento', 'observacoes'];

    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updates[] = "$field = ?";
            $params[] = $input[$field];
            $types .= 's';
        }
    }

    if (empty($updates)) {
        errorResponse('Nenhum campo para atualizar', 400);
    }

    $sql = "UPDATE usuarios SET " . implode(', ', $updates) . ", updated_at = NOW() WHERE id = ?";
    $params[] = $userId;
    $types .= 'i';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if (!$stmt->execute()) {
        errorResponse('Erro ao atualizar perfil', 500);
    }

    $stmt->close();

    getPerfil($conn, $userId);
}
