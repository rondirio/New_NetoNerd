<?php
/**
 * StyleManager API - Resumo de Comissões
 *
 * GET /api/v1/stylemanager/comissoes/resumo
 *
 * Query params:
 *   - data_inicio: Data início (Y-m-d)
 *   - data_fim: Data fim (Y-m-d)
 */

require_once __DIR__ . '/../config/api_helper.php';

requireMethod('GET');

$auth = requireAuth();
$conn = $auth['db']->getConnection();
$userId = $auth['user_id'];
$userTipo = $auth['user_tipo'];

if (!in_array($userTipo, ['profissional', 'admin'])) {
    errorResponse('Acesso não autorizado', 403);
}

$dataInicio = getQueryParam('data_inicio', date('Y-m-01'));
$dataFim = getQueryParam('data_fim', date('Y-m-d'));

$profissionalFilter = '';
$params = [$dataInicio, $dataFim];
$types = 'ss';

if ($userTipo === 'profissional') {
    $profissionalFilter = 'AND c.profissional_id = ?';
    $params[] = $userId;
    $types .= 'i';
}

// Total de serviços e comissões
$sql = "SELECT
            COUNT(*) as total_servicos,
            COALESCE(SUM(c.valor_comissao), 0) as total_comissoes,
            COALESCE(SUM(c.valor_servico), 0) as total_faturado
        FROM comissoes c
        JOIN agendamentos a ON c.agendamento_id = a.id
        WHERE a.data BETWEEN ? AND ? AND a.status = 'finalizado'
        $profissionalFilter";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$totais = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Gorjetas do período
$sql = "SELECT COALESCE(SUM(valor), 0) as total
        FROM gorjetas
        WHERE data BETWEEN ? AND ?";

if ($userTipo === 'profissional') {
    $sql .= " AND profissional_id = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$gorjetas = (float)$stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// Vales do período
$sql = "SELECT COALESCE(SUM(valor), 0) as total
        FROM vales
        WHERE data BETWEEN ? AND ?";

if ($userTipo === 'profissional') {
    $sql .= " AND profissional_id = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$vales = (float)$stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$totalComissoes = (float)$totais['total_comissoes'];
$valorLiquido = $totalComissoes + $gorjetas - $vales;

successResponse([
    'periodo' => [
        'inicio' => $dataInicio,
        'fim' => $dataFim
    ],
    'total_servicos' => (int)$totais['total_servicos'],
    'total_faturado' => (float)$totais['total_faturado'],
    'total_comissoes' => $totalComissoes,
    'total_gorjetas' => $gorjetas,
    'total_vales' => $vales,
    'valor_liquido' => $valorLiquido
]);
