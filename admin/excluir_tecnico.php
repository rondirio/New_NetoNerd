<?php
/**
 * Script de Exclusão de Técnicos
 * NetoNerd ITSM
 */

// 1. Validação de Acesso (Apenas Admins podem excluir)
require_once "../controller/auth_middleware.php";
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php?error=invalid_method');
    exit();
}

requireCsrfToken();

// 2. Importa a conexão centralizada do seu projeto
require_once "../config/bandoDeDados/conexao.php";
$conn = getConnection();

if (!$conn) {
    error_log("Erro de conexão ao banco de dados no script de exclusão.");
    header('Location: dashboard.php?error=db');
    exit();
}

// 3. Recebe e sanitiza o ID
$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header('Location: dashboard.php?error=invalid_id');
    exit();
}

// 4. Deleção com Prepared Statement
try {
    // Iniciamos uma transação para garantir segurança
    $conn->begin_transaction();

    // Não existe FK entre chamados.tecnico_id e tecnicos.id — sem isto, os
    // chamados do técnico excluído ficariam com tecnico_id órfão, invisíveis
    // tanto na fila do técnico (que não existe mais) quanto na de não-atribuídos.
    $stmt_orfaos = $conn->prepare("UPDATE chamados SET tecnico_id = NULL WHERE tecnico_id = ?");
    $stmt_orfaos->bind_param('i', $id);
    $stmt_orfaos->execute();
    $stmt_orfaos->close();

    $stmt = $conn->prepare("DELETE FROM tecnicos WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $conn->commit();
        $redirect_params = 'deleted=1';

        registrarLogSistema($conn, $_SESSION['usuario_id'] ?? $_SESSION['id'] ?? null, "Excluiu o técnico com ID: $id", 'tecnico', $id);

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