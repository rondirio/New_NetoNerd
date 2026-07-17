<?php
/**
 * Script de Instalação Automática
 * Sistema de Gerenciamento de Despesas
 * 
 * Execute este arquivo apenas UMA VEZ após fazer o upload do sistema
 * Acesse: http://seu-dominio.com/despesas/install.php
 */

// Verificar se já foi instalado
if (file_exists('config/.installed')) {
    die('<h1 style="color: red;">⚠️ Sistema já foi instalado!</h1><p>Delete o arquivo <code>config/.installed</code> se deseja reinstalar.</p>');
}

$erros = [];
$sucesso = [];
$avisos = [];

// Verificar versão do PHP
if (version_compare(PHP_VERSION, '7.4', '<')) {
    $erros[] = "PHP 7.4+ é necessário. Versão atual: " . PHP_VERSION;
} else {
    $sucesso[] = "PHP " . PHP_VERSION . " ✓";
}

// Verificar extensões
$extensoes = ['pdo', 'pdo_mysql', 'mysqli', 'mbstring', 'json'];
foreach ($extensoes as $ext) {
    if (!extension_loaded($ext)) {
        $erros[] = "Extensão PHP '{$ext}' não está instalada";
    } else {
        $sucesso[] = "Extensão {$ext} ✓";
    }
}

// Verificar permissões de escrita
$diretorios = ['config', 'classes', 'assets', 'api'];
foreach ($diretorios as $dir) {
    if (!is_writable($dir)) {
        $avisos[] = "Diretório '{$dir}' pode não ter permissões adequadas (recomendado: 755)";
    } else {
        $sucesso[] = "Permissões do diretório {$dir} ✓";
    }
}

// Interface HTML
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
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        h2 {
            color: #34495e;
            margin: 30px 0 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        
        .status {
            padding: 15px;
            margin: 10px 0;
            border-radius: 8px;
            border-left: 4px solid;
        }
        
        .sucesso {
            background: #d4edda;
            color: #155724;
            border-color: #27ae60;
        }
        
        .erro {
            background: #f8d7da;
            color: #721c24;
            border-color: #e74c3c;
        }
        
        .aviso {
            background: #fff3cd;
            color: #856404;
            border-color: #f39c12;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 1em;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #3498db;
        }
        
        button {
            background: #27ae60;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            width: 100%;
            margin-top: 20px;
        }
        
        button:hover {
            background: #229954;
        }
        
        button:disabled {
            background: #95a5a6;
            cursor: not-allowed;
        }
        
        .step {
            margin: 30px 0;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        code {
            background: #ecf0f1;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: monospace;
        }
        
        ul {
            list-style: none;
            padding-left: 0;
        }
        
        li {
            padding: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Instalação do Sistema de Despesas</h1>
        <p style="color: #7f8c8d; margin-bottom: 30px;">Configure seu sistema em poucos passos</p>
        
        <h2>1. Verificação de Requisitos</h2>
        
        <?php if (count($sucesso) > 0): ?>
        <div class="status sucesso">
            <strong>✓ Verificações Bem-sucedidas:</strong>
            <ul>
                <?php foreach ($sucesso as $msg): ?>
                    <li>• <?php echo $msg; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php if (count($avisos) > 0): ?>
        <div class="status aviso">
            <strong>⚠️ Avisos:</strong>
            <ul>
                <?php foreach ($avisos as $msg): ?>
                    <li>• <?php echo $msg; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php if (count($erros) > 0): ?>
        <div class="status erro">
            <strong>✗ Erros Críticos:</strong>
            <ul>
                <?php foreach ($erros as $msg): ?>
                    <li>• <?php echo $msg; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
        
        <?php if (count($erros) === 0): ?>
        <h2>2. Configuração do Banco de Dados</h2>
        
        <form method="POST" action="processar_instalacao.php">
            <div class="form-group">
                <label for="db_host">Host do Banco de Dados</label>
                <input type="text" id="db_host" name="db_host" value="localhost" required>
            </div>
            
            <div class="form-group">
                <label for="db_name">Nome do Banco de Dados</label>
                <input type="text" id="db_name" name="db_name" value="despesas_db" required>
            </div>
            
            <div class="form-group">
                <label for="db_user">Usuário do Banco</label>
                <input type="text" id="db_user" name="db_user" value="root" required>
            </div>
            
            <div class="form-group">
                <label for="db_pass">Senha do Banco</label>
                <input type="password" id="db_pass" name="db_pass">
            </div>
            
            <h2>3. Configuração de Email (Opcional)</h2>
            <p style="color: #7f8c8d; margin-bottom: 15px;">Preencha apenas se quiser enviar relatórios por email</p>
            
            <div class="form-group">
                <label for="smtp_host">SMTP Host</label>
                <input type="text" id="smtp_host" name="smtp_host" value="smtp.gmail.com">
            </div>
            
            <div class="form-group">
                <label for="smtp_port">SMTP Port</label>
                <select id="smtp_port" name="smtp_port">
                    <option value="587">587 (TLS)</option>
                    <option value="465">465 (SSL)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="smtp_user">Email de Envio</label>
                <input type="email" id="smtp_user" name="smtp_user" placeholder="seu-email@gmail.com">
            </div>
            
            <div class="form-group">
                <label for="smtp_pass">Senha de App do Gmail</label>
                <input type="password" id="smtp_pass" name="smtp_pass">
                <small style="color: #7f8c8d;">
                    Para Gmail: <a href="https://myaccount.google.com/apppasswords" target="_blank">Gerar senha de app aqui</a>
                </small>
            </div>
            
            <button type="submit">🚀 Instalar Sistema</button>
        </form>
        
        <div class="step">
            <h3>📋 Após a Instalação</h3>
            <p>1. Delete ou renomeie o arquivo <code>install.php</code></p>
            <p>2. Acesse o sistema em <code>index.php</code></p>
            <p>3. Comece a adicionar suas despesas!</p>
        </div>
        
        <?php else: ?>
        <div class="status erro">
            <p><strong>Não é possível continuar com a instalação devido aos erros acima.</strong></p>
            <p>Por favor, corrija os problemas e recarregue esta página.</p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
