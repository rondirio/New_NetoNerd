<?php
/**
 * Cron Job - Gerador de Despesas Recorrentes
 * 
 * Este script deve ser executado automaticamente no dia 1º de cada mês
 * 
 * Configuração do Cron (Linux):
 * Execute: crontab -e
 * Adicione a linha:
 * 0 1 1 * * /usr/bin/php /caminho/para/despesas/cron_gerar_recorrentes.php
 * 
 * Isso executará o script todo dia 1º às 01:00
 */

// Definir que é execução CLI
if (php_sapi_name() !== 'cli' && !defined('ALLOW_WEB_CRON')) {
    die('Este script deve ser executado via linha de comando ou com permissão especial.');
}

// Incluir classes necessárias
require_once __DIR__ . '/classes/Despesa.php';

// Criar arquivo de log
$logFile = __DIR__ . '/logs/cron_recorrentes.log';
$logDir = dirname($logFile);

if (!file_exists($logDir)) {
    mkdir($logDir, 0755, true);
}

function logMessage($message) {
    global $logFile;
    $timestamp = date('Y-m-d H:i:s');
    $logMessage = "[{$timestamp}] {$message}\n";
    file_put_contents($logFile, $logMessage, FILE_APPEND);
    echo $logMessage;
}

try {
    logMessage("========================================");
    logMessage("Iniciando geração de despesas recorrentes");
    
    $despesa = new Despesa();
    
    // Verificar se há despesas recorrentes
    $totalRecorrentes = $despesa->contarRecorrentes();
    logMessage("Total de despesas recorrentes cadastradas: {$totalRecorrentes}");
    
    if ($totalRecorrentes == 0) {
        logMessage("Nenhuma despesa recorrente encontrada. Finalizando...");
        exit(0);
    }
    
    // Gerar despesas
    $criadas = $despesa->gerarRecorrentes();
    
    if ($criadas > 0) {
        logMessage("✓ SUCESSO: {$criadas} despesa(s) gerada(s) para o próximo mês!");
    } else {
        logMessage("ℹ️ INFO: Nenhuma despesa foi gerada (podem já existir para o próximo mês)");
    }
    
    // Também atualizar despesas vencidas
    $despesa->atualizarVencidas();
    logMessage("Status de despesas vencidas atualizado");
    
    logMessage("Processo finalizado com sucesso");
    logMessage("========================================\n");
    
    exit(0);
    
} catch (Exception $e) {
    logMessage("✗ ERRO: " . $e->getMessage());
    logMessage("========================================\n");
    exit(1);
}
