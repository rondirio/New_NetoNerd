<?php
/**
 * StyleManager API - Cadastro Rápido de Cliente
 *
 * POST /api/v1/stylemanager/clientes/rapido
 *
 * Cadastro simplificado apenas com nome e telefone.
 *
 * Request:
 * {
 *   "nome": "João Silva",
 *   "telefone": "(21) 99999-9999"
 * }
 */

require_once __DIR__ . '/../config/api_helper.php';

// Apenas POST
requireMethod('POST');

// Requer staff
$auth = requireStaff();
$conn = $auth['db']->getConnection();

$input = getJsonInput();

$errors = validateRequired($input, ['nome', 'telefone']);
if ($errors) {
    errorResponse('Nome e telefone são obrigatórios', 400, $errors);
}

$nome = sanitize($input['nome']);
$telefone = preg_replace('/[^0-9]/', '', $input['telefone']);

// Verifica se já existe cliente com este telefone
$stmt = $conn->prepare("SELECT id, nome FROM usuarios WHERE telefone LIKE ? AND tipo = 'cliente' LIMIT 1");
$telefoneBusca = "%$telefone%";
$stmt->bind_param('s', $telefoneBusca);
$stmt->execute();
$existente = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($existente) {
    // Retorna o cliente existente
    successResponse([
        'id' => (int)$existente['id'],
        'nome' => $existente['nome'],
        'telefone' => $input['telefone'],
        'existente' => true
    ], 'Cliente já cadastrado');
}

// Cria novo cliente
$stmt = $conn->prepare("
    INSERT INTO usuarios (nome, telefone, tipo, ativo, created_at)
    VALUES (?, ?, 'cliente', 1, NOW())
");
$stmt->bind_param('ss', $nome, $input['telefone']);

if (!$stmt->execute()) {
    errorResponse('Erro ao criar cliente', 500);
}

$clienteId = $stmt->insert_id;
$stmt->close();

successResponse([
    'id' => $clienteId,
    'nome' => $nome,
    'telefone' => $input['telefone'],
    'existente' => false
], 'Cliente criado com sucesso', 201);
