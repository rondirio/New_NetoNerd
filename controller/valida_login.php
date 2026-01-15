<?php
/**
 * Validação de Login - CLIENTES
 * NetoNerd ITSM
 *
 * Login com EMAIL e SENHA
 */

session_start();
require_once "../config/bandoDeDados/conexao.php";

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../publics/login.php?erro=metodo_invalido');
    exit();
}

$conn = getConnection();

if (!$conn) {
    error_log("Erro na conexão com o banco: " . mysqli_connect_error());
    header('Location: ../publics/login.php?erro=conexao');
    exit();
}

// Sanitizar entrada
$email = trim($_POST['email'] ?? '');
$senha = $_POST['senha'] ?? '';

// Validação básica
if (empty($email) || empty($senha)) {
    header('Location: ../publics/login.php?erro=campos_vazios');
    exit();
}

// Validar formato do email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ../publics/login.php?erro=email_invalido');
    exit();
}

try {
    // Buscar cliente no banco
    $stmt = $conn->prepare("
        SELECT id, nome, email, senha_hash, telefone
        FROM clientes
        WHERE email = ?
        LIMIT 1
    ");

    if (!$stmt) {
        throw new Exception("Erro ao preparar query: " . $conn->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        // Cliente não encontrado
        error_log("Tentativa de login com email inexistente: $email | IP: " . $_SERVER['REMOTE_ADDR']);
        $stmt->close();
        $conn->close();

        header('Location: ../publics/login.php?erro=credenciais_invalidas');
        exit();
    }

    $cliente = $result->fetch_assoc();
    $stmt->close();

    // Verificar senha
    if (!password_verify($senha, $cliente['senha_hash'])) {
        // Senha incorreta
        error_log("Tentativa de login com senha incorreta: $email | IP: " . $_SERVER['REMOTE_ADDR']);
        $conn->close();

        header('Location: ../publics/login.php?erro=credenciais_invalidas');
        exit();
    }

    // ============================================
    // LOGIN BEM-SUCEDIDO
    // ============================================

    // Regenerar ID da sessão (segurança contra fixação)
    session_regenerate_id(true);

    // Definir variáveis de sessão
    $_SESSION['autenticado'] = 'SIM';
    $_SESSION['id'] = $cliente['id'];
    $_SESSION['usuario_id'] = $cliente['id'];
    $_SESSION['nome'] = $cliente['nome'];
    $_SESSION['email'] = $cliente['email'];
    $_SESSION['telefone'] = $cliente['telefone'];
    $_SESSION['tipo'] = 'cliente'; // IMPORTANTE: Define o tipo
    $_SESSION['tipo_usuario'] = 'cliente';
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
    $_SESSION['user_agent'] = $_SERVER['HTTP_USER_AGENT'];

    // Token CSRF
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    // Registrar log de acesso
    $stmt = $conn->prepare("
        INSERT INTO logs_sistema (usuario_id, acao)
        VALUES (?, 'Login de cliente bem-sucedido')
    ");
    if ($stmt) {
        $stmt->bind_param("i", $cliente['id']);
        $stmt->execute();
        $stmt->close();
    }

    $conn->close();

    // Redirecionar para a home do cliente
    header('Location: ../cliente/home.php');
    exit();

} catch (Exception $e) {
    error_log("Erro no login de cliente: " . $e->getMessage());
    $conn->close();

    header('Location: ../publics/login.php?erro=sistema');
    exit();
}
?>
