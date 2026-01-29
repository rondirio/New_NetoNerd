<?php
/**
 * StyleManager API - Profissionais Endpoint
 *
 * GET /api/v1/stylemanager/profissionais - Lista profissionais
 * GET /api/v1/stylemanager/profissionais/{id} - Obtém profissional
 */

require_once __DIR__ . '/../config/api_helper.php';

requireMethod('GET');

$auth = requireAuth();
$conn = $auth['db']->getConnection();

$id = getPathId();

if ($id) {
    // Obtém profissional específico
    $stmt = $conn->prepare("
        SELECT id, nome, email, telefone, foto,
               (SELECT COUNT(*) FROM agendamentos WHERE profissional_id = usuarios.id AND status = 'finalizado') as total_atendimentos
        FROM usuarios
        WHERE id = ? AND tipo = 'profissional' AND ativo = 1
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $profissional = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$profissional) {
        errorResponse('Profissional não encontrado', 404);
    }

    $profissional['id'] = (int)$profissional['id'];
    $profissional['total_atendimentos'] = (int)$profissional['total_atendimentos'];

    successResponse($profissional);
} else {
    // Lista todos os profissionais ativos
    $stmt = $conn->prepare("
        SELECT id, nome, email, telefone, foto
        FROM usuarios
        WHERE tipo = 'profissional' AND ativo = 1
        ORDER BY nome
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $profissionais = [];

    while ($row = $result->fetch_assoc()) {
        $profissionais[] = [
            'id' => (int)$row['id'],
            'nome' => $row['nome'],
            'email' => $row['email'],
            'telefone' => $row['telefone'],
            'foto' => $row['foto']
        ];
    }

    $stmt->close();
    successResponse($profissionais);
}
