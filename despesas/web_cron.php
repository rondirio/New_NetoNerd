<?php
/**
 * Web Cron - Gerador de Despesas Recorrentes
 * 
 * Para quem não tem acesso ao cron do servidor
 * 
 * Acesse: http://seu-site.com/despesas/web_cron.php?senha=SUA_SENHA_SECRETA
 * 
 * Configure a senha em config/web_cron.php
 */

// Verificar senha
$senhaCorreta = 'mudar-esta-senha-123'; // MUDE ESTA SENHA!

if (!isset($_GET['senha']) || $_GET['senha'] !== $senhaCorreta) {
    http_response_code(403);
    die('Acesso negado. Senha incorreta.');
}

// Permitir execução web
define('ALLOW_WEB_CRON', true);

// Incluir o script principal
require_once __DIR__ . '/cron_gerar_recorrentes.php';
