<?php
require_once '../controller/auth_middleware.php';
requireCliente();

include_once '../config/bandoDeDados/conexao.php';

// Verifica se os dados foram enviados via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();
    $id = $_POST['id']; // ID do chamado a ser atualizado
    $status = $_POST['status']; // Novo status do chamado
    $descricao = $_POST['descricao']; // Nova descrição do chamado
    $cliente_id = getUserId();

    // Validação básica
    if (!empty($id) && !empty($status) && !empty($descricao)) {
        // Atualiza o chamado no banco de dados, restrito ao dono do chamado
        $sql = "UPDATE chamados SET status = ?, descricao = ? WHERE id = ? AND cliente_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $status, $descricao, $id, $cliente_id);

        if ($stmt->execute()) {
            echo "Chamado atualizado com sucesso!";
        } else {
            echo "Erro ao atualizar o chamado: " . $conn->error;
        }

        $stmt->close();
    } else {
        echo "Por favor, preencha todos os campos.";
    }
}

$conn->close();
?>

<!-- Formulário para atualizar o chamado -->
<form method="POST" action="atualizarChamado.php">
    <?php echo csrfField(); ?>
    <label for="id">ID do Chamado:</label>
    <input type="text" name="id" id="id" required><br>

    <label for="status">Status:</label>
    <input type="text" name="status" id="status" required><br>

    <label for="descricao">Descrição:</label>
    <textarea name="descricao" id="descricao" required></textarea><br>

    <button type="submit">Atualizar Chamado</button>
</form>