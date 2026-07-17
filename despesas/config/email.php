<?php
// Configurações do PHPMailer — lidas do .env (mesmas credenciais do core), nunca hardcoded aqui
require_once __DIR__ . '/../../config/config.php';

define('SMTP_HOST', Config::get('MAIL_HOST', 'smtp.gmail.com'));
define('SMTP_PORT', (int) Config::get('MAIL_PORT', 587));
define('SMTP_SECURE', Config::get('MAIL_ENCRYPTION', 'tls'));
define('SMTP_USERNAME', Config::get('MAIL_USERNAME', ''));
define('SMTP_PASSWORD', Config::get('MAIL_PASSWORD', ''));
define('EMAIL_FROM', Config::get('MAIL_FROM_EMAIL', ''));
define('EMAIL_FROM_NAME', 'Sistema de Despesas');
