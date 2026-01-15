<?php
session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

// PROTEÇÃO: Apenas administradores podem acessar
requireAdmin();

// Verificar autenticação de admin
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: ../publics/login.php?erro=acesso_negado');
    exit();
}

$conn = getConnection();
$acao = $_REQUEST['acao'] ?? '';

try {
    switch ($acao) {
        case 'criar':
            // Validar dados
            $nome = trim($_POST['nome'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $cor = $_POST['cor'] ?? '#007bff';
            $icone = trim($_POST['icone'] ?? 'fa-ticket');

            if (empty($nome)) {
                throw new Exception('Nome é obrigatório');
            }

            // Verificar se já existe
            $stmt = $conn->prepare("SELECT id FROM categorias_chamado WHERE nome = ?");
            $stmt->bind_param("s", $nome);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $stmt->close();
                $conn->close();
                header('Location: categorias.php?erro=nome_duplicado');
                exit();
            }
            $stmt->close();

            // Inserir categoria
            $stmt = $conn->prepare("
                INSERT INTO categorias_chamado (nome, descricao, cor, icone, ativo)
                VALUES (?, ?, ?, ?, 1)
            ");
            $stmt->bind_param("ssss", $nome, $descricao, $cor, $icone);
            $stmt->execute();
            $stmt->close();

            header('Location: categorias.php?sucesso=criada');
            break;

        case 'editar':
            // Validar dados
            $id = intval($_POST['id'] ?? 0);
            $nome = trim($_POST['nome'] ?? '');
            $descricao = trim($_POST['descricao'] ?? '');
            $cor = $_POST['cor'] ?? '#007bff';
            $icone = trim($_POST['icone'] ?? 'fa-ticket');

            if ($id <= 0 || empty($nome)) {
                throw new Exception('Dados inválidos');
            }

            // Verificar se já existe outro com o mesmo nome
            $stmt = $conn->prepare("SELECT id FROM categorias_chamado WHERE nome = ? AND id != ?");
            $stmt->bind_param("si", $nome, $id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                $stmt->close();
                $conn->close();
                header('Location: categorias.php?erro=nome_duplicado');
                exit();
            }
            $stmt->close();

            // Atualizar categoria
            $stmt = $conn->prepare("
                UPDATE categorias_chamado
                SET nome = ?, descricao = ?, cor = ?, icone = ?
                WHERE id = ?
            ");
            $stmt->bind_param("ssssi", $nome, $descricao, $cor, $icone, $id);
            $stmt->execute();
            $stmt->close();

            header('Location: categorias.php?sucesso=atualizada');
            break;

        case 'excluir':
            $id = intval($_GET['id'] ?? 0);

            if ($id <= 0) {
                throw new Exception('ID inválido');
            }

            // Verificar se está em uso
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM chamados WHERE categoria_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($result['total'] > 0) {
                $conn->close();
                header('Location: categorias.php?erro=em_uso');
                exit();
            }

            // Excluir categoria
            $stmt = $conn->prepare("DELETE FROM categorias_chamado WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            header('Location: categorias.php?sucesso=excluida');
            break;

        case 'ativar':
            $id = intval($_GET['id'] ?? 0);

            if ($id <= 0) {
                throw new Exception('ID inválido');
            }

            $stmt = $conn->prepare("UPDATE categorias_chamado SET ativo = 1 WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            header('Location: categorias.php?sucesso=ativada');
            break;

        case 'desativar':
            $id = intval($_GET['id'] ?? 0);

            if ($id <= 0) {
                throw new Exception('ID inválido');
            }

            $stmt = $conn->prepare("UPDATE categorias_chamado SET ativo = 0 WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();

            header('Location: categorias.php?sucesso=desativada');
            break;

        default:
            throw new Exception('Ação inválida');
    }

} catch (Exception $e) {
    error_log("Erro ao processar categoria: " . $e->getMessage());
    header('Location: categorias.php?erro=geral');
}

$conn->close();
?>
