# JWTMiddleware — exemplos de uso

Exemplos de integração de `JWTMiddleware.php` nos projetos que consomem a API JWT do super admin. Movido para cá (BE27 do plano de correção) — antes vivia como ~170 linhas de comentário dentro do próprio arquivo de implementação.

## EXEMPLO 1: MyHealth
Arquivo: `middleware/auth.php`
```php
<?php
require_once __DIR__ . '/JWTMiddleware.php';

use NetoNerd\Middleware\JWTMiddleware;

// Configurar middleware
$jwt = new JWTMiddleware(
    'https://admin.netonerd.com.br/api/jwt',  // URL do SuperAdmin
    'myhealth'                               // Nome do projeto
);

// Autenticar
$tokenData = $jwt->authenticate();

if (!$tokenData) {
    // Middleware já respondeu com 401, script para aqui
    exit;
}

// Prosseguir com a requisição
$tenantId = $jwt->getTenantId();
$empresa = $jwt->getEmpresa();
$plano = $jwt->getPlano();

// Verificar recursos do plano
if (!$jwt->hasFeature('relatorios_avancados')) {
    http_response_code(403);
    echo json_encode(['error' => 'Seu plano não tem acesso a relatórios avançados']);
    exit;
}

// Rate limiting (opcional)
$jwt->checkRateLimit(1000, 3600); // 1000 requests por hora

// Log de acesso
$jwt->logAccess('visualizou_pacientes');

// Continuar com lógica do endpoint...
```

## EXEMPLO 2: BarberShop Manager
Arquivo: `api/protected_endpoint.php`
```php
<?php
require_once __DIR__ . '/../middleware/JWTMiddleware.php';

use NetoNerd\Middleware\JWTMiddleware;

$jwt = new JWTMiddleware(
    'https://admin.netonerd.com.br/api/jwt',
    'barbershop'
);

$tokenData = $jwt->authenticate();

// Usar tenant_id para filtrar dados
$tenantId = $jwt->getTenantId();

// Query com isolamento de dados
$stmt = $db->prepare("
    SELECT * FROM agendamentos
    WHERE tenant_id = ?
    ORDER BY data_hora DESC
");
$stmt->bind_param('s', $tenantId);
$stmt->execute();
// ...
```

## EXEMPLO 3: New_NetoNerd (Suporte TI)
Arquivo: `tecnico/chamados.php`
```php
<?php
session_start();
require_once __DIR__ . '/../middleware/JWTMiddleware.php';

use NetoNerd\Middleware\JWTMiddleware;

// Configurar
$jwt = new JWTMiddleware(
    'http://localhost/Super_admin_NetoNerd/api/jwt',  // Desenvolvimento
    'suporte_ti'
);

// Autenticar
$tokenData = $jwt->authenticate();

// Salvar na sessão para uso posterior
$_SESSION['tenant_id'] = $jwt->getTenantId();
$_SESSION['empresa'] = $jwt->getEmpresa();
$_SESSION['plano'] = $jwt->getPlano();

// Verificar plano para recursos específicos
if ($jwt->getPlano() === 'enterprise') {
    // Mostrar recursos exclusivos
    echo "<div class='feature'>Suporte prioritário 24/7</div>";
}

// Log de acesso
$jwt->logAccess('acessou_painel_chamados', [
    'user_id' => $_SESSION['user_id'] ?? null
]);
```

## EXEMPLO 4: Rota de API Protegida (Padrão RESTful)
```php
<?php
// api/v1/pacientes/list.php (MyHealth)
require_once __DIR__ . '/../../middleware/JWTMiddleware.php';

use NetoNerd\Middleware\JWTMiddleware;

// Headers
header('Content-Type: application/json; charset=utf-8');

// Autenticar
$jwt = new JWTMiddleware(
    getenv('SUPERADMIN_API_URL'),  // Usar variável de ambiente
    'myhealth'
);

$tokenData = $jwt->authenticate();

// Rate limiting
$jwt->checkRateLimit(100, 60); // 100 requests por minuto

// Buscar dados
$tenantId = $jwt->getTenantId();
$stmt = $db->prepare("SELECT * FROM pacientes WHERE tenant_id = ?");
$stmt->bind_param('s', $tenantId);
$stmt->execute();
$result = $stmt->get_result();

$pacientes = [];
while ($row = $result->fetch_assoc()) {
    $pacientes[] = $row;
}

// Responder
echo json_encode([
    'success' => true,
    'tenant' => $jwt->getEmpresa(),
    'total' => count($pacientes),
    'data' => $pacientes
]);

// Log
$jwt->logAccess('listou_pacientes', ['total' => count($pacientes)]);
```
