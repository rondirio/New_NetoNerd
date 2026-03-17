<?php
/**
 * Script de Exclusão de Técnicos
 * NetoNerd ITSM
 */

session_start();

// // 1. Validação de Acesso (Apenas Admins podem excluir)
// if (!isset($_SESSION['autenticado']) || $_SESSION['autenticado'] !== 'SIM' || $_SESSION['tipo_usuario'] !== 'admin') {
//     header('Location: ../tecnico/loginTecnico.php?login=erro_acesso');
//     exit();
// }

// 2. Importa a conexão centralizada do seu projeto
require_once "../config/bandoDeDados/conexao.php";
$conn = getConnection();

if (!$conn) {
    error_log("Erro de conexão ao banco de dados no script de exclusão.");
    header('Location: dashboard.php?error=db');
    exit();
}

// 3. Recebe e sanitiza o ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: dashboard.php?error=invalid_id');
    exit();
}

// 4. Deleção com Prepared Statement
try {
    // Iniciamos uma transação para garantir segurança
    $conn->begin_transaction();

    $stmt = $conn->prepare("DELETE FROM tecnicos WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $conn->commit();
        $redirect_params = 'deleted=1';
        
        // Registrar ação no log do sistema (opcional, se você tiver a tabela logs_sistema)
        // $log_stmt = $conn->prepare("INSERT INTO logs_sistema (usuario_id, acao) VALUES (?, ?)");
        // $acao = "Excluiu o técnico com ID: " . $id;
        // $log_stmt->bind_param("is", $_SESSION['usuario_id'], $acao);
        // $log_stmt->execute();

    } else {
        $conn->rollback();
        $redirect_params = 'error=not_found';
    }

    $stmt->close();

} catch (Exception $e) {
    $conn->rollback();
    error_log("Erro ao excluir técnico: " . $e->getMessage());
    $redirect_params = 'error=stmt';
}

$conn->close();

// 5. Redirecionamento
header("Location: dashboard.php?$redirect_params");
exit();
?>