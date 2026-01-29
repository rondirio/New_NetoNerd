<?php
/**
 * StyleManager API - Serviços Endpoint
 *
 * GET /api/v1/stylemanager/servicos - Lista serviços
 * GET /api/v1/stylemanager/servicos/{id} - Obtém serviço
 */

require_once __DIR__ . '/../config/api_helper.php';

requireMethod('GET');

$auth = requireAuth();
$conn = $auth['db']->getConnection();

$id = getPathId();

if ($id) {
    // Obtém serviço específico
    $stmt = $conn->prepare("SELECT id, nome, descricao, duracao, preco, ativo FROM servicos WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $servico = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$servico) {
        errorResponse('Serviço não encontrado', 404);
    }

    $servico['id'] = (int)$servico['id'];
    $servico['duracao'] = (int)$servico['duracao'];
    $servico['preco'] = (float)$servico['preco'];
    $servico['ativo'] = (bool)$servico['ativo'];

    successResponse($servico);
} else {
    // Lista todos os serviços ativos
    $stmt = $conn->prepare("SELECT id, nome, descricao, duracao, preco FROM servicos WHERE ativo = 1 ORDER BY nome");
    $stmt->execute();
    $result = $stmt->get_result();
    $servicos = [];

    while ($row = $result->fetch_assoc()) {
        $servicos[] = [
            'id' => (int)$row['id'],
            'nome' => $row['nome'],
            'descricao' => $row['descricao'],
            'duracao' => (int)$row['duracao'],
            'preco' => (float)$row['preco']
        ];
    }

    $stmt->close();
    successResponse($servicos);
}
