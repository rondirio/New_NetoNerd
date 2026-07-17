<?php
/**
 * Encerra um chamado sem resolução, quando o cliente não responde às
 * mensagens do técnico há pelo menos 48h. Reaproveita o status 'cancelado'
 * (mesmo usado quando o cliente cancela o próprio chamado) — o histórico
 * registra que foi o técnico quem encerrou, e por quê.
 */
require_once '../controller/auth_middleware.php';
require_once '../controller/historico_chamados.php';
require_once '../config/bandoDeDados/conexao.php';

requireTecnico();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: meus_chamados.php?erro=metodo_invalido');
    exit();
}

requireCsrfToken();

$conn = getConnection();
$tecnico_id = $_SESSION['usuario_id'];
$chamado_id = intval($_POST['chamado_id'] ?? 0);
$justificativa = trim($_POST['justificativa'] ?? '');

if ($chamado_id === 0) {
    header('Location: meus_chamados.php?erro=chamado_invalido');
    exit();
}

if (strlen($justificativa) < 20) {
    header('Location: resolver_chamado.php?id=' . $chamado_id . '&erro=' . urlencode('A justificativa deve ter pelo menos 20 caracteres.'));
    exit();
}

try {
    $conn->begin_transaction();

    $stmt = $conn->prepare("
        SELECT * FROM chamados
        WHERE id = ? AND tecnico_id = ? AND status != 'resolvido' AND status != 'cancelado'
    ");
    $stmt->bind_param("ii", $chamado_id, $tecnico_id);
    $stmt->execute();
    $chamado = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$chamado) {
        throw new Exception("Chamado não encontrado, não atribuído a você, ou já foi encerrado.");
    }

    // Revalida a elegibilidade no servidor (48h sem resposta do cliente após
    // a última mensagem do técnico) — a tela já checa isso, mas não confiar
    // só na validação client-side/HTML para uma ação que fecha o chamado.
    $stmt = $conn->prepare("
        SELECT MAX(data_atualizacao) as ultima
        FROM chamado_atualizacoes
        WHERE chamado_id = ? AND tipo_atualizacao = 'comentario'
    ");
    $stmt->bind_param("i", $chamado_id);
    $stmt->execute();
    $ultima_msg_tecnico_atualizacao = $stmt->get_result()->fetch_assoc()['ultima'];
    $stmt->close();

    $stmt = $conn->prepare("
        SELECT MAX(data_resposta) as ultima
        FROM respostas_chamado
        WHERE chamado_id = ? AND tipo_usuario IN ('tecnico', 'admin')
    ");
    $stmt->bind_param("i", $chamado_id);
    $stmt->execute();
    $ultima_msg_tecnico_resposta = $stmt->get_result()->fetch_assoc()['ultima'];
    $stmt->close();

    $stmt = $conn->prepare("
        SELECT MAX(data_resposta) as ultima
        FROM respostas_chamado
        WHERE chamado_id = ? AND tipo_usuario = 'cliente'
    ");
    $stmt->bind_param("i", $chamado_id);
    $stmt->execute();
    $ultima_resposta_cliente = $stmt->get_result()->fetch_assoc()['ultima'];
    $stmt->close();

    $candidatos = array_filter([$ultima_msg_tecnico_atualizacao, $ultima_msg_tecnico_resposta]);
    $ultima_msg_tecnico = $candidatos ? max($candidatos) : null;

    if (!$ultima_msg_tecnico) {
        throw new Exception("Envie uma mensagem ao cliente antes de encerrar sem resolução.");
    }

    $cliente_respondeu_depois = $ultima_resposta_cliente && strtotime($ultima_resposta_cliente) > strtotime($ultima_msg_tecnico);
    if ($cliente_respondeu_depois) {
        throw new Exception("O cliente já respondeu — não é possível encerrar sem resolução.");
    }

    $horas_sem_resposta = (time() - strtotime($ultima_msg_tecnico)) / 3600;
    if ($horas_sem_resposta < 48) {
        throw new Exception("Ainda não passaram 48h desde a última mensagem sem resposta do cliente.");
    }

    $stmt = $conn->prepare("
        UPDATE chamados
        SET status = 'cancelado', data_fechamento = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param("i", $chamado_id);
    $stmt->execute();
    $stmt->close();

    $descricao = "Chamado encerrado sem resolução — cliente não respondeu em 48h. " . $justificativa;

    $stmt = $conn->prepare("
        INSERT INTO chamado_atualizacoes (chamado_id, tecnico_id, tipo_atualizacao, descricao)
        VALUES (?, ?, 'conclusao', ?)
    ");
    $stmt->bind_param("iis", $chamado_id, $tecnico_id, $descricao);
    $stmt->execute();
    $stmt->close();

    registrarHistoricoStatus($conn, $chamado_id, $tecnico_id, $chamado['status'], 'cancelado', $descricao);

    registrarLogSistema($conn, $tecnico_id, "Encerrou sem resolução o chamado #$chamado_id (cliente sem resposta em 48h).", 'chamado', $chamado_id);

    $conn->commit();

    header('Location: meus_chamados.php?sucesso=encerrado_sem_resolucao');
    exit();

} catch (Exception $e) {
    $conn->rollback();
    error_log("Erro ao encerrar sem resolução: " . $e->getMessage());
    header('Location: resolver_chamado.php?id=' . $chamado_id . '&erro=' . urlencode($e->getMessage()));
    exit();
}
