<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

require_once __DIR__ . '/../classes/Despesa.php';

try {
    $despesa = new Despesa();
    
    // Atualiza despesas vencidas
    $despesa->atualizarVencidas();
    
    // Busca boletos pendentes
    $boletos = $despesa->boletosPendentes();
    
    // Formata resposta
    $response = [
        'success' => true,
        'total' => count($boletos),
        'data' => [],
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    foreach ($boletos as $boleto) {
        $diasVencimento = (strtotime($boleto['data_vencimento']) - strtotime(date('Y-m-d'))) / (60 * 60 * 24);
        
        $response['data'][] = [
            'id' => $boleto['id'],
            'nome_conta' => $boleto['nome_conta'],
            'descricao' => $boleto['descricao'],
            'valor' => floatval($boleto['valor']),
            'valor_formatado' => 'R$ ' . number_format($boleto['valor'], 2, ',', '.'),
            'data_vencimento' => $boleto['data_vencimento'],
            'data_vencimento_formatada' => date('d/m/Y', strtotime($boleto['data_vencimento'])),
            'dias_para_vencimento' => intval($diasVencimento),
            'status' => $boleto['status'],
            'vencido' => $diasVencimento < 0,
            'debito_automatico' => boolval($boleto['debito_automatico']),
            'categoria' => $boleto['categoria'],
            'observacoes' => $boleto['observacoes']
        ];
    }
    
    // Ordenar por data de vencimento
    usort($response['data'], function($a, $b) {
        return strtotime($a['data_vencimento']) - strtotime($b['data_vencimento']);
    });
    
    echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
