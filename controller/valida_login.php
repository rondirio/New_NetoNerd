<?php
session_start();

include("../config/bandoDeDados/conexao.php");
$conn = getConnection();

if (!$conn) {
    die("Erro na conexão com o banco: " . mysqli_connect_error());
}

$email = $_POST['email'];
$senha = $_POST['senha'];

$query = "SELECT id, nome, senha_hash FROM clientes WHERE email = ?";
$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Erro ao preparar a query: " . $conn->error);
}

$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

$usuario_autenticado = false;

if ($result->num_rows === 1) {
    $usuario = $result->fetch_assoc();

    // Validação real
    if (password_verify($senha, $usuario['senha_hash'])) {
        $usuario_autenticado = true;
        $usuario_id   = $usuario['id'];
        $usuario_nome = $usuario['nome'];
    }
}

$stmt->close();
$conn->close();

if ($usuario_autenticado) {
    $_SESSION['autenticado'] = 'SIM';
    $_SESSION['id'] = $usuario_id;
    $_SESSION['nome'] = $usuario_nome;

    header('Location: ../cliente/home.php');
    exit();
}

$_SESSION['autenticado'] = 'NÃO';
header('Location: ../publics/index.php?login=erro');
exit();

?>
