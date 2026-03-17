<?php
/**
 * Header Layout - NetoNerd
 * Template de cabeçalho reutilizável
 */

// Define título padrão se não definido
$pageTitle = $pageTitle ?? 'NetoNerd - Soluções em Tecnologia';
$pageDescription = $pageDescription ?? 'Suporte técnico especializado';

// Verifica se usuário está logado
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $_SESSION['user_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <meta name="author" content="NetoNerd">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="public/assets/css/main.css">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="public/assets/images/favicon.png">
    
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            padding-top: 76px; /* Altura do navbar */
        }
        
        /* Navbar Styles */
        .navbar-custom {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 15px 0;
        }
        
        .navbar-brand .logo {
            height: 50px;
            width: auto;
            transition: transform 0.3s ease;
        }
        
        .navbar-brand:hover .logo {
            transform: scale(1.05);
        }
        
        .navbar-custom .nav-link {
            color: rgba(255,255,255,0.9) !important;
            font-weight: 500;
            padding: 8px 15px !important;
            margin: 0 5px;
            transition: all 0.3s ease;
            border-radius: 5px;
        }
        
        .navbar-custom .nav-link:hover {
            color: white !important;
            background: rgba(255,255,255,0.1);
        }
        
        .navbar-custom .btn-nav {
            background: rgba(255,255,255,0.2);
            border: 2px solid rgba(255,255,255,0.3);
            color: white !important;
            font-weight: 600;
            padding: 8px 20px !important;
            margin: 0 5px;
            transition: all 0.3s ease;
        }
        
        .navbar-custom .btn-nav:hover {
            background: white;
            color: #007bff !important;
            border-color: white;
            transform: translateY(-2px);
        }
        
        .navbar-toggler {
            border-color: rgba(255,255,255,0.5);
        }
        
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' width='30' height='30' viewBox='0 0 30 30'%3e%3cpath stroke='rgba(255, 255, 255, 0.8)' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
        
        /* User Dropdown */
        .user-dropdown {
            background: rgba(255,255,255,0.15);
            padding: 8px 15px;
            border-radius: 25px;
        }
        
        .user-dropdown .dropdown-toggle {
            color: white !important;
            font-weight: 600;
        }
        
        .user-dropdown .dropdown-toggle::after {
            margin-left: 8px;
        }
        
        @media (max-width: 991px) {
            .navbar-custom .nav-link,
            .navbar-custom .btn-nav {
                margin: 5px 0;
            }
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-custom fixed-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <img class="logo" src="public/assets/images/logoNetoNerd.jpg" alt="Logo NetoNerd">
        </a>
        
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" 
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item">
                    <a class="nav-link" href="atendimento.php">
                        <i class="fas fa-headset"></i> Atendimento
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="planos.php">
                        <i class="fas fa-tags"></i> Planos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="contato.php">
                        <i class="fas fa-envelope"></i> Contato
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="quemsomo.php">
                        <i class="fas fa-info-circle"></i> Quem Somos
                    </a>
                </li>
                
                <?php if ($isLoggedIn): ?>
                    <!-- Usuário Logado -->
                    <li class="nav-item dropdown user-dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" 
                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-user-circle"></i> <?= htmlspecialchars($userName) ?>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="userDropdown">
                            <a class="dropdown-item" href="app/Views/dashboard/home.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                            <a class="dropdown-item" href="app/Views/chamados/criar.php">
                                <i class="fas fa-plus-circle"></i> Abrir Chamado
                            </a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="app/Controllers/AuthController.php?action=logout">
                                <i class="fas fa-sign-out-alt"></i> Sair
                            </a>
                        </div>
                    </li>
                <?php else: ?>
                    <!-- Usuário Não Logado -->
                    <li class="nav-item">
                        <a class="nav-link btn-nav" href="index.php#login">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-nav" href="../Views/auth/loginTecnico.php">
                            <i class="fas fa-user-cog"></i> Técnico
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn-nav" href="superadmin/index.php">
                            <i class="fas fa-crown"></i> Admin
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Content Start -->