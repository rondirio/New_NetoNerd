<?php
session_start();
require_once 'classes/Auth.php';
require_once 'classes/Despesa.php';
require_once 'classes/EmailService.php';

$auth = new Auth();
$auth->protegerPagina();
$usuarioId = $auth->getUsuarioId();

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: relatorio.php');
    exit;
}

try {
    $mes = str_pad(max(1, min(12, intval($_POST['mes'] ?? date('m')))), 2, '0', STR_PAD_LEFT);
    $ano = max(2000, min(2100, intval($_POST['ano'] ?? date('Y'))));
    $emailDestinatario = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    if (!$emailDestinatario) {
        throw new Exception("E-mail destinatário inválido.");
    }
    $nomeDestinatario = htmlspecialchars(trim($_POST['nome'] ?? 'Usuário'), ENT_QUOTES, 'UTF-8');

    // Buscar dados filtrados pelo usuário autenticado
    $despesa = new Despesa();
    $despesas    = $despesa->listar(['usuario_id' => $usuarioId, 'mes' => $mes, 'ano' => $ano]);
    $estatisticas = $despesa->estatisticasMes($mes, $ano, $usuarioId);
    
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
    error_log("enviar_relatorio.php - " . $e->getMessage());
    $_SESSION['mensagem'] = 'Erro ao enviar relatório. Tente novamente ou contate o suporte.';
    $_SESSION['tipo_mensagem'] = 'error';
}

header('Location: relatorio.php?mes=' . $mes . '&ano=' . $ano);
exit;
