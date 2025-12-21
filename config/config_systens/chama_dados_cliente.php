<?php 

require_once "../controller/validador_acesso.php";

function obterDadosCliente() {
    require_once "../bandoDeDados/conexao.php";
    $conn = getConnection();
    $usuario_id = $_SESSION['id'];

    // Busca dados do cliente
    $stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $cliente = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    return $cliente;
}

?>