<?php
/**
 * StyleManager API - Clientes Endpoint
 *
 * GET  /api/v1/stylemanager/clientes - Lista clientes
 * GET  /api/v1/stylemanager/clientes/{id} - Obtém cliente
 * POST /api/v1/stylemanager/clientes - Cria cliente
 */

require_once __DIR__ . '/../config/api_helper.php';

// Requer autenticação de staff (admin, recepcionista ou profissional)
$auth = requireStaff();
$conn = $auth['db']->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
$id = getPathId();

switch ($method) {
    case 'GET':
        if ($id) {
            getCliente($conn, $id);
        } else {
            listClientes($conn);
        }
        break;

    case 'POST':
        createCliente($conn);
        break;

    default:
        errorResponse('Método não permitido', 405);
}

/**
 * Lista clientes com busca
 */
function listClientes(mysqli $conn): void {
    $search = getQueryParam('search');
    $page = max(1, (int)getQueryParam('page', 1));
    $limit = min(100, max(1, (int)getQueryParam('limit', 20)));

    $where = ["tipo = 'cliente'", "ativo = 1"];
    $params = [];
    $types = '';

    if ($search) {
        $searchTerm = "%$search%";
        $where[] = "(nome LIKE ? OR telefone LIKE ? OR email LIKE ?)";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= 'sss';
    }

    $whereClause = implode(' AND ', $where);

    $sql = "SELECT id, nome, email, telefone, data_nascimento,
                   (SELECT COUNT(*) FROM agendamentos WHERE cliente_id = usuarios.id) as total_atendimentos,
                   (SELECT MAX(data) FROM agendamentos WHERE cliente_id = usuarios.id AND status = 'finalizado') as ultimo_atendimento
            FROM usuarios
            WHERE $whereClause
            ORDER BY nome ASC";

    $result = paginate($conn, $sql, $params, $types, $page, $limit);

    successResponse($result);
}

/**
 * Obtém um cliente específico
 */
function getCliente(mysqli $conn, int $id): void {
    $stmt = $conn->prepare("
        SELECT u.id, u.nome, u.email, u.telefone, u.data_nascimento, u.observacoes, u.foto,
               (SELECT COUNT(*) FROM agendamentos WHERE cliente_id = u.id) as total_atendimentos,
               (SELECT MAX(data) FROM agendamentos WHERE cliente_id = u.id AND status = 'finalizado') as ultimo_atendimento
        FROM usuarios u
        WHERE u.id = ? AND u.tipo = 'cliente'
    ");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $cliente = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$cliente) {
        errorResponse('Cliente não encontrado', 404);
    }

    $cliente['id'] = (int)$cliente['id'];
    $cliente['total_atendimentos'] = (int)$cliente['total_atendimentos'];

    successResponse($cliente);
}

/**
 * Cria um novo cliente
 */
function createCliente(mysqli $conn): void {
    $input = getJsonInput();

    $errors = validateRequired($input, ['nome']);
    if ($errors) {
        errorResponse('Dados inválidos', 400, $errors);
    }

    $nome = sanitize($input['nome']);
    $email = isset($input['email']) ? strtolower(trim($input['email'])) : null;
    $telefone = $input['telefone'] ?? null;
    $dataNascimento = $input['data_nascimento'] ?? null;
    $observacoes = $input['observacoes'] ?? null;

    // Valida email se fornecido
    if ($email && !validateEmail($email)) {
        errorResponse('Email inválido', 400);
    }

    // Verifica se email já existe
    if ($email) {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->fetch_assoc()) {
            errorResponse('Email já cadastrado', 409);
        }
        $stmt->close();
    }

    // Insere o cliente
    $stmt = $conn->prepare("
        INSERT INTO usuarios (nome, email, telefone, data_nascimento, observacoes, tipo, ativo, created_at)
        VALUES (?, ?, ?, ?, ?, 'cliente', 1, NOW())
    ");
    $stmt->bind_param('sssss', $nome, $email, $telefone, $dataNascimento, $observacoes);

    if (!$stmt->execute()) {
        errorResponse('Erro ao criar cliente', 500);
    }

    $clienteId = $stmt->insert_id;
    $stmt->close();

    getCliente($conn, $clienteId);
}
