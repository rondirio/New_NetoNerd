<?php
/**
 * Configuração do Banco de Dados
 * * @author NetoNerd Development Team
 * @version 1.0.0
 */

// Credenciais lidas do .env, nunca hardcoded aqui
require_once __DIR__ . '/../../../config/config.php';

define('DB_HOST', Config::get('SUPERADMIN_API_DB_HOST', 'localhost'));
define('DB_USER', Config::get('SUPERADMIN_API_DB_USERNAME', ''));
define('DB_PASS', Config::get('SUPERADMIN_API_DB_PASSWORD', ''));
define('DB_NAME', Config::get('SUPERADMIN_API_DB_NAME', 'super_admin_netonerd'));