<?php
/**
 * Validação de Login - TÉCNICOS e ADMINISTRADORES
 * NetoNerd ITSM - Versão Consolidada com Correção de FK
 */

// Ativa exibição de erros técnicos do MySQLi para depuração
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

require_once "../controller/auth_middleware.php";
require_once "../config/bandoDeDados/conexao.php";

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

$bloqueio = isLoginBloqueado($conn, $matricula, 'tecnico');
if ($bloqueio['bloqueado']) {
    header('Location: ../tecnico/loginTecnico.php?msg=bloqueado&tempo=' . $bloqueio['minutos']);
    exit();
}

// 1. BUSCA DE DADOS — tenta admins primeiro, depois tecnicos (tabelas
// separadas desde a Fase 4: admin não é mais decidido por padrão de
// matrícula, é uma pessoa cadastrada explicitamente na tabela admins)
$tipo = 'admin';
$stmt = $conn->prepare("SELECT id, nome, email, matricula, senha_hash FROM admins WHERE matricula = ? AND Ativo = 1 LIMIT 1");
$stmt->bind_param("s", $matricula);
$stmt->execute();
$result = $stmt->get_result();
$tecnico = $result->fetch_assoc();
$stmt->close();

if (!$tecnico) {
    $tipo = 'tecnico';
    $stmt = $conn->prepare("SELECT id, nome, email, matricula, senha_hash FROM tecnicos WHERE matricula = ? LIMIT 1");
    $stmt->bind_param("s", $matricula);
    $stmt->execute();
    $result = $stmt->get_result();
    $tecnico = $result->fetch_assoc();
    $stmt->close();
}

if (!$tecnico) {
    registrarTentativaLogin($conn, $matricula, 'tecnico', false);
    header('Location: ../tecnico/loginTecnico.php?erro=credenciais_invalidas');
    exit();
}

// 2. VALIDAÇÃO DE SENHA
$senha_valida = false;
if (!empty($tecnico['senha_hash']) && password_get_info($tecnico['senha_hash'])['algo'] !== null) {
    $senha_valida = password_verify($senha, $tecnico['senha_hash']);
} else if ($senha === $tecnico['senha_hash']) {
    // Migração automática para Hash seguro
    $senha_valida = true;
    $novo_hash = password_hash($senha, PASSWORD_DEFAULT);
    $tabela = ($tipo === 'admin') ? 'admins' : 'tecnicos';
    $upd = $conn->prepare("UPDATE $tabela SET senha_hash = ? WHERE id = ?");
    $upd->bind_param("si", $novo_hash, $tecnico['id']);
    $upd->execute();
    $upd->close();
}

if (!$senha_valida) {
    registrarTentativaLogin($conn, $matricula, 'tecnico', false);
    header('Location: ../tecnico/loginTecnico.php?erro=credenciais_invalidas');
    exit();
}

// 3. SUCESSO NO LOGIN
registrarTentativaLogin($conn, $matricula, 'tecnico', true);
criarSessaoTecnico($tecnico, $tipo);

// 4. GRAVAÇÃO DE LOG
$acao = "Login realizado: " . $tipo;
registrarLogSistema($conn, $tecnico['id'], $acao, $tipo, $tecnico['id']);

$conn->close();

// Redirecionamento Final
session_write_close();
$url = ($tipo === 'admin') ? '../admin/dashboard.php' : '../tecnico/paineltecnico.php';
header("Location: $url");
exit();