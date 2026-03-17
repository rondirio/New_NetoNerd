<?php
session_start();
require_once 'classes/Despesa.php';
require_once 'classes/EmailService.php';

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: relatorio.php');
    exit;
}

try {
    $mes = $_POST['mes'];
    $ano = $_POST['ano'];
    $emailDestinatario = $_POST['email'];
    $nomeDestinatario = $_POST['nome'] ?? 'Usuário';
    
    // Buscar dados
    $despesa = new Despesa();
    $despesas = $despesa->listar(['mes' => $mes, 'ano' => $ano]);
    $estatisticas = $despesa->estatisticasMes($mes, $ano);
    
    $meses = [
        '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
        '04' => 'Abril', '05' => 'Maio', '06' => 'Junho',
        '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro',
        '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
    ];
    
    $periodo = $meses[$mes] . '/' . $ano;
    
    // Preparar relatório
    $relatorio = [
        'periodo' => $periodo,
        'estatisticas' => $estatisticas,
        'despesas' => $despesas
    ];
    
    // Enviar email
    $emailService = new EmailService();
    $emailService->enviarRelatorio($emailDestinatario, $nomeDestinatario, $relatorio);
    
    $_SESSION['mensagem'] = "Relatório enviado com sucesso para {$emailDestinatario}!";
    $_SESSION['tipo_mensagem'] = 'success';
    
} catch (Exception $e) {
    $_SESSION['mensagem'] = 'Erro ao enviar relatório: ' . $e->getMessage();
    $_SESSION['tipo_mensagem'] = 'error';
}

header('Location: relatorio.php?mes=' . $mes . '&ano=' . $ano);
exit;
