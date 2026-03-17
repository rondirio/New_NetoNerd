<?php
/**
 * Mobile Includes - NetoNerd ITSM v2.0
 * Inclua este arquivo no <head> das páginas para adicionar suporte mobile
 *
 * Uso: <?php include_once "../routes/mobile_includes.php"; ?>
 * ou:  <?php include_once "routes/mobile_includes.php"; ?>
 */

// Detectar o caminho base para os assets
$mobile_base_path = '';
if (strpos($_SERVER['PHP_SELF'], '/publics/') !== false) {
    $mobile_base_path = '../';
} elseif (strpos($_SERVER['PHP_SELF'], '/cliente/') !== false) {
    $mobile_base_path = '../';
} elseif (strpos($_SERVER['PHP_SELF'], '/tecnico/') !== false) {
    $mobile_base_path = '../';
} elseif (strpos($_SERVER['PHP_SELF'], '/admin/') !== false) {
    $mobile_base_path = '../';
} elseif (strpos($_SERVER['PHP_SELF'], '/ordem_servico/') !== false) {
    $mobile_base_path = '../';
}
?>
<!-- Mobile Meta Tags -->
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="theme-color" content="#007bff">

<!-- Mobile Fixes CSS -->
<link rel="stylesheet" type="text/css" href="<?php echo $mobile_base_path; ?>src/css/mobile-fixes.css">

<!-- Mobile Enhancements JS (defer para não bloquear renderização) -->
<script defer src="<?php echo $mobile_base_path; ?>src/js/mobile-enhancements.js"></script>
