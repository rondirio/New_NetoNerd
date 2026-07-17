<?php

  require_once __DIR__ . '/auth_middleware.php';
  if(!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] != 'SIM'){
    header('Location: index.php?login=erro2');
    exit();
  }

?>