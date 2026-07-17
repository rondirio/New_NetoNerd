<?php
/**
 * Middleware de Autenticação - NetoNerd ITSM
 * Protege rotas e valida permissões de acesso
 *
 * Uso:
 * require_once '../controller/auth_middleware.php';
 * requireAuth(); // Qualquer usuário autenticado
 * requireAdmin(); // Apenas administradores
 * requireTecnico(); // Apenas técnicos ou admins
 * requireCliente(); // Apenas clientes
 */

require_once __DIR__ . '/../config/config.php';

if (session_status() === PHP_SESSION_NONE) {
    configurarCookieSessaoSegura();
    session_start();
}

/**
 * Configura os parâmetros do cookie de sessão (HttpOnly/Secure/SameSite)
 * antes de session_start() — sem isso, o fluxo principal dependia
 * inteiramente da configuração padrão do php.ini de produção, que pode
 * não ter esses atributos habilitados. Idempotente: chamar mais de uma
 * vez ou depois que a sessão já iniciou não tem efeito (PHP ignora).
 *
 * Nome de cookie próprio (não o "PHPSESSID" padrão do PHP): em produção
 * cada produto do hub já roda em domínio/subdomínio próprio, então isso
 * não tem efeito de segurança ali — mas em ambiente local, onde vários
 * projetos PHP do hub compartilham o mesmo host (localhost) e o mesmo
 * session.save_path global do XAMPP, usar o nome padrão faz dois projetos
 * lerem o mesmo arquivo de sessão em disco (confirmado: sessão de teste
 * do NetoNerd "vazando" para o StyleManager local). Um nome de cookie
 * próprio evita essa colisão sem exigir nada dos outros projetos do hub.
 */
function configurarCookieSessaoSegura() {
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }
    session_name('NETONERD_SESSID');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => Config::get('SESSION_SECURE', 'false') === 'true',
        'httponly' => Config::get('SESSION_HTTPONLY', 'true') !== 'false',
        'samesite' => 'Lax',
    ]);
}

/**
 * Extrai o path base do projeto a partir de APP_URL (ex: "http://netonerd.com.br/public_html"
 * -> "/public_html"; "http://localhost/NetoNerd/New_NetoNerd-main" -> "/NetoNerd/New_NetoNerd-main").
 * Evita hardcode de path que muda entre ambiente local e produção.
 * @return string
 */
function basePath() {
    $appUrl = Config::get('APP_URL', '');
    $path = parse_url($appUrl, PHP_URL_PATH) ?? '';
    return rtrim($path, '/');
}

/**
 * Verifica se o usuário está autenticado
 * @return bool
 */
function isAuthenticated() {
    return isset($_SESSION['autenticado']) && $_SESSION['autenticado'] === 'SIM';
}

/**
 * Verifica se o usuário é administrador
 * @return bool
 */
function isAdmin() {
    return isAuthenticated() &&
           isset($_SESSION['tipo']) &&
           $_SESSION['tipo'] === 'admin';
}

/**
 * Verifica se o usuário é técnico (ou admin)
 * @return bool
 */
function isTecnico() {
    return isAuthenticated() &&
           isset($_SESSION['tipo']) &&
           ($_SESSION['tipo'] === 'tecnico' || $_SESSION['tipo'] === 'admin');
}

/**
 * Verifica se o usuário é cliente
 * @return bool
 */
function isCliente() {
    return isAuthenticated() &&
           isset($_SESSION['tipo']) &&
           $_SESSION['tipo'] === 'cliente';
}

/**
 * Obtém o tipo de usuário atual
 * @return string|null
 */
function getUserType() {
    return $_SESSION['tipo'] ?? null;
}

/**
 * Obtém o ID do usuário atual
 * @return int|null
 */
function getUserId() {
    return $_SESSION['id'] ?? $_SESSION['usuario_id'] ?? null;
}

/**
 * Obtém o nome do usuário atual
 * @return string|null
 */
function getUserName() {
    return $_SESSION['nome'] ?? $_SESSION['usuario_nome'] ?? null;
}

/**
 * Retorna o token CSRF da sessão atual, gerando um novo se ainda não existir.
 * Usar em todo formulário POST autenticado: <?php echo csrfField(); ?>
 * @return string
 */
function generateCsrfToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Renderiza o campo hidden pronto para uso dentro de um <form method="POST">.
 * @return string
 */
function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(generateCsrfToken()) . '">';
}

/**
 * Valida o token CSRF enviado (POST ou GET) contra o token da sessão.
 * Comparação com hash_equals() (resistente a timing attack). Não expira nem
 * regenera o token no sucesso — o mesmo token da sessão serve para múltiplos
 * envios enquanto a sessão durar, como já era o comportamento assumido pelo
 * código que gerava o token no login mas nunca o validava.
 * @return bool
 */
function isValidCsrfToken() {
    $enviado = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
    return !empty($_SESSION['csrf_token']) && !empty($enviado) && hash_equals($_SESSION['csrf_token'], $enviado);
}

/**
 * Requer token CSRF válido na requisição atual. Interrompe com 403 se ausente
 * ou inválido — chamar logo após requireAuth()/requireAdmin()/etc., antes de
 * qualquer leitura de outros campos do POST.
 */
function requireCsrfToken() {
    if (!isValidCsrfToken()) {
        error_log("CSRF inválido/ausente - Usuário: " . getUserName() . " (ID: " . getUserId() . ") | URI: " . $_SERVER['REQUEST_URI'] . " | IP: " . $_SERVER['REMOTE_ADDR']);
        http_response_code(403);
        die('Requisição inválida ou expirada. Volte à página anterior e tente novamente.');
    }
}

/**
 * Requer autenticação (qualquer tipo de usuário)
 * Redireciona para login se não autenticado
 */
function requireAuth() {
    if (!isAuthenticated()) {
        // Salvar URL de retorno
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];

        // Redirecionar para login apropriado
        header('Location: ' . basePath() . '/publics/login.php?erro=nao_autenticado');
        exit();
    }

    // Atualizar última atividade
    $_SESSION['last_activity'] = time();
}

/**
 * Requer acesso de ADMINISTRADOR
 * Bloqueia técnicos e clientes
 */
function requireAdmin() {
    requireAuth();

    if (!isAdmin()) {
        // Registrar tentativa de acesso não autorizado
        error_log("Tentativa de acesso admin negada - Usuário: " . getUserName() . " (ID: " . getUserId() . ") | Tipo: " . getUserType() . " | IP: " . $_SERVER['REMOTE_ADDR']);

        // Redirecionar baseado no tipo de usuário
        $tipo = getUserType();
        switch ($tipo) {
            case 'tecnico':
                header('Location: ' . basePath() . '/tecnico/paineltecnico.php?erro=acesso_negado');
                break;
            case 'cliente':
                header('Location: ' . basePath() . '/cliente/home.php?erro=acesso_negado');
                break;
            default:
                header('Location: ' . basePath() . '/publics/login.php?erro=acesso_negado');
        }
        exit();
    }
}

/**
 * Requer acesso de TÉCNICO ou ADMIN
 * Bloqueia apenas clientes
 */
function requireTecnico() {
    requireAuth();

    if (!isTecnico()) {
        error_log("Tentativa de acesso técnico negada - Usuário: " . getUserName() . " (ID: " . getUserId() . ") | Tipo: " . getUserType());

        if (isCliente()) {
            header('Location: ' . basePath() . '/cliente/home.php?erro=acesso_negado');
        } else {
            header('Location: ' . basePath() . '/publics/login.php?erro=acesso_negado');
        }
        exit();
    }
}

/**
 * Requer acesso de CLIENTE
 * Bloqueia técnicos e admins
 */
function requireCliente() {
    requireAuth();

    if (!isCliente()) {
        error_log("Tentativa de acesso cliente negada - Usuário: " . getUserName() . " (ID: " . getUserId() . ") | Tipo: " . getUserType());

        $tipo = getUserType();
        if ($tipo === 'admin') {
            header('Location: ' . basePath() . '/admin/dashboard.php?erro=acesso_negado');
        } elseif ($tipo === 'tecnico') {
            header('Location: ' . basePath() . '/tecnico/paineltecnico.php?erro=acesso_negado');
        } else {
            header('Location: ' . basePath() . '/publics/login.php?erro=acesso_negado');
        }
        exit();
    }
}

/**
 * Verifica timeout de sessão (opcional)
 * @param int $timeout_seconds Tempo em segundos (padrão: 2 horas)
 * @return bool
 */
function checkSessionTimeout($timeout_seconds = 7200) {
    if (!isset($_SESSION['last_activity'])) {
        $_SESSION['last_activity'] = time();
        return false;
    }

    $elapsed = time() - $_SESSION['last_activity'];

    if ($elapsed > $timeout_seconds) {
        // Sessão expirada
        session_unset();
        session_destroy();
        return true;
    }

    // Atualizar última atividade
    $_SESSION['last_activity'] = time();
    return false;
}

/**
 * Destrói a sessão de forma redundante: limpa as variáveis, chama session_destroy()
 * e, como camada extra, invalida explicitamente o cookie de sessão e confere que
 * nada ficou para trás. session_destroy() sozinho não invalida o cookie no
 * navegador nem garante que $_SESSION fique vazio na mesma requisição.
 */
function destruirSessaoComRedundancia() {
    // 1ª camada: limpar variáveis e destruir a sessão no servidor
    $_SESSION = [];
    session_unset();
    session_destroy();

    // 2ª camada: invalidar o cookie de sessão no navegador
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }

    // 3ª camada: se por algum motivo restou vestígio, força a limpeza
    if (!empty($_SESSION) || session_status() === PHP_SESSION_ACTIVE) {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}

/**
 * Fazer logout do usuário
 */
function logout() {
    // Registrar logout
    if (isAuthenticated()) {
        error_log("Logout do usuário: " . getUserName() . " (ID: " . getUserId() . ") | Tipo: " . getUserType());
    }

    // Limpar sessão com redundância (dados + cookie + checagem final)
    destruirSessaoComRedundancia();

    // Redirecionar para login
    header('Location: ' . basePath() . '/publics/login.php?msg=logout_sucesso');
    exit();
}

/**
 * Cria a tabela de tentativas de login se ainda não existir (mesma tabela
 * já usada por config/config_systens/auth_system.php — reaproveitada aqui
 * como implementação real de rate limiting por IP/banco, em vez do contador
 * em $_SESSION que um atacante contorna descartando o cookie a cada tentativa).
 * @param mysqli $conn
 */
function garantirTabelaLoginAttempts($conn) {
    $conn->query("CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        identificador VARCHAR(255) NOT NULL,
        tipo_usuario ENUM('cliente', 'tecnico') NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        tentativa_data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        sucesso BOOLEAN DEFAULT FALSE,
        INDEX idx_identificador (identificador),
        INDEX idx_ip (ip_address),
        INDEX idx_data (tentativa_data)
    )");
}

/**
 * Verifica se o identificador (email/matrícula) OU o IP atual estão bloqueados
 * por excesso de tentativas de login malsucedidas na janela de tempo.
 * @param mysqli $conn
 * @param string $identificador email ou matrícula tentando logar
 * @param string $tipo_usuario 'cliente' ou 'tecnico'
 * @param int $max_tentativas
 * @param int $tempo_bloqueio_segundos
 * @return array ['bloqueado' => bool, 'minutos' => int]
 */
function isLoginBloqueado($conn, $identificador, $tipo_usuario, $max_tentativas = 5, $tempo_bloqueio_segundos = 900) {
    garantirTabelaLoginAttempts($conn);

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';

    // Janela de tempo calculada no MySQL (NOW() - INTERVAL), não no PHP:
    // evita depender do relógio dos dois serviços estarem sincronizados
    // (confirmado divergente neste ambiente — PHP e MySQL em fusos diferentes).
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total, MIN(tentativa_data) as primeira, NOW() as agora
        FROM login_attempts
        WHERE (identificador = ? OR ip_address = ?)
        AND tipo_usuario = ?
        AND tentativa_data > (NOW() - INTERVAL ? SECOND)
        AND sucesso = 0
    ");
    $stmt->bind_param("sssi", $identificador, $ip, $tipo_usuario, $tempo_bloqueio_segundos);
    $stmt->execute();
    $resultado = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($resultado['total'] < $max_tentativas) {
        return ['bloqueado' => false, 'minutos' => 0];
    }

    $decorrido = strtotime($resultado['agora']) - strtotime($resultado['primeira']);
    $restante = $tempo_bloqueio_segundos - $decorrido;

    return [
        'bloqueado' => $restante > 0,
        'minutos' => max(1, (int) ceil($restante / 60)),
    ];
}

/**
 * Registra uma tentativa de login (sucesso ou falha) por identificador + IP.
 * @param mysqli $conn
 * @param string $identificador email ou matrícula usada na tentativa
 * @param string $tipo_usuario 'cliente' ou 'tecnico'
 * @param bool $sucesso
 */
function registrarTentativaLogin($conn, $identificador, $tipo_usuario, $sucesso) {
    garantirTabelaLoginAttempts($conn);

    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt = $conn->prepare("
        INSERT INTO login_attempts (identificador, tipo_usuario, ip_address, sucesso)
        VALUES (?, ?, ?, ?)
    ");
    $sucesso_int = $sucesso ? 1 : 0;
    $stmt->bind_param("sssi", $identificador, $tipo_usuario, $ip, $sucesso_int);
    $stmt->execute();
    $stmt->close();
}

/**
 * Registra uma ação em logs_sistema com IP e identificação do recurso afetado
 * (M5 — suficiente para reconstruir escopo de incidente, art. 48 LGPD).
 * @param string|null $tipoRecurso Ex: 'chamado', 'tecnico', 'usuario'
 * @param int|null $recursoId ID do recurso afetado
 */
function registrarLogSistema($conn, $usuarioId, $acao, $tipoRecurso = null, $recursoId = null) {
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    $stmt = $conn->prepare("
        INSERT INTO logs_sistema (usuario_id, acao, ip_address, tipo_recurso, recurso_id)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("isssi", $usuarioId, $acao, $ip, $tipoRecurso, $recursoId);
    $stmt->execute();
    $stmt->close();
}

/**
 * Renderiza mensagem de erro
 * @param string $tipo Tipo de erro
 */
function renderErrorMessage($tipo) {
    $mensagens = [
        'nao_autenticado' => 'Você precisa estar autenticado para acessar esta página.',
        'acesso_negado' => 'Você não tem permissão para acessar esta área.',
        'sessao_expirada' => 'Sua sessão expirou. Faça login novamente.',
        'bloqueado' => 'Sua conta foi temporariamente bloqueada por múltiplas tentativas de login.',
    ];

    $mensagem = $mensagens[$tipo] ?? 'Erro de autenticação.';

    echo '<div class="alert alert-danger" role="alert">';
    echo '<i class="fas fa-exclamation-triangle"></i> ' . htmlspecialchars($mensagem);
    echo '</div>';
}

/**
 * Debug de sessão (apenas em desenvolvimento)
 */
function debugSession() {
    if (Config::isDebug()) {
        echo '<pre style="background: #f8f9fa; padding: 15px; border-radius: 5px; border: 1px solid #dee2e6;">';
        echo '<strong>DEBUG - Sessão Atual:</strong><br>';
        echo 'Autenticado: ' . (isAuthenticated() ? 'SIM' : 'NÃO') . '<br>';
        echo 'Tipo: ' . (getUserType() ?? 'N/A') . '<br>';
        echo 'ID: ' . (getUserId() ?? 'N/A') . '<br>';
        echo 'Nome: ' . (getUserName() ?? 'N/A') . '<br>';
        echo 'IP: ' . ($_SESSION['ip_address'] ?? 'N/A') . '<br>';
        echo '<br><strong>Permissões:</strong><br>';
        echo 'isAdmin: ' . (isAdmin() ? 'SIM' : 'NÃO') . '<br>';
        echo 'isTecnico: ' . (isTecnico() ? 'SIM' : 'NÃO') . '<br>';
        echo 'isCliente: ' . (isCliente() ? 'SIM' : 'NÃO') . '<br>';
        echo '</pre>';
    }
}
?>
