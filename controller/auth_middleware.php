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

if (session_status() === PHP_SESSION_NONE) {
    session_start();
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
 * Requer autenticação (qualquer tipo de usuário)
 * Redireciona para login se não autenticado
 */
function requireAuth() {
    if (!isAuthenticated()) {
        // Salvar URL de retorno
        $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];

        // Redirecionar para login apropriado
        header('Location: /New_NetoNerd/publics/login.php?erro=nao_autenticado');
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
                header('Location: /New_NetoNerd/tecnico/paineltecnico.php?erro=acesso_negado');
                break;
            case 'cliente':
                header('Location: /New_NetoNerd/cliente/home.php?erro=acesso_negado');
                break;
            default:
                header('Location: /New_NetoNerd/publics/login.php?erro=acesso_negado');
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
            header('Location: /New_NetoNerd/cliente/home.php?erro=acesso_negado');
        } else {
            header('Location: /New_NetoNerd/publics/login.php?erro=acesso_negado');
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
            header('Location: /New_NetoNerd/admin/dashboard.php?erro=acesso_negado');
        } elseif ($tipo === 'tecnico') {
            header('Location: /New_NetoNerd/tecnico/paineltecnico.php?erro=acesso_negado');
        } else {
            header('Location: /New_NetoNerd/publics/login.php?erro=acesso_negado');
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
 * Fazer logout do usuário
 */
function logout() {
    // Registrar logout
    if (isAuthenticated()) {
        error_log("Logout do usuário: " . getUserName() . " (ID: " . getUserId() . ") | Tipo: " . getUserType());
    }

    // Limpar sessão
    session_unset();
    session_destroy();

    // Redirecionar para login
    header('Location: /New_NetoNerd/publics/login.php?msg=logout_sucesso');
    exit();
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
