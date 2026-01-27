<?php
/**
 * Atualizar Status da Ordem de Serviço - NetoNerd ITSM v2.0
 */
session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar_ordens_servico.php?erro=metodo_invalido');
    exit();
}

$conn = getConnection();

$os_id = intval($_POST['os_id']);
$novo_status = trim($_POST['novo_status']);

// Validar status
$status_validos = ['concluida', 'cancelada'];
if (!in_array($novo_status, $status_validos)) {
    header('Location: visualizar_ordem_servico.php?id=' . $os_id . '&erro=Status inválido');
    exit();
}

// Buscar OS atual
$sql_check = "SELECT status, data_inicio FROM ordens_servico WHERE id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param('i', $os_id);
$stmt_check->execute();
$result = $stmt_check->get_result();

if ($result->num_rows === 0) {
    $stmt_check->close();
    $conn->close();
    header('Location: listar_ordens_servico.php?erro=Ordem de serviço não encontrada');
    exit();
}

$os_atual = $result->fetch_assoc();
$status_antigo = $os_atual['status'];
$data_inicio_antiga = $os_atual['data_inicio'];
$stmt_check->close();

// Preparar SQL baseado no novo status
$sql = "UPDATE ordens_servico SET status = ?";
$params = [$novo_status];
$types = 's';

// Lógica de datas baseada na mudança de status
if ($novo_status === 'em_andamento' && !$data_inicio_antiga) {
    // Iniciando atendimento pela primeira vez
    $sql .= ", data_inicio = NOW()";
} elseif ($novo_status === 'concluida' && $status_antigo !== 'concluida') {
    // Concluindo atendimento
    $sql .= ", data_conclusao = NOW()";
    
    // Se não tinha data de início, define agora também
    if (!$data_inicio_antiga) {
        $sql .= ", data_inicio = NOW()";
    }
} elseif ($novo_status === 'cancelada') {
    // Cancelando ordem - define data de conclusão
    $sql .= ", data_conclusao = NOW()";
    
    // Se não tinha data de início, define agora também
    if (!$data_inicio_antiga) {
        $sql .= ", data_inicio = NOW()";
    }
}

$sql .= " WHERE id = ?";
$params[] = $os_id;
$types .= 'i';

// Executar atualização
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    
    // Mensagem de sucesso baseada no novo status
    $mensagem = match($novo_status) {
        'aberta' => 'Ordem reaberta com sucesso',
        'em_andamento' => 'Atendimento iniciado com sucesso',
        'concluida' => 'Ordem concluída com sucesso',
        'cancelada' => 'Ordem cancelada com sucesso',
        default => 'Status atualizado com sucesso'
    };
    
    header('Location: visualizar_ordem_servico.php?id=' . $os_id . '&sucesso=status_atualizado&msg=' . urlencode($mensagem));
    exit();
    
} else {
    $erro = $stmt->error;
    $stmt->close();
    $conn->close();
    header('Location: visualizar_ordem_servico.php?id=' . $os_id . '&erro=' . urlencode('Erro ao atualizar status: ' . $erro));
    exit();
}
?>