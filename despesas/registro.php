<?php
session_start();
require_once 'classes/Auth.php';

$auth = new Auth();
if ($auth->verificarAutenticacao()) {
    header('Location: despesas.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nome = $_POST['nome'];
        $email = $_POST['email'];
        $senha = $_POST['senha'];
        $confirmar = $_POST['confirmar_senha'];
        
        if ($senha !== $confirmar) {
            throw new Exception("As senhas não coincidem.");
        }
        
        $auth->registrar($nome, $email, $senha);
        $auth->login($email, $senha);
        
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
    <title>Cadastro - Sistema de Despesas</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 15px; }
        .register-box { background: white; border-radius: 20px; padding: 40px; max-width: 500px; width: 100%; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
        .register-box h1 { color: #2c3e50; margin-bottom: 10px; }
        .register-box p { color: #7f8c8d; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; color: #2c3e50; font-weight: 600; }
        .form-group input { width: 100%; padding: 12px; border: 2px solid #ecf0f1; border-radius: 10px; font-size: 1em; }
        .form-group input:focus { outline: none; border-color: #667eea; }
        .btn { width: 100%; padding: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; border-radius: 10px; font-size: 1.1em; font-weight: 600; cursor: pointer; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(102,126,234,0.4); }
        .links { text-align: center; margin-top: 20px; }
        .links a { color: #667eea; text-decoration: none; font-weight: 600; }
        .alert { padding: 15px; background: #f8d7da; color: #721c24; border-radius: 10px; margin-bottom: 20px; }
        @media (max-width: 480px) { .register-box { padding: 30px 20px; } }
    </style>
</head>
<body>
    <div class="register-box">
        <h1>💰 Criar Conta</h1>
        <p>Comece a organizar suas finanças hoje</p>
        
        <?php if (isset($erro)): ?>
            <div class="alert"><?php echo htmlspecialchars($erro); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Nome Completo</label>
                <input type="text" name="nome" required placeholder="Seu nome">
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="seu@email.com">
            </div>
            <div class="form-group">
                <label>Senha</label>
                <input type="password" name="senha" required placeholder="Mínimo 6 caracteres">
            </div>
            <div class="form-group">
                <label>Confirmar Senha</label>
                <input type="password" name="confirmar_senha" required placeholder="Digite novamente">
            </div>
            <button type="submit" class="btn">Criar Conta Gratuita</button>
        </form>
        
        <div class="links">
            <p>Já tem uma conta? <a href="index.php">Entrar</a></p>
        </div>
    </div>
</body>
</html>
