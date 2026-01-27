<?php
/**
 * Validação de Login - TÉCNICOS e ADMINISTRADORES
 * NetoNerd ITSM - Versão Consolidada com Correção de FK
 */

// Ativa exibição de erros técnicos do MySQLi para depuração
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

session_start();
require_once "../config/bandoDeDados/conexao.php";

// Constantes de segurança
define('MAX_TENTATIVAS', 5);
define('TEMPO_BLOQUEIO', 900); // 15 minutos

/**
 * Identifica perfil pelo padrão da matrícula
 */
function isAdmin($matricula) {
    return (
        stripos($matricula, 'ADM') !== false ||
        preg_match('/\d{4}A\d{3}/', $matricula) === 1
    );
}

/**
 * Funções de controle de tentativas (Brute Force)
 */
function verificarBloqueio($matricula) {
    if (!isset($_SESSION['login_tentativas'])) $_SESSION['login_tentativas'] = [];
    if (isset($_SESSION['login_tentativas'][$matricula])) {
        $t = $_SESSION['login_tentativas'][$matricula];
        if ($t['contador'] >= MAX_TENTATIVAS) {
            $decorrido = time() - $t['ultimo_tempo'];
            if ($decorrido < TEMPO_BLOQUEIO) return ['bloqueado' => true, 'minutos' => ceil((TEMPO_BLOQUEIO - $decorrido) / 60)];
            unset($_SESSION['login_tentativas'][$matricula]);
        }
    }
    return ['bloqueado' => false];
}

function registrarTentativa($matricula, $sucesso = false) {
    if (!isset($_SESSION['login_tentativas'])) $_SESSION['login_tentativas'] = [];
    if ($sucesso) {
        unset($_SESSION['login_tentativas'][$matricula]);
    } else {
        if (!isset($_SESSION['login_tentativas'][$matricula])) {
            $_SESSION['login_tentativas'][$matricula] = ['contador' => 1, 'ultimo_tempo' => time()];
        } else {
            $_SESSION['login_tentativas'][$matricula]['contador']++;
            $_SESSION['login_tentativas'][$matricula]['ultimo_tempo'] = time();
        }
    }
}

/**
 * Configuração da Sessão Autenticada
 */
function criarSessaoTecnico($tecnico, $tipo_usuario) {
    session_regenerate_id(true);
    $_SESSION['autenticado'] = 'SIM';

    // IDs do usuário (compatibilidade com sistemas antigos e novos)
    $_SESSION['id'] = $tecnico['id'];                    // Para admin pages
    $_SESSION['usuario_id'] = $tecnico['id'];            // Para compatibilidade

    // Tipo de usuário (compatibilidade com sistemas antigos e novos)
    $_SESSION['tipo'] = $tipo_usuario;                   // Para admin pages
    $_SESSION['tipo_usuario'] = $tipo_usuario;           // Para compatibilidade

    // Dados do usuário
    $_SESSION['nome'] = $tecnico['nome'];                // Para admin pages
    $_SESSION['usuario_nome'] = $tecnico['nome'];        // Para compatibilidade
    $_SESSION['email'] = $tecnico['email'];              // Para admin pages
    $_SESSION['usuario_email'] = $tecnico['email'];      // Para compatibilidade
    $_SESSION['matricula'] = $tecnico['matricula'];

    // Segurança
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ============================================
// INÍCIO DO PROCESSAMENTO
// ============================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') die("Acesso inválido.");

$conn = getConnection();
$matricula = trim($_POST['matricula'] ?? '');
$senha = $_POST['senha'] ?? '';

// Validação básica de entrada
if (empty($matricula) || empty($senha)) {
    header('Location: ../tecnico/loginTecnico.php?erro=campos_vazios');
    exit();
}

$bloqueio = verificarBloqueio($matricula);
if ($bloqueio['bloqueado']) {
    header('Location: ../tecnico/loginTecnico.php?erro=bloqueado&tempo=' . $bloqueio['minutos']);
    exit();
}

// 1. BUSCA DE DADOS
$stmt = $conn->prepare("
    SELECT id, nome, email, matricula, senha_hash 
    FROM tecnicos 
    WHERE matricula = ? 
    LIMIT 1
");
$stmt->bind_param("s", $matricula);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    registrarTentativa($matricula, false);
    header('Location: ../tecnico/loginTecnico.php?erro=credenciais_invalidas');
    exit();
}

$tecnico = $result->fetch_assoc();
$stmt->close();

// 2. VALIDAÇÃO DE SENHA
$senha_valida = false;
if (!empty($tecnico['senha_hash']) && password_get_info($tecnico['senha_hash'])['algo'] !== null) {
    $senha_valida = password_verify($senha, $tecnico['senha_hash']);
} else if ($senha === $tecnico['senha_hash']) {
    // Migração automática para Hash seguro
    $senha_valida = true;
    $novo_hash = password_hash($senha, PASSWORD_DEFAULT);
    $upd = $conn->prepare("UPDATE tecnicos SET senha_hash = ? WHERE id = ?");
    $upd->bind_param("si", $novo_hash, $tecnico['id']);
    $upd->execute();
    $upd->close();
}

if (!$senha_valida) {
    registrarTentativa($matricula, false);
    header('Location: ../tecnico/loginTecnico.php?erro=credenciais_invalidas');
    exit();
}

// 3. SUCESSO NO LOGIN
registrarTentativa($matricula, true);
$tipo = isAdmin($matricula) ? 'admin' : 'tecnico';
criarSessaoTecnico($tecnico, $tipo);

// 4. GRAVAÇÃO DE LOG (PROTEÇÃO CONTRA ERRO DE FK)
// Verificamos se o ID existe na tabela 'usuarios' antes do insert para evitar o Fatal Error
$check = $conn->prepare("SELECT id FROM usuarios WHERE id = ?");
$check->bind_param("i", $tecnico['id']);
$check->execute();
$idValidoParaLog = $check->get_result()->num_rows > 0;
$check->close();

if ($idValidoParaLog) {
    $stmt_log = $conn->prepare("INSERT INTO logs_sistema (usuario_id, acao) VALUES (?, ?)");
    $acao = "Login realizado: " . $tipo;
    $stmt_log->bind_param("is", $tecnico['id'], $acao);
    $stmt_log->execute();
    $stmt_log->close();
} else {
    // Grava log sem o ID caso o usuário não esteja na tabela vinculada
    $stmt_log = $conn->prepare("INSERT INTO logs_sistema (acao) VALUES (?)");
    $acao = "Login externo detectado ($tipo): " . $tecnico['nome'];
    $stmt_log->bind_param("s", $acao);
    $stmt_log->execute();
    $stmt_log->close();
}

$conn->close();

// Redirecionamento Final
session_write_close();
$url = ($tipo === 'admin') ? '../admin/dashboard.php' : '../tecnico/paineltecnico.php';
header("Location: $url");
exit();