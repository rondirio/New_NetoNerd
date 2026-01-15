# 📚 Documentação de Integração - Sistema de Licenciamento NetoNerd

## 🎯 Visão Geral

Este documento explica como integrar o sistema de licenciamento da NetoNerd em seus produtos:
- **MyHealth** - Prontuário Eletrônico
- **Escritorius** - Gestão para Escritórios
- **NetoNerd PJ** - Gestão Empresarial
- **StyleManager** - Gestão para Salões

## 🔑 Como Funciona

1. **Venda realizada** → NetoNerd gera uma API Key única
2. **Cliente recebe** a API Key por email
3. **Cliente ativa** o sistema inserindo a API Key
4. **Sistema valida** periodicamente a licença (diariamente)
5. **Pagamento atrasado** → Sistema bloqueia após 7 dias

---

## 📦 Arquivos para Integração

### 1. Classe de Validação de Licença (Copie para seu projeto)

Crie o arquivo `includes/LicenseValidator.php` no seu projeto:

```php
<?php
/**
 * Validador de Licença - NetoNerd
 * Use esta classe em MyHealth, Escritorius, NetoNerd PJ ou StyleManager
 */

class LicenseValidator {
    private $api_url = 'https://sistema.netonerd.com.br/api/validar_licenca.php';
    private $config_file;
    private $cache_file;

    public function __construct() {
        $this->config_file = __DIR__ . '/../config/license.json';
        $this->cache_file = __DIR__ . '/../cache/license_cache.json';

        // Criar diretórios se não existirem
        @mkdir(dirname($this->config_file), 0755, true);
        @mkdir(dirname($this->cache_file), 0755, true);
    }

    /**
     * Ativa a licença pela primeira vez
     */
    public function ativar($api_key) {
        $dados = [
            'api_key' => $api_key,
            'acao' => 'ativar',
            'url' => $this->getUrl(),
            'ip' => $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname()),
            'dominio' => $_SERVER['HTTP_HOST'],
            'versao' => $this->getVersaoProduto(),
            'os' => PHP_OS,
            'php_version' => PHP_VERSION
        ];

        $resposta = $this->fazerRequisicao($dados);

        if ($resposta && $resposta['sucesso']) {
            // Salvar configuração da licença
            $config = [
                'api_key' => $api_key,
                'ativada' => true,
                'data_ativacao' => date('Y-m-d H:i:s'),
                'usuario_admin' => $resposta['dados']['usuario_admin'] ?? null,
                'senha_temporaria' => $resposta['dados']['senha_temporaria'] ?? null
            ];

            file_put_contents($this->config_file, json_encode($config, JSON_PRETTY_PRINT));

            // Criar usuário admin se fornecido
            if (!empty($config['usuario_admin']) && !empty($config['senha_temporaria'])) {
                $this->criarUsuarioAdmin($config['usuario_admin'], $config['senha_temporaria']);
            }

            return [
                'sucesso' => true,
                'mensagem' => 'Licença ativada com sucesso!',
                'dados' => $resposta['dados']
            ];
        }

        return [
            'sucesso' => false,
            'mensagem' => $resposta['mensagem'] ?? 'Erro ao ativar licença'
        ];
    }

    /**
     * Valida a licença (verificar se está ativa)
     * Deve ser chamado diariamente ou a cada execução importante
     */
    public function validar() {
        // Verificar se está ativada
        if (!$this->estaAtivada()) {
            return [
                'valida' => false,
                'mensagem' => 'Sistema não ativado. Insira uma API Key válida.'
            ];
        }

        // Verificar cache (evitar requisições desnecessárias)
        if ($this->cacheValido()) {
            $cache = json_decode(file_get_contents($this->cache_file), true);
            return [
                'valida' => true,
                'mensagem' => 'Licença válida (cache)',
                'dados' => $cache['dados']
            ];
        }

        // Fazer validação online
        $config = json_decode(file_get_contents($this->config_file), true);

        $dados = [
            'api_key' => $config['api_key'],
            'acao' => 'validar',
            'url' => $this->getUrl()
        ];

        $resposta = $this->fazerRequisicao($dados);

        if ($resposta && $resposta['valida']) {
            // Atualizar cache
            $cache = [
                'timestamp' => time(),
                'dados' => $resposta['dados']
            ];
            file_put_contents($this->cache_file, json_encode($cache));

            return [
                'valida' => true,
                'mensagem' => 'Licença válida',
                'dados' => $resposta['dados']
            ];
        }

        // Licença inválida ou expirada
        return [
            'valida' => false,
            'mensagem' => $resposta['mensagem'] ?? 'Licença inválida'
        ];
    }

    /**
     * Verifica se a licença está ativada
     */
    public function estaAtivada() {
        if (!file_exists($this->config_file)) {
            return false;
        }

        $config = json_decode(file_get_contents($this->config_file), true);
        return !empty($config['ativada']) && !empty($config['api_key']);
    }

    /**
     * Obtém informações da licença
     */
    public function obterInfo() {
        if (!$this->estaAtivada()) {
            return null;
        }

        return json_decode(file_get_contents($this->config_file), true);
    }

    // Métodos privados

    private function fazerRequisicao($dados) {
        $ch = curl_init($this->api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dados));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: NetoNerd-License-Client/1.0'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        $resposta = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_code === 200 || $http_code === 403) {
            return json_decode($resposta, true);
        }

        return null;
    }

    private function cacheValido() {
        if (!file_exists($this->cache_file)) {
            return false;
        }

        $cache = json_decode(file_get_contents($this->cache_file), true);
        $tempo_cache = 24 * 60 * 60; // 24 horas

        return (time() - $cache['timestamp']) < $tempo_cache;
    }

    private function getUrl() {
        $protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'];
        return $protocolo . '://' . $host;
    }

    private function getVersaoProduto() {
        // Cada produto deve ter sua própria versão
        if (defined('APP_VERSION')) {
            return APP_VERSION;
        }
        return '1.0.0';
    }

    private function criarUsuarioAdmin($usuario, $senha) {
        // IMPORTANTE: Implemente a criação do usuário admin no SEU banco de dados
        // Este é apenas um exemplo genérico

        /*
        $conn = getConnection(); // Sua função de conexão
        $senha_hash = password_hash($senha, PASSWORD_BCRYPT);

        $stmt = $conn->prepare("
            INSERT INTO usuarios (username, senha_hash, tipo, nome)
            VALUES (?, ?, 'admin', 'Administrador')
            ON DUPLICATE KEY UPDATE senha_hash = ?
        ");
        $stmt->bind_param("sss", $usuario, $senha_hash, $senha_hash);
        $stmt->execute();
        $stmt->close();
        */
    }
}
?>
```

---

## 🚀 Passo a Passo de Integração

### 1. Copie os Arquivos

Copie a classe `LicenseValidator.php` para o diretório `includes/` do seu projeto.

### 2. Crie a Página de Ativação

Crie `ativar_licenca.php` na raiz do seu projeto:

```php
<?php
require_once 'includes/LicenseValidator.php';

$validator = new LicenseValidator();

// Se já está ativado, redirecionar
if ($validator->estaAtivada()) {
    header('Location: index.php');
    exit;
}

$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $api_key = trim($_POST['api_key']);

    if (strlen($api_key) !== 64) {
        $erro = 'API Key inválida. Deve ter 64 caracteres.';
    } else {
        $resultado = $validator->ativar($api_key);

        if ($resultado['sucesso']) {
            $sucesso = $resultado['mensagem'];
            // Mostrar credenciais de admin se fornecidas
            if (!empty($resultado['dados']['usuario_admin'])) {
                $sucesso .= '<br><br><strong>Credenciais de Administrador:</strong><br>';
                $sucesso .= 'Usuário: ' . $resultado['dados']['usuario_admin'] . '<br>';
                $sucesso .= 'Senha: ' . $resultado['dados']['senha_temporaria'] . '<br>';
                $sucesso .= '<small>IMPORTANTE: Altere a senha após o primeiro login!</small>';
            }
            header('refresh:5;url=index.php');
        } else {
            $erro = $resultado['mensagem'];
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Ativar Licença - NetoNerd</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
        }
        h1 { color: #667eea; text-align: center; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: monospace;
        }
        button {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover { background: #5568d3; }
        .erro { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .sucesso { background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔑 Ativar Licença</h1>

        <?php if ($erro): ?>
            <div class="erro"><?php echo $erro; ?></div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="sucesso"><?php echo $sucesso; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>API Key:</label>
                <input type="text" name="api_key" placeholder="Cole aqui a API Key fornecida pela NetoNerd" required maxlength="64" autofocus>
                <small>Você recebeu esta chave por email após a compra</small>
            </div>
            <button type="submit">Ativar Sistema</button>
        </form>
    </div>
</body>
</html>
```

### 3. Proteja Seu Sistema

Adicione a validação no arquivo principal (ex: `index.php`):

```php
<?php
session_start();
require_once 'includes/LicenseValidator.php';

$validator = new LicenseValidator();

// Redirecionar para ativação se não estiver ativado
if (!$validator->estaAtivada()) {
    header('Location: ativar_licenca.php');
    exit;
}

// Validar licença (cache de 24h)
$resultado = $validator->validar();

if (!$resultado['valida']) {
    // Sistema bloqueado
    die('
    <html>
    <body style="font-family: Arial; text-align: center; padding: 50px;">
        <h1 style="color: #dc3545;">🚫 Sistema Bloqueado</h1>
        <p>' . htmlspecialchars($resultado['mensagem']) . '</p>
        <p>Entre em contato com a NetoNerd:</p>
        <p>📧 suporte@netonerd.com.br | 📱 (21) 97739-5867</p>
    </body>
    </html>
    ');
}

// Sistema válido, continuar normalmente...
?>
```

---

## 🔄 Fluxo Completo

```
┌─────────────────┐
│ Cliente compra  │
│  na NetoNerd    │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ NetoNerd gera   │
│    API Key      │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Cliente recebe  │
│  email com key  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Cliente acessa  │
│ ativar_licenca  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Sistema valida  │
│   com API       │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│ Cria user admin │
│  Trial 30 dias  │
└────────┬────────┘
         │
         ▼
┌─────────────────┐
│Sistema funciona │
│  normalmente    │
└────────┬────────┘
         │
         ▼ (diariamente)
┌─────────────────┐
│ Valida licença  │
│   (cache 24h)   │
└────────┬────────┘
         │
         ├─ Válida → Continua
         │
         └─ Inválida → Bloqueia
```

---

## 📋 Checklist de Integração

- [ ] Copiou `LicenseValidator.php` para seu projeto
- [ ] Criou página `ativar_licenca.php`
- [ ] Adicionou validação no `index.php` ou arquivo principal
- [ ] Implementou a função `criarUsuarioAdmin()` para seu banco
- [ ] Testou ativação com API Key de teste
- [ ] Testou bloqueio (suspendendo licença manualmente)
- [ ] Criou diretórios `cache/` e `config/` com permissões corretas
- [ ] Adicionou `config/license.json` e `cache/` no `.gitignore`

---

## 🆘 Suporte

Dúvidas na integração? Entre em contato:

- 📧 **Email:** suporte@netonerd.com.br
- 📱 **WhatsApp:** (21) 97739-5867
- 🌐 **Site:** www.netonerd.com.br

---

**© 2026 NetoNerd Soluções Digitais LTDA**
