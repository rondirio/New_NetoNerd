<?php
session_start();
require_once 'classes/Auth.php';

// Se já está logado, redirecionar
$auth = new Auth();
if ($auth->verificarAutenticacao()) {
    header('Location: despesas.php');
    exit;
}

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = $_POST['email'];
        $senha = $_POST['senha'];
        $lembrar = isset($_POST['lembrar']);
        
        $usuario = $auth->login($email, $senha, $lembrar);
        
        header('Location: despesas.php');
        exit;
        
    } catch (Exception $e) {
        $erro = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Despesas</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
            display: flex;
            animation: slideUp 0.5s ease-out;
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
        
        .login-left {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 60px 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        
        .login-left h1 {
            font-size: 2.5em;
            margin-bottom: 20px;
        }
        
        .login-left p {
            font-size: 1.1em;
            opacity: 0.9;
            line-height: 1.6;
        }
        
        .login-left .features {
            margin-top: 40px;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            font-size: 1em;
        }
        
        .feature-item span {
            font-size: 1.5em;
            margin-right: 15px;
        }
        
        .login-right {
            flex: 1;
            padding: 60px 40px;
        }
        
        .login-right h2 {
            color: #2c3e50;
            margin-bottom: 10px;
            font-size: 2em;
        }
        
        .login-right p {
            color: #7f8c8d;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #ecf0f1;
            border-radius: 10px;
            font-size: 1em;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 25px;
        }
        
        .checkbox-group input {
            width: auto;
            margin-right: 10px;
        }
        
        .checkbox-group label {
            color: #7f8c8d;
            font-weight: normal;
        }
        
        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }
        
        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #e74c3c;
        }
        
        .links {
            text-align: center;
            margin-top: 20px;
            color: #7f8c8d;
        }
        
        .links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
        
        /* Mobile Responsivo */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }
            
            .login-left {
                padding: 40px 30px;
            }
            
            .login-left h1 {
                font-size: 2em;
            }
            
            .login-left .features {
                margin-top: 20px;
            }
            
            .feature-item {
                margin-bottom: 15px;
                font-size: 0.95em;
            }
            
            .login-right {
                padding: 40px 30px;
            }
            
            .login-right h2 {
                font-size: 1.6em;
            }
        }
        
        @media (max-width: 480px) {
            body {
                padding: 10px;
            }
            
            .login-container {
                border-radius: 15px;
            }
            
            .login-left {
                padding: 30px 20px;
            }
            
            .login-left h1 {
                font-size: 1.6em;
            }
            
            .login-right {
                padding: 30px 20px;
            }
            
            .login-right h2 {
                font-size: 1.4em;
            }
            
            .form-group input {
                padding: 12px;
                font-size: 0.95em;
            }
            
            .btn {
                padding: 12px;
                font-size: 1em;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-left">
            <h1>💰 Gerenciador de Despesas</h1>
            <p>Controle suas finanças de forma simples e eficiente</p>
            
            <div class="features">
                <div class="feature-item">
                    <span>✓</span>
                    <span>Organize suas contas mensais</span>
                </div>
                <div class="feature-item">
                    <span>🔁</span>
                    <span>Despesas recorrentes automáticas</span>
                </div>
                <div class="feature-item">
                    <span>📊</span>
                    <span>Relatórios detalhados</span>
                </div>
                <div class="feature-item">
                    <span>📧</span>
                    <span>Envio de relatórios por email</span>
                </div>
                <div class="feature-item">
                    <span>🔒</span>
                    <span>Seus dados 100% seguros</span>
                </div>
            </div>
        </div>
        
        <div class="login-right">
            <h2>Bem-vindo de volta!</h2>
            <p>Entre com sua conta para continuar</p>
            
            <?php if (isset($erro)): ?>
                <div class="alert">
                    <?php echo htmlspecialchars($erro); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           required 
                           placeholder="seu@email.com"
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="senha">Senha</label>
                    <input type="password" 
                           id="senha" 
                           name="senha" 
                           required 
                           placeholder="••••••••">
                </div>
                
                <div class="checkbox-group">
                    <input type="checkbox" id="lembrar" name="lembrar">
                    <label for="lembrar">Lembrar de mim</label>
                </div>
                
                <button type="submit" class="btn">Entrar</button>
            </form>
            
            <div class="links">
                <p>Não tem uma conta? <a href="registro.php">Cadastre-se gratuitamente</a></p>
            </div>
        </div>
    </div>
</body>
</html>
