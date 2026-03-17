<?php
/**
 * StyleManager API - Dashboard Endpoint
 *
 * GET /api/v1/stylemanager/dashboard
 *
 * Retorna métricas do dashboard baseadas no tipo de usuário.
 *
 * Response:
 * {
 *   "success": true,
 *   "data": {
 *     "atendimentos_hoje": 5,
 *     "atendimentos_semana": 25,
 *     "atendimentos_mes": 100,
 *     "comissoes_hoje": 150.00,
 *     "comissoes_mes": 3500.00,
 *     "faturamento_hoje": 800.00,
 *     "faturamento_mes": 15000.00,
 *     "proximos_agendamentos": [...],
 *     "aniversariantes_hoje": [...]
 *   }
 * }
 */

require_once __DIR__ . '/config/api_helper.php';

// Apenas GET
requireMethod('GET');

// Requer autenticação
$auth = requireAuth();
$conn = $auth['db']->getConnection();
$userId = $auth['user_id'];
$userTipo = $auth['user_tipo'];

$hoje = date('Y-m-d');
$inicioSemana = date('Y-m-d', strtotime('monday this week'));
$inicioMes = date('Y-m-01');

$response = [];

// Filtro por profissional (se não for admin/recepcionista)
$profissionalFilter = '';
$profissionalId = null;

if ($userTipo === 'profissional') {
    $profissionalFilter = 'AND a.profissional_id = ?';
    $profissionalId = $userId;
} elseif ($userTipo === 'cliente') {
    // Cliente só vê seus próprios agendamentos
    $profissionalFilter = 'AND a.cliente_id = ?';
    $profissionalId = $userId;
}

// ===== ATENDIMENTOS HOJE =====
$sql = "SELECT COUNT(*) as total FROM agendamentos a
        WHERE a.data = ? AND a.status IN ('confirmado', 'em_atendimento', 'finalizado')
        $profissionalFilter";
$stmt = $conn->prepare($sql);

if ($profissionalId) {
    $stmt->bind_param('si', $hoje, $profissionalId);
} else {
    $stmt->bind_param('s', $hoje);
}

$stmt->execute();
$response['atendimentos_hoje'] = (int)$stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// ===== ATENDIMENTOS DA SEMANA =====
$sql = "SELECT COUNT(*) as total FROM agendamentos a
        WHERE a.data BETWEEN ? AND ? AND a.status IN ('confirmado', 'em_atendimento', 'finalizado')
        $profissionalFilter";
$stmt = $conn->prepare($sql);

if ($profissionalId) {
    $stmt->bind_param('ssi', $inicioSemana, $hoje, $profissionalId);
} else {
    $stmt->bind_param('ss', $inicioSemana, $hoje);
}

$stmt->execute();
$response['atendimentos_semana'] = (int)$stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// ===== ATENDIMENTOS DO MÊS =====
$sql = "SELECT COUNT(*) as total FROM agendamentos a
        WHERE a.data BETWEEN ? AND ? AND a.status IN ('confirmado', 'em_atendimento', 'finalizado')
        $profissionalFilter";
$stmt = $conn->prepare($sql);

if ($profissionalId) {
    $stmt->bind_param('ssi', $inicioMes, $hoje, $profissionalId);
} else {
    $stmt->bind_param('ss', $inicioMes, $hoje);
}

$stmt->execute();
$response['atendimentos_mes'] = (int)$stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

// ===== COMISSÕES (só para profissional e admin) =====
if (in_array($userTipo, ['profissional', 'admin'])) {
    // Comissões hoje
    $sql = "SELECT COALESCE(SUM(c.valor_comissao), 0) as total
            FROM comissoes c
            JOIN agendamentos a ON c.agendamento_id = a.id
            WHERE a.data = ? AND a.status = 'finalizado'";

    if ($userTipo === 'profissional') {
        $sql .= " AND c.profissional_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('si', $hoje, $userId);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $hoje);
    }

    $stmt->execute();
    $response['comissoes_hoje'] = (float)$stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // Comissões do mês
    $sql = "SELECT COALESCE(SUM(c.valor_comissao), 0) as total
            FROM comissoes c
            JOIN agendamentos a ON c.agendamento_id = a.id
            WHERE a.data BETWEEN ? AND ? AND a.status = 'finalizado'";

    if ($userTipo === 'profissional') {
        $sql .= " AND c.profissional_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssi', $inicioMes, $hoje, $userId);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $inicioMes, $hoje);
    }

    $stmt->execute();
    $response['comissoes_mes'] = (float)$stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
}

// ===== FATURAMENTO (só para admin e recepcionista) =====
if (in_array($userTipo, ['admin', 'recepcionista'])) {
    // Faturamento hoje
    $sql = "SELECT COALESCE(SUM(a.valor), 0) as total
            FROM agendamentos a
            WHERE a.data = ? AND a.status = 'finalizado'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $hoje);
    $stmt->execute();
    $response['faturamento_hoje'] = (float)$stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    // Faturamento do mês
    $sql = "SELECT COALESCE(SUM(a.valor), 0) as total
            FROM agendamentos a
            WHERE a.data BETWEEN ? AND ? AND a.status = 'finalizado'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $inicioMes, $hoje);
    $stmt->execute();
    $response['faturamento_mes'] = (float)$stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
}

// ===== PRÓXIMOS AGENDAMENTOS =====
$sql = "SELECT a.id, a.data, a.hora_inicio, a.hora_fim, a.valor, a.status, a.observacoes,
               c.id as cliente_id, c.nome as cliente_nome, c.telefone as cliente_telefone,
               s.id as servico_id, s.nome as servico_nome,
               p.id as profissional_id, p.nome as profissional_nome
        FROM agendamentos a
        LEFT JOIN usuarios c ON a.cliente_id = c.id
        LEFT JOIN servicos s ON a.servico_id = s.id
        LEFT JOIN usuarios p ON a.profissional_id = p.id
        WHERE a.data >= ? AND a.status IN ('agendado', 'confirmado', 'em_atendimento')
        $profissionalFilter
        ORDER BY a.data ASC, a.hora_inicio ASC
        LIMIT 10";

$stmt = $conn->prepare($sql);

if ($profissionalId) {
    $stmt->bind_param('si', $hoje, $profissionalId);
} else {
    $stmt->bind_param('s', $hoje);
}

$stmt->execute();
$result = $stmt->get_result();
$agendamentos = [];

while ($row = $result->fetch_assoc()) {
    $agendamentos[] = [
        'id' => (int)$row['id'],
        'data' => $row['data'],
        'hora_inicio' => $row['hora_inicio'],
        'hora_fim' => $row['hora_fim'],
        'valor' => (float)$row['valor'],
        'status' => $row['status'],
        'observacoes' => $row['observacoes'],
        'cliente' => $row['cliente_id'] ? [
            'id' => (int)$row['cliente_id'],
            'nome' => $row['cliente_nome'],
            'telefone' => $row['cliente_telefone']
        ] : null,
        'servico' => $row['servico_id'] ? [
            'id' => (int)$row['servico_id'],
            'nome' => $row['servico_nome']
        ] : null,
        'profissional' => $row['profissional_id'] ? [
            'id' => (int)$row['profissional_id'],
            'nome' => $row['profissional_nome']
        ] : null
    ];
}

$stmt->close();
$response['proximos_agendamentos'] = $agendamentos;

// ===== ANIVERSARIANTES DO DIA (só para admin e recepcionista) =====
if (in_array($userTipo, ['admin', 'recepcionista'])) {
    $diaAtual = date('m-d');
    $sql = "SELECT id, nome, telefone, email, data_nascimento
            FROM usuarios
            WHERE tipo = 'cliente' AND ativo = 1
            AND DATE_FORMAT(data_nascimento, '%m-%d') = ?
            ORDER BY nome ASC
            LIMIT 20";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $diaAtual);
    $stmt->execute();
    $result = $stmt->get_result();
    $response['aniversariantes_hoje'] = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

successResponse($response);
