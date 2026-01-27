<?php
session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

// PROTEÇÃO: Apenas administradores
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: atribuir_chamados.php?erro=metodo_invalido');
    exit();
}

$conn = getConnection();

$chamado_id = intval($_POST['chamado_id'] ?? 0);
$tecnico_id = intval($_POST['tecnico_id'] ?? 0);
$admin_id = intval($_SESSION['id']);
$comentario = trim($_POST['comentario'] ?? '');
$acao = $_POST['acao'] ?? 'atribuir';

// Validações
if ($chamado_id === 0 || $tecnico_id === 0) {
    header('Location: atribuir_chamados.php?erro=dados_invalidos');
    exit();
}

try {
    $conn->begin_transaction();

    // Verificar se chamado existe
    $stmt = $conn->prepare("SELECT id, titulo, tecnico_id FROM chamados WHERE id = ?");
    $stmt->bind_param("i", $chamado_id);
    $stmt->execute();
    $chamado = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$chamado) {
        throw new Exception("Chamado não encontrado");
    }

    // Verificar se técnico existe e está ativo
    $stmt = $conn->prepare("SELECT id, nome, matricula FROM tecnicos WHERE id = ? AND Ativo = 1");
    $stmt->bind_param("i", $tecnico_id);
    $stmt->execute();
    $tecnico = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$tecnico) {
        throw new Exception("Técnico não encontrado ou inativo");
    }

    // Verificar se é admin (não pode atribuir a admins)
    if (stripos($tecnico['matricula'], 'ADM') !== false || preg_match('/\d{4}A\d{3}/', $tecnico['matricula'])) {
        throw new Exception("Não é possível atribuir chamados a administradores");
    }

    // Se já tinha um técnico, desativar atribuição anterior
    if ($chamado['tecnico_id'] !== null) {
        $stmt = $conn->prepare("
            UPDATE chamado_atribuicoes
            SET ativo = 0
            WHERE chamado_id = ? AND ativo = 1
        ");
        $stmt->bind_param("i", $chamado_id);
        $stmt->execute();
        $stmt->close();
    }

    // Atualizar chamado com novo técnico
    $stmt = $conn->prepare("UPDATE chamados SET tecnico_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $tecnico_id, $chamado_id);
    $stmt->execute();
    $stmt->close();

    // Registrar atribuição no histórico
    $stmt = $conn->prepare("
        INSERT INTO chamado_atribuicoes (chamado_id, tecnico_id, admin_id, comentario, ativo)
        VALUES (?, ?, ?, ?, 1)
    ");
    $stmt->bind_param("iiis", $chamado_id, $tecnico_id, $admin_id, $comentario);
    $stmt->execute();
    $stmt->close();

    // Registrar no histórico de chamados
    $historico_comentario = "Chamado atribuído ao técnico " . $tecnico['nome'] . " (" . $tecnico['matricula'] . ")";
    if ($comentario) {
        $historico_comentario .= " - Comentário do admin: " . $comentario;
    }

    $stmt = $conn->prepare("
        INSERT INTO historico_chamados (chamado_id, usuario_id, status_anterior, status_novo, comentario)
        VALUES (?, ?, NULL, NULL, ?)
    ");
    $stmt->bind_param("iis", $chamado_id, $admin_id, $historico_comentario);
    $stmt->execute();
    $stmt->close();

    // Registrar log do sistema
    $log_acao = "Atribuiu chamado #$chamado_id ao técnico " . $tecnico['nome'] . " (ID: $tecnico_id)";
    $stmt = $conn->prepare("INSERT INTO logs_sistema (usuario_id, acao) VALUES (?, ?)");
    $stmt->bind_param("is", $admin_id, $log_acao);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    // Redirecionar com sucesso
    header('Location: atribuir_chamados.php?sucesso=1');
    exit();

} catch (Exception $e) {
    $conn->rollback();
    error_log("Erro ao atribuir chamado: " . $e->getMessage());
    header('Location: atribuir_chamados.php?erro=' . urlencode($e->getMessage()));
    exit();
}
?>
