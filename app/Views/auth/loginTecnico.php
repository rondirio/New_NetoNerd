<?php
/**
 * NetoNerd - Login Técnico
 * Página de autenticação para técnicos
 * 
 * @package NetoNerd
 * @author NetoNerd Team
 * @version 2.0
 */

// Inicia sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Configurações da página
$pageTitle = 'Login Técnico - NetoNerd';
$pageDescription = 'Acesso exclusivo para técnicos';

// Processa mensagens de erro
$loginErro = '';
$loginTipo = '';
if (isset($_GET['login'])) {
    switch ($_GET['login']) {
        case 'erro':
            $loginErro = 'Matrícula ou senha inválidos';
            $loginTipo = 'danger';
            break;
        case 'erro2':
            $loginErro = 'Faça login para verificar os chamados de hoje';
            $loginTipo = 'warning';
            break;
        case 'logout':
            $loginErro = 'Logout realizado com sucesso!';
            $loginTipo = 'success';
            break;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= htmlspecialchars($pageDescription) ?>">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="public/assets/css/main.css">
    
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        /* Navbar */
        .navbar-custom {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 15px 0;
        }
        
        .navbar-brand .logo {
            height: 50px;
            width: auto;
        }
        
        .navbar-custom .nav-link {
            color: #667eea !important;
            font-weight: 500;
            padding: 8px 15px !important;
            margin: 0 5px;
            transition: all 0.3s ease;
            border-radius: 5px;
        }
        
        .navbar-custom .nav-link:hover {
            background: rgba(102, 126, 234, 0.1);
        }
        
        /* Main Container */
        .login-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 15px;
        }
        
        /* Welcome Message */
        .welcome-message {
            text-align: center;
            color: white;
            margin-bottom: 30px;
            animation: fadeInDown 0.6s ease-out;
        }
        
        .welcome-message h1 {
            font-size: 2.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            margin-bottom: 10px;
        }
        
        .welcome-message p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        /* Login Card */
        .login-card-wrapper {
            max-width: 450px;
            width: 100%;
            animation: fadeInUp 0.6s ease-out;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        
        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
            border: none;
        }
        
        .card-header-custom h4 {
            margin: 0;
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .card-header-custom .icon {
            font-size: 3rem;
            margin-bottom: 10px;
            opacity: 0.9;
        }
        
        .card-body-custom {
            padding: 40px;
        }
        
        /* Form Styles */
        .form-group label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }
        
        .form-control-custom {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control-custom:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }
        
        .input-group-icon {
            position: relative;
        }
        
        .input-group-icon i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            z-index: 10;
        }
        
        .input-group-icon .form-control-custom {
            padding-left: 45px;
        }
        
        .input-group-icon .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #adb5bd;
            transition: color 0.3s ease;
            z-index: 10;
        }
        
        .input-group-icon .toggle-password:hover {
            color: #667eea;
        }
        
        /* Button */
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        /* Alert */
        .alert-custom {
            border: none;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            animation: shake 0.5s;
        }
        
        /* Links */
        .login-links {
            text-align: center;
            margin-top: 25px;
            padding-top: 25px;
            border-top: 1px solid #e9ecef;
        }
        
        .login-links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .login-links a:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        /* Features */
        .features {
            margin-top: 30px;
            text-align: center;
        }
        
        .feature-item {
            display: inline-block;
            margin: 0 15px;
            color: white;
            font-size: 0.9rem;
        }
        
        .feature-item i {
            margin-right: 8px;
            font-size: 1.2rem;
        }
        
        /* Footer */
        .footer-custom {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            color: #495057;
            text-align: center;
            padding: 20px 0;
            box-shadow: 0 -4px 15px rgba(0,0,0,0.1);
        }
        
        .footer-custom p {
            margin: 0;
            font-size: 0.9rem;
        }
        
        /* Animations */
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-10px); }
            75% { transform: translateX(10px); }
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .welcome-message h1 {
                font-size: 2rem;
            }
            
            .welcome-message p {
                font-size: 1rem;
            }
            
            .card-body-custom {
                padding: 30px 25px;
            }
            
            .feature-item {
                display: block;
                margin: 10px 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <img class="logo" src="../../../imagens/logoNetoNerd.jpg" alt="Logo NetoNerd">
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ml-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-home"></i> Início
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../index.php#login">
                            <i class="fas fa-user"></i> Login Cliente
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    
    <!-- Main Container -->
    <div class="login-container">
        <div class="container">
            <!-- Welcome Message -->
            <div class="welcome-message">
                <h1><i class="fas fa-tools"></i> Área do Técnico</h1>
                <p>Vamos ver os chamados de hoje</p>
            </div>
            
            <!-- Login Card -->
            <div class="login-card-wrapper mx-auto">
                <div class="login-card">
                    <div class="card-header-custom">
                        <div class="icon">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <h4>Login Técnico</h4>
                        <p class="mb-0" style="font-size: 0.9rem; opacity: 0.9;">
                            Acesso exclusivo para profissionais
                        </p>
                    </div>
                    
                    <div class="card-body-custom">
                        <!-- Alert de Erro -->
                        <?php if (!empty($loginErro)): ?>
                        <div class="alert alert-<?= $loginTipo ?> alert-custom alert-dismissible fade show" role="alert">
                            <i class="fas fa-<?= $loginTipo === 'success' ? 'check-circle' : 'exclamation-triangle' ?> mr-2"></i>
                            <?= htmlspecialchars($loginErro) ?>
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Formulário -->
                        <form action="valida_loginTecnico.php" method="post" id="loginForm">
                            <div class="form-group">
                                <label for="matricula">
                                    <i class="fas fa-id-card mr-1"></i> Matrícula
                                </label>
                                <div class="input-group-icon">
                                    <i class="fas fa-user"></i>
                                    <input 
                                        type="text" 
                                        name="matricula" 
                                        id="matricula" 
                                        class="form-control form-control-custom" 
                                        placeholder="Digite sua matrícula"
                                        required 
                                        autofocus
                                    >
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="senha">
                                    <i class="fas fa-lock mr-1"></i> Senha
                                </label>
                                <div class="input-group-icon">
                                    <i class="fas fa-key"></i>
                                    <input 
                                        type="password" 
                                        name="senha" 
                                        id="senha" 
                                        class="form-control form-control-custom" 
                                        placeholder="Digite sua senha"
                                        required
                                    >
                                    <i class="fas fa-eye toggle-password" id="togglePassword"></i>
                                </div>
                            </div>
                            
                            <div class="custom-control custom-checkbox mb-3">
                                <input type="checkbox" class="custom-control-input" id="lembrar" name="lembrar">
                                <label class="custom-control-label" for="lembrar">
                                    Lembrar-me neste dispositivo
                                </label>
                            </div>
                            
                            <button type="submit" class="btn btn-login">
                                <i class="fas fa-sign-in-alt mr-2"></i> Entrar no Sistema
                            </button>
                            
                            <div class="login-links">
                                <a href="#" data-toggle="modal" data-target="#modalRecuperarSenha">
                                    <i class="fas fa-question-circle"></i> Esqueceu a senha?
                                </a>
                                <span class="mx-2">•</span>
                                <a href="contato.php">
                                    <i class="fas fa-headset"></i> Suporte
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Features -->
                <div class="features">
                    <div class="feature-item">
                        <i class="fas fa-shield-alt"></i> Acesso Seguro
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-clock"></i> Disponível dentro do seu horário de trabalho
                    </div>
                    <div class="feature-item">
                        <i class="fas fa-mobile-alt"></i> Mobile Friendly
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="footer-custom">
        <div class="container">
            <p>
                <strong>© 2025 NetoNerd</strong> - Todos os direitos reservados
            </p>
        </div>
    </footer>
    
    <!-- Modal Recuperar Senha -->
    <div class="modal fade" id="modalRecuperarSenha" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content" style="border-radius: 15px; overflow: hidden;">
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                    <h5 class="modal-title">
                        <i class="fas fa-key mr-2"></i> Recuperar Senha
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="padding: 30px;">
                    <p>Entre em contato com o administrador do sistema para recuperar sua senha.</p>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>WhatsApp:</strong> (21) 97739-5867
                    </div>
                    <a href="https://wa.me/5521977395867?text=Preciso%20recuperar%20minha%20senha%20de%20técnico" 
                       class="btn btn-success btn-block"
                       target="_blank">
                        <i class="fab fa-whatsapp mr-2"></i> Contatar via WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    
    <script>
        // Toggle Password Visibility
        const togglePassword = document.getElementById('togglePassword');
        const senhaInput = document.getElementById('senha');
        
        togglePassword.addEventListener('click', function() {
            const type = senhaInput.getAttribute('type') === 'password' ? 'text' : 'password';
            senhaInput.setAttribute('type', type);
            
            // Toggle icon
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
        
        // Validação do formulário
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const matricula = document.getElementById('matricula').value.trim();
            const senha = document.getElementById('senha').value;
            
            if (!matricula || !senha) {
                e.preventDefault();
                alert('Por favor, preencha todos os campos!');
                return false;
            }
            
            // Desabilita botão para evitar duplo submit
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Entrando...';
        });
        
        // Auto-dismiss de alertas
        setTimeout(function() {
            $('.alert').fadeOut('slow');
        }, 5000);
        
        // Focus no input ao carregar
        window.addEventListener('load', function() {
            document.getElementById('matricula').focus();
        });
        
        // Animação de entrada
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelector('.login-card-wrapper').style.opacity = '0';
            setTimeout(function() {
                document.querySelector('.login-card-wrapper').style.transition = 'opacity 0.6s ease-out';
                document.querySelector('.login-card-wrapper').style.opacity = '1';
            }, 200);
        });
    </script>
</body>
</html>