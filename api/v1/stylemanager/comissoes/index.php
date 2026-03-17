<?php
/**
 * StyleManager API - Comissões Endpoint
 *
 * GET /api/v1/stylemanager/comissoes - Lista comissões
 */

require_once __DIR__ . '/../config/api_helper.php';

requireMethod('GET');

$auth = requireAuth();
$conn = $auth['db']->getConnection();
$userId = $auth['user_id'];
$userTipo = $auth['user_tipo'];

// Apenas profissionais e admins podem ver comissões
if (!in_array($userTipo, ['profissional', 'admin'])) {
    errorResponse('Acesso não autorizado', 403);
}

$dataInicio = getQueryParam('data_inicio', date('Y-m-01'));
$dataFim = getQueryParam('data_fim', date('Y-m-d'));
$page = max(1, (int)getQueryParam('page', 1));
$limit = min(100, max(1, (int)getQueryParam('limit', 20)));

$where = ['a.data BETWEEN ? AND ?', 'a.status = "finalizado"'];
$params = [$dataInicio, $dataFim];
$types = 'ss';

// Profissional só vê suas próprias comissões
if ($userTipo === 'profissional') {
    $where[] = 'c.profissional_id = ?';
    $params[] = $userId;
    $types .= 'i';
}

$whereClause = implode(' AND ', $where);

$sql = "SELECT c.id, c.valor_comissao, c.valor_servico, c.percentual,
               a.id as agendamento_id, a.data, a.hora_inicio,
               s.nome as servico_nome,
               cl.nome as cliente_nome,
               p.nome as profissional_nome
        FROM comissoes c
        JOIN agendamentos a ON c.agendamento_id = a.id
        LEFT JOIN servicos s ON a.servico_id = s.id
        LEFT JOIN usuarios cl ON a.cliente_id = cl.id
        LEFT JOIN usuarios p ON c.profissional_id = p.id
        WHERE $whereClause
        ORDER BY a.data DESC, a.hora_inicio DESC";

$result = paginate($conn, $sql, $params, $types, $page, $limit);

// Formata resultados
$comissoes = array_map(function($row) {
    return [
        'id' => (int)$row['id'],
        'valor_comissao' => (float)$row['valor_comissao'],
        'valor_servico' => (float)$row['valor_servico'],
        'percentual' => (float)$row['percentual'],
        'agendamento_id' => (int)$row['agendamento_id'],
        'data' => $row['data'],
        'hora' => substr($row['hora_inicio'], 0, 5),
        'servico' => $row['servico_nome'],
        'cliente' => $row['cliente_nome'],
        'profissional' => $row['profissional_nome']
    ];
}, $result['data']);

successResponse([
    'data' => $comissoes,
    'pagination' => $result['pagination']
]);
