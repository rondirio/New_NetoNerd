<?php
/**
 * Processador de Chaves API - NetoNerd ITSM
 * Processa criação, exclusão e alteração de status das chaves API
 */

session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireAdmin();

$conn = getConnection();

// Verificar se ação foi enviada
if (!isset($_POST['acao'])) {
    header('Location: api_keys.php?msg=erro');
    exit;
}

$acao = $_POST['acao'];

switch ($acao) {
    case 'criar':
        $cliente_nome = trim($_POST['cliente_nome'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $chave = trim($_POST['chave'] ?? '');
        $status = $_POST['status'] ?? 'ativa';
        $data_expiracao = !empty($_POST['data_expiracao']) ? $_POST['data_expiracao'] : null;
        $ip_permitido = trim($_POST['ip_permitido'] ?? '');
        $criado_por = $_SESSION['id'] ?? $_SESSION['usuario_id'] ?? null;

        // Gerar chave se não fornecida
        if (empty($chave)) {
            $chave = 'NN_' . bin2hex(random_bytes(16));
        }

        // Verificar se chave já existe
        $stmt = $conn->prepare("SELECT id FROM api_keys WHERE chave = ?");
        $stmt->bind_param("s", $chave);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Chave já existe, gerar outra
            $chave = 'NN_' . bin2hex(random_bytes(16));
        }
        $stmt->close();

        // Inserir nova chave
        $stmt = $conn->prepare("INSERT INTO api_keys (chave, descricao, cliente_nome, status, data_expiracao, ip_permitido, criado_por) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssi", $chave, $descricao, $cliente_nome, $status, $data_expiracao, $ip_permitido, $criado_por);

        if ($stmt->execute()) {
            header('Location: api_keys.php?msg=criada');
        } else {
            header('Location: api_keys.php?msg=erro');
        }
        $stmt->close();
        break;

    case 'excluir':
        $id = intval($_POST['id'] ?? 0);

        if ($id > 0) {
            $stmt = $conn->prepare("DELETE FROM api_keys WHERE id = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                header('Location: api_keys.php?msg=excluida');
            } else {
                header('Location: api_keys.php?msg=erro');
            }
            $stmt->close();
        } else {
            header('Location: api_keys.php?msg=erro');
        }
        break;

    case 'ativar':
        $id = intval($_POST['id'] ?? 0);

        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE api_keys SET status = 'ativa' WHERE id = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                header('Location: api_keys.php?msg=atualizada');
            } else {
                header('Location: api_keys.php?msg=erro');
            }
            $stmt->close();
        } else {
            header('Location: api_keys.php?msg=erro');
        }
        break;

    case 'desativar':
        $id = intval($_POST['id'] ?? 0);

        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE api_keys SET status = 'inativa' WHERE id = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                header('Location: api_keys.php?msg=atualizada');
            } else {
                header('Location: api_keys.php?msg=erro');
            }
            $stmt->close();
        } else {
            header('Location: api_keys.php?msg=erro');
        }
        break;

    case 'revogar':
        $id = intval($_POST['id'] ?? 0);

        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE api_keys SET status = 'revogada' WHERE id = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                header('Location: api_keys.php?msg=atualizada');
            } else {
                header('Location: api_keys.php?msg=erro');
            }
            $stmt->close();
        } else {
            header('Location: api_keys.php?msg=erro');
        }
        break;

    default:
        header('Location: api_keys.php?msg=erro');
        break;
}

$conn->close();
exit;
?>
