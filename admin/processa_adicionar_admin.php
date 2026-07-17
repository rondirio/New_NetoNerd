<?php
/**
 * Processar Cadastro de Admin
 * NetoNerd ITSM v2.0
 */

require_once '../controller/auth_middleware.php';
requireAdmin();

require_once '../config/bandoDeDados/conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();

    $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $matricula = isset($_POST['matricula']) ? trim($_POST['matricula']) : '';
    $senha = isset($_POST['password']) ? trim($_POST['password']) : '';

    if (empty($nome) || empty($email) || empty($matricula) || empty($senha)) {
        header('Location: dashboard.php?erro=campos_obrigatorios');
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: dashboard.php?erro=email_invalido');
        exit();
    }

    if (strlen($senha) < 6) {
        header('Location: dashboard.php?erro=senha_curta');
        exit();
    }

    $sql_check = "SELECT id FROM admins WHERE email = ? OR matricula = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param('ss', $email, $matricula);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $stmt_check->close();
        $conn->close();
        header('Location: dashboard.php?erro=admin_existente');
        exit();
    }
    $stmt_check->close();

    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "INSERT INTO admins (nome, email, matricula, senha_hash, Ativo) VALUES (?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssss', $nome, $email, $matricula, $senha_hash);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header('Location: dashboard.php?sucesso=admin_cadastrado');
        exit();
    } else {
        $erro_msg = urlencode($stmt->error);
        $stmt->close();
        $conn->close();
        header('Location: dashboard.php?erro=erro_banco&msg=' . $erro_msg);
        exit();
    }

} else {
    header('Location: dashboard.php?erro=metodo_invalido');
    exit();
}
