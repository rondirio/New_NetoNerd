<?php
require_once __DIR__ . '/../controller/auth_middleware.php';

// Destrói a sessão com redundância (dados + cookie + checagem final de vestígios)
destruirSessaoComRedundancia();

// Redirect to login page
header("Location: ../publics/login.php");
exit;
?>