<?php
print_r($_POST);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - NetoNerd</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/main.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }
        
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg width="100" height="100" xmlns="http://www.w3.org/2000/svg"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>');
            background-size: 50px 50px;
            animation: float 20s linear infinite;
        }
        
        @keyframes float {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }
        
        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 450px;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            animation: slideUp 0.6s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login-header {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .login-logo {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            border: 4px solid white;
            margin-bottom: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
        }
        
        .login-header h2 {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .login-header p {
            margin: 10px 0 0;
            opacity: 0.95;
            font-size: 0.95rem;
        }
        
        .login-body {
            padding: 40px 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }
        
        .input-group {
            position: relative;
        }
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            z-index: 10;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px 12px 45px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 3px rgba(0,123,255,0.1);
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #999;
            cursor: pointer;
            z-index: 10;
            padding: 5px;
        }
        
        .toggle-password:hover {
            color: #007bff;
        }
        
        .form-check {
            margin-bottom: 20px;
        }
        
        .form-check-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 10px;
            background: linear-gradient(135deg, #007bff, #0056b3);
            border: none;
            color: white;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,123,255,0.3);
            color: white;
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        .divider {
            text-align: center;
            margin: 25px 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 50%;
            height: 1px;
            background: #e9ecef;
        }
        
        .divider span {
            background: white;
            padding: 0 15px;
            color: #999;
            font-size: 0.9rem;
            position: relative;
            z-index: 1;
        }
        
        .btn-cadastro {
            width: 100%;
            padding: 14px;
            font-size: 1rem;
            font-weight: 600;
            border-radius: 10px;
            background: white;
            border: 2px solid #007bff;
            color: #007bff;
            transition: all 0.3s ease;
        }
        
        .btn-cadastro:hover {
            background: #007bff;
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .alert-danger {
            background: #ffe5e5;
            color: #dc3545;
        }
        
        .alert-success {
            background: #d4edda;
            color: #28a745;
        }
        
        .back-home {
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 1000;
        }
        
        .btn-back {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255,255,255,0.3);
            color: white;
            padding: 10px 20px;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-back:hover {
            background: rgba(255,255,255,0.3);
            color: white;
            text-decoration: none;
        }
        
        .forgot-password {
            text-align: center;
            margin-top: 15px;
        }
        
        .forgot-password a {
            color: #007bff;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 600;
        }
        
        .forgot-password a:hover {
            text-decoration: underline;
        }
        
        .login-footer {
            background: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            border-top: 1px solid #e9ecef;
        }
        
        .login-footer p {
            margin: 0;
            color: #666;
            font-size: 0.85rem;
        }
        
        @media (max-width: 576px) {
            .login-container {
                max-width: 100%;
            }
            
            .login-body {
                padding: 30px 20px;
            }
            
            .back-home {
                position: static;
                margin-bottom: 20px;
                text-align: center;
            }
        }
    </style>
</head>
<body>


    <!-- Botão Voltar -->
    <div class="back-home">
        <a href="../publics/index.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Voltar ao Início
        </a>
    </div>

    <div class="login-container">
        <div class="login-card">
            <!-- Header -->
            <div class="login-header">
                <img src="../src/imagens/logoNetoNerd.jpg" alt="NetoNerd" class="login-logo">
                <h2>Bem-vindo de volta!</h2>
                <p>Faça login para acessar sua conta</p>
            </div>

            <!-- Body -->
            <div class="login-body">
                <!-- Mensagens de Erro/Sucesso -->
                <?php if(isset($_GET['login']) && $_GET['login'] == 'erro'): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <strong>Erro!</strong> Email ou senha incorretos.
                </div>
                <?php endif; ?>

                <?php if(isset($_GET['login']) && $_GET['login'] == 'erro2'): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-lock"></i>
                    <strong>Acesso negado!</strong> Faça login para acessar esta página.
                </div>
                <?php endif; ?>

                <?php if(isset($_GET['cadastro']) && $_GET['cadastro'] == 'sucesso'): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <strong>Sucesso!</strong> Cadastro realizado. Faça login para continuar.
                </div>
                <?php endif; ?>

                <?php if(isset($_GET['msg'])): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-info-circle"></i>
                    <?php 
                    $messages = [
                        'sessao_expirada' => 'Sua sessão expirou. Faça login novamente.',
                        'campos_vazios' => 'Por favor, preencha todos os campos.',
                        'bloqueado' => 'Conta temporariamente bloqueada por excesso de tentativas. Aguarde ' . ($_GET['tempo'] ?? '15') . ' minutos.'
                    ];
                    echo $messages[$_GET['msg']] ?? 'Erro desconhecido.';
                    ?>
                </div>
                <?php endif; ?>

                <!-- Formulário de Login -->
                <form action="../controller/valida_loginTecnico.php" method="POST" id="loginForm">
                    <div class="form-group">
                        <label for="email">
                            <i class="fas fa-registered"></i> Matrícula
                        </label>
                        <div class="input-group">
                            <span class="input-icon">
                                <i class="fas fa-registered"></i>
                            </span>
                            <input type="text" 
                                   class="form-control" 
                                   id="matricula" 
                                   name="matricula" 
                                   placeholder="NNF12345"
                                   required 
                                   
                                   value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="senha">
                            <i class="fas fa-lock"></i> Senha
                        </label>
                        <div class="input-group">
                            <span class="input-icon">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" 
                                   class="form-control" 
                                   id="senha" 
                                   name="senha" 
                                   placeholder="••••••••"
                                   required
                                   autocomplete="current-password">
                            <button type="button" class="toggle-password" onclick="togglePassword()">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="lembrar" name="lembrar">
                        <label class="form-check-label" for="lembrar">
                            Manter-me conectado
                        </label>
                    </div>

                    <button type="submit" class="btn btn-login">
                        <i class="fas fa-sign-in-alt"></i> Entrar
                    </button>
                </form>

                <div class="forgot-password">
                    <a href="recuperar_senha.php">
                        <i class="fas fa-key"></i> Esqueceu sua senha?
                    </a>
                </div>

                <!-- <div class="divider">
                    <span>OU</span>
                </div>

                <a href="cadastro.php" class="btn btn-cadastro">
                    <i class="fas fa-user-plus"></i> Criar Nova Conta
                </a> -->
            </div>

            <!-- Footer -->
            <!-- <div class="login-footer">
                <p>
                    <i class="fas fa-briefcase"></i> Acesso para técnicos? 
                    <a href="loginTecnico.php" style="color: #007bff; font-weight: 600;">Clique aqui</a>
                </p>
            </div> -->
        </div>
    </div>

    <!-- Scripts -->
    <script>
        // Toggle senha
        function togglePassword() {
            const senhaInput = document.getElementById('senha');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (senhaInput.type === 'password') {
                senhaInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                senhaInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Validação do formulário
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const senha = document.getElementById('senha').value;

            // if (!email || !senha) {
            //     e.preventDefault();
            //     alert('Por favor, preencha todos os campos.');
            //     return false;
            // }

            // Validação de email
            const matriculaRegex = /^(\d{4}[A-Z]\d{4}|[A-Z]{3}\d{6})$/;
            if (!matriculaRegex.test(email)) {
                e.preventDefault();
                alert('Por favor, insira uma matrícula válida (ex: 2026F1000 ou ADM202502).');
                return false;
            }

            if (senha.length < 6) {
                e.preventDefault();
                alert('A senha deve ter no mínimo 6 caracteres.');
                return false;
            }
        });

        // Fechar alertas automaticamente após 5 segundos
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        // Adiciona foco ao primeiro campo ao carregar
        window.addEventListener('load', () => {
            document.getElementById('email').focus();
        });
    </script>
</body>
</html>