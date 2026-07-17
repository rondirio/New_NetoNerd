<?php
// Configurações do Banco de Dados — lidas do .env, nunca hardcoded aqui
require_once __DIR__ . '/../../config/config.php';

define('DB_HOST', Config::get('DESPESAS_DB_HOST', 'localhost'));
define('DB_NAME', Config::get('DESPESAS_DB_NAME', ''));
define('DB_USER', Config::get('DESPESAS_DB_USER', ''));
define('DB_PASS', Config::get('DESPESAS_DB_PASS', ''));
define('DB_CHARSET', 'utf8mb4');
