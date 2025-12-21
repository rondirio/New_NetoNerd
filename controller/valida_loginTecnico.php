<?php


/**
 * Validação de Login - Técnicos e Administradores
 * NetoNerd ITSM - Versão Corrigida
 * 
 * MELHORIAS APLICADAS:
 * - Uso consistente de MySQLi (mantendo padrão do projeto)
 * - Proteção contra SQL Injection
 * - Segurança de sessão aprimorada
 * - Sistema de tentativas de login
 */

session_start();
// print_r($_SESSION);

require_once "../config/bandoDeDados/conexao.php";
// require_once '../controller/validador_acesso.php';


// Constantes de segurança
define('MAX_TENTATIVAS', 5);
define('TEMPO_BLOQUEIO', 900); // 15 minutos em segundos


// die();
/**
 * Verifica se o usuário está bloqueado por tentativas excessivas
 */
function verificarBloqueio($matricula) {
    if (!isset($_SESSION['login_tentativas'])) {
        $_SESSION['login_tentativas'] = [];
    }
    
    if (isset($_SESSION['login_tentativas'][$matricula])) {
        $tentativa = $_SESSION['login_tentativas'][$matricula];
        
        if ($tentativa['contador'] >= MAX_TENTATIVAS) {
            $tempo_decorrido = time() - $tentativa['ultimo_tempo'];
            
            if ($tempo_decorrido < TEMPO_BLOQUEIO) {
                $tempo_restante = ceil((TEMPO_BLOQUEIO - $tempo_decorrido) / 60);
                return [
                    'bloqueado' => true,
                    'minutos' => $tempo_restante
                ];
            } else {
                // Tempo de bloqueio expirado - reseta tentativas
                unset($_SESSION['login_tentativas'][$matricula]);
            }
        }
    }
    
    return ['bloqueado' => false];
}

// print_r($_SESSION);


/**
 * Registra tentativa de login
 */
function registrarTentativa($matricula, $sucesso = false) {
    if (!isset($_SESSION['login_tentativas'])) {
        $_SESSION['login_tentativas'] = [];
    }
    
    if ($sucesso) {
        // Login bem-sucedido - limpa tentativas
        unset($_SESSION['login_tentativas'][$matricula]);
    } else {
        // Login falhou - incrementa contador
        if (!isset($_SESSION['login_tentativas'][$matricula])) {
            $_SESSION['login_tentativas'][$matricula] = [
                'contador' => 1,
                'ultimo_tempo' => time()
            ];
        } else {
            $_SESSION['login_tentativas'][$matricula]['contador']++;
            $_SESSION['login_tentativas'][$matricula]['ultimo_tempo'] = time();
        }
    }
}
print_r($_SESSION);


/**
 * Cria sessão segura para o técnico
 */
function criarSessaoTecnico($tecnico, $tipo_usuario) {
    // Regenera ID da sessão para prevenir fixação de sessão
    session_regenerate_id(true);
    
    // Define variáveis de sessão
    $_SESSION['autenticado'] = 'SIM';
    $_SESSION['usuario_id'] = $tecnico['id'];
    $_SESSION['tipo_usuario'] = $tipo_usuario;
    $_SESSION['usuario_nome'] = $tecnico['nome'];
    $_SESSION['usuario_email'] = $tecnico['email'];
    $_SESSION['matricula'] = $tecnico['matricula'];
    $_SESSION['carro_do_dia'] = $tecnico['carro_do_dia'];
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    
    // Token CSRF
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// print_r($_POST);


// ============================================
// PROCESSAMENTO DO LOGIN
// ============================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../tecnico/loginTecnico.php?login=erro2');
    exit();
}

$conn = getConnection();

if (!$conn) {
    error_log("Erro de conexão ao banco de dados: " . mysqli_connect_error());
    header('Location: ../tecnico/loginTecnico.php?login=erro&msg=conexao');
    exit();
}

// Sanitiza entrada
$matricula = trim($_POST['matricula'] ?? '');
$senha = $_POST['senha'] ?? '';

// Validação básica
if (empty($matricula) || empty($senha)) {
    header('Location: ../tecnico/loginTecnico.php?login=erro&msg=campos_vazios');
    exit();
}

// Verifica bloqueio por tentativas
$bloqueio = verificarBloqueio($matricula);
if ($bloqueio['bloqueado']) {
    header('Location: ../tecnico/loginTecnico.php?login=erro&msg=bloqueado&tempo=' . $bloqueio['minutos']);
    exit();
}

// ============================================
// CONTA MASTER DE ADMINISTRAÇÃO (TEMPORÁRIA)
// ============================================
// IMPORTANTE: Esta conta deve ser removida em produção
// e substituída por um técnico cadastrado no banco

if ($matricula === 'Rondineli' && $senha === 'Rcouto95') {
    // Verifica se já existe um admin cadastrado
    $check_admin = $conn->prepare("SELECT id FROM tecnicos WHERE matricula LIKE '%ADM%' LIMIT 1");
    $check_admin->execute();
    
    if ($check_admin->get_result()->num_rows == 0) {
        // Cria conta admin temporária se não existir
        $admin_data = [
            'id' => 0,
            'nome' => 'Rondineli Oliveira (Admin Master)',
            'email' => 'rondi.rio@netonerd.com.br',
            'matricula' => '2025F1ADM000',
            'carro_do_dia' => 'N/A'
        ];
        
        criarSessaoTecnico($admin_data, 'admin');
        registrarTentativa($matricula, true);
        
        // Log de acesso master
        error_log("ACESSO MASTER: Usuário Rondineli acessou o sistema em " . date('Y-m-d H:i:s'));
        
        // header('Location: ../admin/dashboard.php');
        exit();
    }
}

// ============================================
// AUTENTICAÇÃO NORMAL VIA BANCO DE DADOS
// ============================================

// Prepara query com proteção contra SQL Injection
$stmt = $conn->prepare(
    "SELECT id, nome, email, matricula, senha_hash, carro_do_dia, status_tecnico 
     FROM tecnicos 
     WHERE matricula = ? 
     LIMIT 1"
);

if (!$stmt) {
    error_log("Erro ao preparar statement: " . $conn->error);
    header('Location: ../tecnico/loginTecnico.php?login=erro&msg=sistema');
    exit();
}

$stmt->bind_param("s", $matricula);
$stmt->execute();
$result = $stmt->get_result();

// Verifica se o técnico existe
if ($result->num_rows === 0) {
    registrarTentativa($matricula, false);
    
    // Log de tentativa de login com matrícula inexistente
    error_log("Tentativa de login com matrícula inexistente: $matricula | IP: " . $_SERVER['REMOTE_ADDR']);
    
    $stmt->close();
    $conn->close();
    
    header('Location: ../tecnico/loginTecnico.php?login=erro&msg=credenciais');
    exit();
}

$tecnico = $result->fetch_assoc();
$stmt->close();

// Verifica se o técnico está ativo
if ($tecnico['status_tecnico'] != 'Ativo') {
    registrarTentativa($matricula, false);
    error_log("Tentativa de login com conta inativa: {$tecnico['nome']} (ID: {$tecnico['id']})");
    
    $conn->close();
    
    header('Location: ../tecnico/loginTecnico.php?login=erro&msg=inativo');
    exit();
}

// Verifica status do técnico
if ($tecnico['status_tecnico'] !== 'Ativo') {
    registrarTentativa($matricula, false);
    
    $conn->close();
    
    header('Location: ../tecnico/loginTecnico.php?login=erro&msg=status_inativo');
    exit();
}

// ============================================
// VERIFICAÇÃO DE SENHA
// ============================================

$senha_valida = false;

// Verifica se a senha está em hash
if (password_get_info($tecnico['senha_hash'])['algo'] !== null) {
    // Senha está em hash - verifica com password_verify
    $senha_valida = password_verify($senha, $tecnico['senha_hash']);
} else {
    // Senha está em texto plano (legado) - compara diretamente
    // IMPORTANTE: Atualizar para hash após validação
    if ($senha === $tecnico['senha_hash']) {
        $senha_valida = true;
        
        // Atualiza senha para hash
        $novo_hash = password_hash($senha, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE tecnicos SET senha_hash = ? WHERE id = ?");
        $update_stmt->bind_param("si", $novo_hash, $tecnico['id']);
        $update_stmt->execute();
        $update_stmt->close();
        
        error_log("Senha de {$tecnico['nome']} atualizada para hash seguro");
    }
}

$conn->close();

// Verifica resultado da validação
if (!$senha_valida) {
    registrarTentativa($matricula, false);
    
    // Log de tentativa falha
    error_log("Tentativa de login falha: {$tecnico['nome']} (Matrícula: $matricula) | IP: " . $_SERVER['REMOTE_ADDR']);
    
    header('Location: ../tecnico/loginTecnico.php?login=erro&msg=credenciais');
    exit();
}

// ============================================
// LOGIN BEM-SUCEDIDO
// ============================================

registrarTentativa($matricula, true);

// Determina tipo de usuário baseado na matrícula
$tipo_usuario = (strpos($matricula, 'ADM') !== false) ? 'admin' : 'tecnico';

// Cria sessão segura
criarSessaoTecnico($tecnico, $tipo_usuario);

// Log de sucesso
error_log("Login bem-sucedido: {$tecnico['nome']} ({$tipo_usuario}) | IP: " . $_SERVER['REMOTE_ADDR']);

// Redireciona baseado no tipo
if ($tipo_usuario === 'admin') {
    header('Location: ../admin/dashboard.php');
} else {
    header('Location: ../tecnico/paineltecnico.php');
}

print_r($_SESSION);

exit();
?>