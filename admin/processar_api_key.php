<?php
/**
 * Processador de Chaves API - NetoNerd ITSM
 * Processa criação, exclusão e alteração de status das chaves API
 * Inclui dados de conexão do banco de dados do cliente
 */

require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';
require_once '../config/crypto.php';

requireAdmin();
requireCsrfToken();

$conn = getConnection();

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
        $criado_por = $_SESSION['id'] ?? $_SESSION['usuario_id'] ?? null;

        // Dados do banco de dados do cliente
        $db_host = trim($_POST['db_host'] ?? '');
        $db_nome = trim($_POST['db_nome'] ?? '');
        $db_usuario = trim($_POST['db_usuario'] ?? '');
        $db_senha = $_POST['db_senha'] ?? '';
        $db_porta = intval($_POST['db_porta'] ?? 3306);
        $ip_permitido = trim($_POST['ip_permitido'] ?? '');

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
            $chave = 'NN_' . bin2hex(random_bytes(16));
        }
        $stmt->close();

        if ($ip_permitido === '') {
            header('Location: api_keys.php?msg=ip_obrigatorio');
            exit;
        }

        // Criptografia real (AES-256-GCM) — ver config/crypto.php
        $db_senha_encrypted = encryptSecret($db_senha);

        // Inserir nova chave
        $stmt = $conn->prepare("INSERT INTO api_keys (chave, descricao, cliente_nome, db_host, db_nome, db_usuario, db_senha, db_porta, status, data_expiracao, ip_permitido, criado_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssisssi", $chave, $descricao, $cliente_nome, $db_host, $db_nome, $db_usuario, $db_senha_encrypted, $db_porta, $status, $data_expiracao, $ip_permitido, $criado_por);

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

    case 'testar_conexao':
        $id = intval($_POST['id'] ?? 0);

        if ($id > 0) {
            $stmt = $conn->prepare("SELECT db_host, db_nome, db_usuario, db_senha, db_porta FROM api_keys WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $data = $result->fetch_assoc();
                $stmt->close();

                // Descriptografar senha
                $senha = decryptSecret($data['db_senha']);

                // Tentar conexão
                $test_conn = @new mysqli(
                    $data['db_host'],
                    $data['db_usuario'],
                    $senha,
                    $data['db_nome'],
                    $data['db_porta']
                );

                if ($test_conn->connect_error) {
                    header('Location: api_keys.php?msg=conexao_erro');
                } else {
                    $test_conn->close();
                    header('Location: api_keys.php?msg=conexao_ok');
                }
            } else {
                $stmt->close();
                header('Location: api_keys.php?msg=erro');
            }
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
