<?php
/**
 * Processador de Instalação
 * Sistema de Gerenciamento de Despesas
 */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: install.php');
    exit;
}

$erros = [];

// Receber dados do formulário
$db_host = $_POST['db_host'] ?? 'localhost';
$db_name = $_POST['db_name'] ?? 'despesas_db';
$db_user = $_POST['db_user'] ?? 'root';
$db_pass = $_POST['db_pass'] ?? '';

$smtp_host = $_POST['smtp_host'] ?? 'smtp.gmail.com';
$smtp_port = $_POST['smtp_port'] ?? '587';
$smtp_user = $_POST['smtp_user'] ?? '';
$smtp_pass = $_POST['smtp_pass'] ?? '';

// Passo 1: Testar conexão com banco
try {
    $conn = new PDO("mysql:host=$db_host", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Criar banco de dados
    $conn->exec("CREATE DATABASE IF NOT EXISTS $db_name CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $conn->exec("USE $db_name");
    
    // Executar SQL de criação de tabelas
    $sql = file_get_contents('database.sql');
    
    // Dividir por comandos (separados por ;)
    $comandos = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($comandos as $comando) {
        if (!empty($comando) && !stripos($comando, 'CREATE DATABASE')) {
            try {
                $conn->exec($comando);
            } catch (PDOException $e) {
                // Ignorar erros de tabelas que já existem
                if (strpos($e->getMessage(), 'already exists') === false) {
                    throw $e;
                }
            }
        }
    }
    
    $conn = null;
    
} catch (PDOException $e) {
    $erros[] = "Erro no banco de dados: " . $e->getMessage();
}

// Passo 2: Criar arquivo de configuração do banco
if (empty($erros)) {
    $config_db = "<?php
// Configurações do Banco de Dados
define('DB_HOST', '$db_host');
define('DB_NAME', '$db_name');
define('DB_USER', '$db_user');
define('DB_PASS', '$db_pass');
define('DB_CHARSET', 'utf8mb4');
";
    
    if (!file_put_contents('config/database.php', $config_db)) {
        $erros[] = "Não foi possível criar o arquivo config/database.php";
    }
}

// Passo 3: Criar arquivo de configuração de email
if (empty($erros)) {
    $smtp_secure = $smtp_port == '465' ? 'ssl' : 'tls';
    
    $config_email = "<?php
// Configurações do PHPMailer
define('SMTP_HOST', '$smtp_host');
define('SMTP_PORT', $smtp_port);
define('SMTP_SECURE', '$smtp_secure');
define('SMTP_USERNAME', '$smtp_user');
define('SMTP_PASSWORD', '$smtp_pass');
define('EMAIL_FROM', '$smtp_user');
define('EMAIL_FROM_NAME', 'Sistema de Despesas');
";
    
    if (!file_put_contents('config/email.php', $config_email)) {
        $erros[] = "Não foi possível criar o arquivo config/email.php";
    }
}

// Passo 4: Marcar como instalado
if (empty($erros)) {
    file_put_contents('config/.installed', date('Y-m-d H:i:s'));
}

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - Sistema de Despesas</title>
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
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .container {
            max-width: 600px;
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            text-align: center;
        }
        
        .sucesso {
            color: #27ae60;
            font-size: 4em;
            margin-bottom: 20px;
        }
        
        .erro {
            color: #e74c3c;
            font-size: 4em;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 20px;
        }
        
        p {
            color: #7f8c8d;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .status {
            padding: 15px;
            margin: 20px 0;
            border-radius: 8px;
            text-align: left;
        }
        
        .status.erro {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #e74c3c;
            font-size: 1em;
        }
        
        .btn {
            display: inline-block;
            background: #3498db;
            color: white;
            padding: 15px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            margin-top: 20px;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #2980b9;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background: #27ae60;
        }
        
        .btn-success:hover {
            background: #229954;
        }
        
        ul {
            list-style: none;
            padding: 0;
        }
        
        li {
            padding: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (empty($erros)): ?>
            <div class="sucesso">✓</div>
            <h1>Instalação Concluída!</h1>
            <p>Seu sistema foi instalado com sucesso e está pronto para uso.</p>
            
            <div style="background: #d4edda; padding: 20px; border-radius: 8px; margin: 30px 0; text-align: left;">
                <strong style="color: #155724;">📋 Próximos Passos:</strong>
                <ul style="color: #155724; margin-top: 10px;">
                    <li>1. Delete ou renomeie o arquivo <code>install.php</code></li>
                    <li>2. Delete o arquivo <code>processar_instalacao.php</code></li>
                    <li>3. Comece a usar o sistema!</li>
                </ul>
            </div>
            
            <a href="despesas.php" class="btn btn-success">🚀 Acessar o Sistema</a>
            
        <?php else: ?>
            <div class="erro">✗</div>
            <h1>Erro na Instalação</h1>
            <p>Ocorreram erros durante a instalação:</p>
            
            <div class="status erro">
                <ul>
                    <?php foreach ($erros as $erro): ?>
                        <li>• <?php echo htmlspecialchars($erro); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            
            <a href="install.php" class="btn">← Voltar e Tentar Novamente</a>
        <?php endif; ?>
    </div>
</body>
</html>
