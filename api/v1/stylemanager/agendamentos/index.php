<?php
/**
 * StyleManager API - Agendamentos Endpoint
 *
 * GET    /api/v1/stylemanager/agendamentos - Lista agendamentos
 * POST   /api/v1/stylemanager/agendamentos - Cria agendamento
 * GET    /api/v1/stylemanager/agendamentos/{id} - Obtém agendamento
 * PUT    /api/v1/stylemanager/agendamentos/{id} - Atualiza agendamento
 * DELETE /api/v1/stylemanager/agendamentos/{id} - Cancela agendamento
 */

require_once __DIR__ . '/../config/api_helper.php';

// Requer autenticação
$auth = requireAuth();
$conn = $auth['db']->getConnection();
$userId = $auth['user_id'];
$userTipo = $auth['user_tipo'];

$method = $_SERVER['REQUEST_METHOD'];
$id = getPathId();

switch ($method) {
    case 'GET':
        if ($id) {
            getAgendamento($conn, $id, $userId, $userTipo);
        } else {
            listAgendamentos($conn, $userId, $userTipo);
        }
        break;

    case 'POST':
        createAgendamento($conn, $auth);
        break;

    case 'PUT':
        if (!$id) {
            errorResponse('ID do agendamento é obrigatório', 400);
        }
        updateAgendamento($conn, $id, $userId, $userTipo);
        break;

    case 'DELETE':
        if (!$id) {
            errorResponse('ID do agendamento é obrigatório', 400);
        }
        cancelAgendamento($conn, $id, $userId, $userTipo);
        break;

    default:
        errorResponse('Método não permitido', 405);
}

/**
 * Lista agendamentos com filtros
 */
function listAgendamentos(mysqli $conn, int $userId, string $userTipo): void {
    $data = getQueryParam('data');
    $dataInicio = getQueryParam('data_inicio');
    $dataFim = getQueryParam('data_fim');
    $status = getQueryParam('status');
    $profissionalId = getQueryParam('profissional_id');
    $clienteId = getQueryParam('cliente_id');
    $page = max(1, (int)getQueryParam('page', 1));
    $limit = min(100, max(1, (int)getQueryParam('limit', 20)));

    $where = ['1=1'];
    $params = [];
    $types = '';

    // Filtro por data
    if ($data) {
        $where[] = 'a.data = ?';
        $params[] = $data;
        $types .= 's';
    } elseif ($dataInicio && $dataFim) {
        $where[] = 'a.data BETWEEN ? AND ?';
        $params[] = $dataInicio;
        $params[] = $dataFim;
        $types .= 'ss';
    }

    // Filtro por status
    if ($status) {
        $where[] = 'a.status = ?';
        $params[] = $status;
        $types .= 's';
    }

    // Filtro por profissional
    if ($profissionalId) {
        $where[] = 'a.profissional_id = ?';
        $params[] = (int)$profissionalId;
        $types .= 'i';
    }

    // Filtro por cliente
    if ($clienteId) {
        $where[] = 'a.cliente_id = ?';
        $params[] = (int)$clienteId;
        $types .= 'i';
    }

    // Restrição por tipo de usuário
    if ($userTipo === 'profissional') {
        $where[] = 'a.profissional_id = ?';
        $params[] = $userId;
        $types .= 'i';
    } elseif ($userTipo === 'cliente') {
        $where[] = 'a.cliente_id = ?';
        $params[] = $userId;
        $types .= 'i';
    }

    $whereClause = implode(' AND ', $where);

    // Query base
    $sql = "SELECT a.id, a.data, a.hora_inicio, a.hora_fim, a.valor, a.status, a.observacoes,
                   a.cliente_id, a.profissional_id, a.servico_id,
                   c.nome as cliente_nome, c.telefone as cliente_telefone,
                   s.nome as servico_nome, s.duracao as servico_duracao,
                   p.nome as profissional_nome
            FROM agendamentos a
            LEFT JOIN usuarios c ON a.cliente_id = c.id
            LEFT JOIN servicos s ON a.servico_id = s.id
            LEFT JOIN usuarios p ON a.profissional_id = p.id
            WHERE $whereClause
            ORDER BY a.data DESC, a.hora_inicio DESC";

    $result = paginate($conn, $sql, $params, $types, $page, $limit);

    // Formata resultados
    $agendamentos = array_map(function($row) {
        return [
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
                'nome' => $row['servico_nome'],
                'duracao' => (int)$row['servico_duracao']
            ] : null,
            'profissional' => $row['profissional_id'] ? [
                'id' => (int)$row['profissional_id'],
                'nome' => $row['profissional_nome']
            ] : null
        ];
    }, $result['data']);

    successResponse([
        'data' => $agendamentos,
        'pagination' => $result['pagination']
    ]);
}

/**
 * Obtém um agendamento específico
 */
function getAgendamento(mysqli $conn, int $id, int $userId, string $userTipo): void {
    $sql = "SELECT a.*, c.nome as cliente_nome, c.telefone as cliente_telefone, c.email as cliente_email,
                   s.nome as servico_nome, s.duracao as servico_duracao, s.preco as servico_preco,
                   p.nome as profissional_nome
            FROM agendamentos a
            LEFT JOIN usuarios c ON a.cliente_id = c.id
            LEFT JOIN servicos s ON a.servico_id = s.id
            LEFT JOIN usuarios p ON a.profissional_id = p.id
            WHERE a.id = ?";

    // Restrição por tipo de usuário
    if ($userTipo === 'profissional') {
        $sql .= " AND a.profissional_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $id, $userId);
    } elseif ($userTipo === 'cliente') {
        $sql .= " AND a.cliente_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $id, $userId);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row) {
        errorResponse('Agendamento não encontrado', 404);
    }

    $agendamento = [
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
            'telefone' => $row['cliente_telefone'],
            'email' => $row['cliente_email']
        ] : null,
        'servico' => $row['servico_id'] ? [
            'id' => (int)$row['servico_id'],
            'nome' => $row['servico_nome'],
            'duracao' => (int)$row['servico_duracao'],
            'preco' => (float)$row['servico_preco']
        ] : null,
        'profissional' => $row['profissional_id'] ? [
            'id' => (int)$row['profissional_id'],
            'nome' => $row['profissional_nome']
        ] : null,
        'created_at' => $row['created_at'] ?? null,
        'updated_at' => $row['updated_at'] ?? null
    ];

    successResponse($agendamento);
}

/**
 * Cria um novo agendamento
 */
function createAgendamento(mysqli $conn, array $auth): void {
    $input = getJsonInput();

    // Valida campos obrigatórios
    $errors = validateRequired($input, ['data', 'hora_inicio', 'servico_id']);
    if ($errors) {
        errorResponse('Dados inválidos', 400, $errors);
    }

    // Valida data
    if (!validateDate($input['data'])) {
        errorResponse('Data inválida', 400);
    }

    // Valida hora
    if (!validateTime($input['hora_inicio'])) {
        errorResponse('Hora inválida', 400);
    }

    // Busca informações do serviço
    $stmt = $conn->prepare("SELECT id, nome, duracao, preco FROM servicos WHERE id = ? AND ativo = 1");
    $stmt->bind_param('i', $input['servico_id']);
    $stmt->execute();
    $servico = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$servico) {
        errorResponse('Serviço não encontrado', 400);
    }

    // Calcula hora fim
    $horaInicio = new DateTime($input['hora_inicio']);
    $horaFim = clone $horaInicio;
    $horaFim->add(new DateInterval('PT' . $servico['duracao'] . 'M'));

    // Valores
    $data = $input['data'];
    $horaInicioStr = $horaInicio->format('H:i:s');
    $horaFimStr = $horaFim->format('H:i:s');
    $valor = isset($input['valor']) ? (float)$input['valor'] : (float)$servico['preco'];
    $clienteId = $input['cliente_id'] ?? null;
    $profissionalId = $input['profissional_id'] ?? null;
    $observacoes = $input['observacoes'] ?? null;
    $status = 'agendado';

    // Se for cliente criando, usa o próprio ID
    if ($auth['user_tipo'] === 'cliente') {
        $clienteId = $auth['user_id'];
    }

    // Verifica conflito de horário
    $sql = "SELECT id FROM agendamentos
            WHERE data = ? AND profissional_id = ?
            AND status NOT IN ('cancelado', 'nao_compareceu')
            AND (
                (hora_inicio < ? AND hora_fim > ?) OR
                (hora_inicio < ? AND hora_fim > ?) OR
                (hora_inicio >= ? AND hora_fim <= ?)
            )";

    if ($profissionalId) {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sissssss', $data, $profissionalId,
            $horaFimStr, $horaInicioStr,
            $horaFimStr, $horaInicioStr,
            $horaInicioStr, $horaFimStr);
        $stmt->execute();
        $conflito = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($conflito) {
            errorResponse('Horário não disponível para este profissional', 409);
        }
    }

    // Insere o agendamento
    $stmt = $conn->prepare("
        INSERT INTO agendamentos (data, hora_inicio, hora_fim, cliente_id, profissional_id, servico_id, valor, status, observacoes, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param('sssiiiiss', $data, $horaInicioStr, $horaFimStr, $clienteId, $profissionalId, $input['servico_id'], $valor, $status, $observacoes);

    if (!$stmt->execute()) {
        errorResponse('Erro ao criar agendamento', 500);
    }

    $agendamentoId = $stmt->insert_id;
    $stmt->close();

    // Retorna o agendamento criado
    getAgendamento($conn, $agendamentoId, $auth['user_id'], $auth['user_tipo']);
}

/**
 * Atualiza um agendamento
 */
function updateAgendamento(mysqli $conn, int $id, int $userId, string $userTipo): void {
    $input = getJsonInput();

    // Verifica se o agendamento existe e se o usuário tem permissão
    $sql = "SELECT id, status FROM agendamentos WHERE id = ?";

    if ($userTipo === 'profissional') {
        $sql .= " AND profissional_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $id, $userId);
    } elseif ($userTipo === 'cliente') {
        $sql .= " AND cliente_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $id, $userId);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
    }

    $stmt->execute();
    $agendamento = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$agendamento) {
        errorResponse('Agendamento não encontrado', 404);
    }

    // Monta query de atualização
    $updates = [];
    $params = [];
    $types = '';

    // Campos atualizáveis
    $allowedFields = ['data', 'hora_inicio', 'hora_fim', 'cliente_id', 'profissional_id', 'servico_id', 'valor', 'status', 'observacoes'];

    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updates[] = "$field = ?";

            if (in_array($field, ['cliente_id', 'profissional_id', 'servico_id'])) {
                $params[] = (int)$input[$field];
                $types .= 'i';
            } elseif ($field === 'valor') {
                $params[] = (float)$input[$field];
                $types .= 'd';
            } else {
                $params[] = $input[$field];
                $types .= 's';
            }
        }
    }

    if (empty($updates)) {
        errorResponse('Nenhum campo para atualizar', 400);
    }

    $updates[] = "updated_at = NOW()";

    $sql = "UPDATE agendamentos SET " . implode(', ', $updates) . " WHERE id = ?";
    $params[] = $id;
    $types .= 'i';

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if (!$stmt->execute()) {
        errorResponse('Erro ao atualizar agendamento', 500);
    }

    $stmt->close();

    // Retorna o agendamento atualizado
    getAgendamento($conn, $id, $userId, $userTipo);
}

/**
 * Cancela um agendamento
 */
function cancelAgendamento(mysqli $conn, int $id, int $userId, string $userTipo): void {
    // Verifica se o agendamento existe e se o usuário tem permissão
    $sql = "SELECT id, status FROM agendamentos WHERE id = ?";

    if ($userTipo === 'cliente') {
        $sql .= " AND cliente_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $id, $userId);
    } elseif ($userTipo === 'profissional') {
        $sql .= " AND profissional_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $id, $userId);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
    }

    $stmt->execute();
    $agendamento = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$agendamento) {
        errorResponse('Agendamento não encontrado', 404);
    }

    if ($agendamento['status'] === 'cancelado') {
        errorResponse('Agendamento já está cancelado', 400);
    }

    if ($agendamento['status'] === 'finalizado') {
        errorResponse('Não é possível cancelar um agendamento finalizado', 400);
    }

    // Cancela
    $stmt = $conn->prepare("UPDATE agendamentos SET status = 'cancelado', updated_at = NOW() WHERE id = ?");
    $stmt->bind_param('i', $id);

    if (!$stmt->execute()) {
        errorResponse('Erro ao cancelar agendamento', 500);
    }

    $stmt->close();

    successResponse(null, 'Agendamento cancelado com sucesso');
}
