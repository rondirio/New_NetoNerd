<?php
session_start();
require_once 'bandoDeDados/conexao.php';

// Verificar autenticação
if (!isset($_SESSION['id'])) {
    header('Location: index.php?erro=nao_autenticado');
    exit();
}

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: home.php');
    exit();
}

$conn = getConnection();
$usuario_id = $_SESSION['id'];

// Validar dados recebidos
if (!isset($_POST['chamado_id']) || !isset($_POST['resposta'])) {
    header('Location: home.php?erro=dados_invalidos');
    exit();
}

$chamado_id = intval($_POST['chamado_id']);
$resposta = trim($_POST['resposta']);

// Validações
if (empty($resposta)) {
    header('Location: detalhe_chamado.php?id=' . $chamado_id . '&erro=resposta_vazia');
    exit();
}

if (strlen($resposta) < 10) {
    header('Location: detalhe_chamado.php?id=' . $chamado_id . '&erro=resposta_curta');
    exit();
}

if (strlen($resposta) > 5000) {
    header('Location: detalhe_chamado.php?id=' . $chamado_id . '&erro=resposta_longa');
    exit();
}

// Verificar se o usuário é dono do chamado
$stmt = $conn->prepare("SELECT id, status FROM chamados WHERE id = ? AND cliente_id = ?");
$stmt->bind_param("ii", $chamado_id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    header('Location: home.php?erro=chamado_nao_encontrado');
    exit();
}

$chamado = $result->fetch_assoc();
$stmt->close();

// Verificar se o chamado está fechado ou cancelado
if (in_array($chamado['status'], ['fechado', 'cancelado'])) {
    $conn->close();
    header('Location: detalhe_chamado.php?id=' . $chamado_id . '&erro=chamado_fechado');
    exit();
}

// Inserir resposta
try {
    $stmt = $conn->prepare("
        INSERT INTO respostas_chamado (id_chamado, id_usuario, resposta, tipo_resposta) 
        VALUES (?, ?, ?, 'publica')
    ");
    
    $stmt->bind_param("iis", $chamado_id, $usuario_id, $resposta);
    
    if (!$stmt->execute()) {
        throw new Exception("Erro ao inserir resposta: " . $stmt->error);
    }
    
    $stmt->close();
    
    // Atualizar data de última atualização do chamado
    $stmt = $conn->prepare("
        UPDATE chamados 
        SET data_ultima_atualizacao = CURRENT_TIMESTAMP 
        WHERE id = ?
    ");
    
    $stmt->bind_param("i", $chamado_id);
    $stmt->execute();
    $stmt->close();
    
    // TODO: Enviar email para o técnico notificando nova resposta
    // Implementar na Fase 1.5
    
    $conn->close();
    
    // Redirecionar com sucesso
    header('Location: detalhe_chamado.php?id=' . $chamado_id . '&sucesso=resposta_adicionada');
    exit();
    
} catch (Exception $e) {
    $conn->close();
    error_log("Erro ao adicionar resposta: " . $e->getMessage());
    header('Location: detalhe_chamado.php?id=' . $chamado_id . '&erro=erro_servidor');
    exit();
}
?>