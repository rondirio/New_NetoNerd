<?php
/**
 * StyleManager API - Configurações do Estabelecimento
 *
 * GET /api/v1/stylemanager/configuracoes
 */

require_once __DIR__ . '/config/api_helper.php';

requireMethod('GET');

$auth = requireAuth();
$conn = $auth['db']->getConnection();
$estabelecimento = $auth['estabelecimento'];

// Busca configurações do banco
$configuracoes = [
    'estabelecimento' => $estabelecimento,
    'hora_abertura' => '08:00',
    'hora_fechamento' => '20:00',
    'intervalo_agendamento' => 30,
    'dias_funcionamento' => [1, 2, 3, 4, 5, 6], // Seg a Sáb
    'permite_agendamento_online' => true,
    'antecedencia_minima' => 30, // minutos
    'antecedencia_maxima' => 30, // dias
];

// Tenta buscar do banco
$stmt = $conn->prepare("SELECT chave, valor FROM configuracoes");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $chave = $row['chave'];
        $valor = $row['valor'];

        // Converte valores numéricos
        if (is_numeric($valor)) {
            $valor = strpos($valor, '.') !== false ? (float)$valor : (int)$valor;
        } elseif ($valor === 'true' || $valor === 'false') {
            $valor = $valor === 'true';
        } elseif (strpos($valor, '[') === 0) {
            $valor = json_decode($valor, true);
        }

        $configuracoes[$chave] = $valor;
    }

    $stmt->close();
}

successResponse($configuracoes);
