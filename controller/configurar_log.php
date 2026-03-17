<?php
// Configura o PHP para registrar erros
ini_set('log_errors', 1);

// Define o local do arquivo de log
ini_set('error_log', dirname(__FILE__) . '/erros.log');

// Reporta todos os erros (avisos, erros fatais, etc)
error_reporting(E_ALL);

// Impede que os erros apareçam na tela para o usuário
ini_set('display_errors', 1);
?>