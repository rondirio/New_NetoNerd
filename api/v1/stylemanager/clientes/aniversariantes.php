<?php
/**
 * StyleManager API - Aniversariantes do Dia
 *
 * GET /api/v1/stylemanager/clientes/aniversariantes
 */

require_once __DIR__ . '/../config/api_helper.php';

requireMethod('GET');

$auth = requireStaff();
$conn = $auth['db']->getConnection();

$diaAtual = date('m-d');

$stmt = $conn->prepare("
    SELECT id, nome, telefone, email, data_nascimento
    FROM usuarios
    WHERE tipo = 'cliente' AND ativo = 1
    AND DATE_FORMAT(data_nascimento, '%m-%d') = ?
    ORDER BY nome ASC
");
$stmt->bind_param('s', $diaAtual);
$stmt->execute();
$result = $stmt->get_result();
$aniversariantes = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

successResponse($aniversariantes);
