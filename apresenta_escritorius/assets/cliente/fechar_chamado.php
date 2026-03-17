<?php
session_start();
require_once 'bandoDeDados/conexao.php';

// Verificar autenticação
if (!isset($_SESSION['id'])) {
    header('Location: index.php?erro=nao_autenticado');
    exit();
}

$conn = getConnection();
$usuario_id = $_SESSION['id'];

// Validar ID do chamado
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: home.php?erro=id_invalido');
    exit();
}

$chamado_id = intval($_GET['id']);

// Verificar se o usuário é dono do chamado e se está resolvido
$stmt = $conn->prepare("
    SELECT id, status 
    FROM chamados 
    WHERE id = ? AND cliente_id = ?
");

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

// Verificar se o chamado está no status "resolvido"
if ($chamado['status'] !== 'resolvido') {
    $conn->close();
    header('Location: detalhe_chamado.php?id=' . $chamado_id . '&erro=status_invalido');
    exit();
}

// Fechar o chamado
try {
    $stmt = $conn->prepare("
        UPDATE chamados 
        SET status = 'fechado', 
            data_fechamento = CURRENT_TIMESTAMP,
            data_ultima_atualizacao = CURRENT_TIMESTAMP
        WHERE id = ?
    ");
    
    $stmt->bind_param("i", $chamado_id);
    
    if (!$stmt->execute()) {
        throw new Exception("Erro ao fechar chamado: " . $stmt->error);
    }
    
    $stmt->close();
    
    // Registrar no histórico
    $stmt = $conn->prepare("
        INSERT INTO historico_chamados 
        (chamado_id, usuario_id, status_anterior, status_novo, comentario) 
        VALUES (?, ?, 'resolvido', 'fechado', 'Chamado fechado pelo cliente após confirmação de resolução')
    ");
    
    $stmt->bind_param("ii", $chamado_id, $usuario_id);
    $stmt->execute();
    $stmt->close();

    // Enviar email para o técnico notificando
    try {
        // Buscar dados completos do chamado
        $stmt = $conn->prepare("
            SELECT c.*, cl.nome as cliente_nome, cl.email as cliente_email
            FROM chamados c
            INNER JOIN clientes cl ON c.cliente_id = cl.id
            WHERE c.id = ?
        ");
        $stmt->bind_param("i", $chamado_id);
        $stmt->execute();
        $chamado_completo = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // Se houver técnico atribuído, notificar
        if ($chamado_completo['tecnico_id']) {
            $stmt = $conn->prepare("SELECT nome, email FROM tecnicos WHERE id = ?");
            $stmt->bind_param("i", $chamado_completo['tecnico_id']);
            $stmt->execute();
            $tecnico = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($tecnico) {
                require_once __DIR__ . '/../config/EmailService.php';
                $emailService = new EmailService();
                $emailService->notificarClienteAtualizacao(
                    ['nome' => $chamado_completo['cliente_nome'], 'email' => $chamado_completo['cliente_email']],
                    $chamado_completo,
                    'O chamado foi fechado com sucesso.'
                );
            }
        }
    } catch (Exception $e) {
        error_log("Erro ao enviar email de notificação: " . $e->getMessage());
        // Não bloquear o fluxo se falhar o envio do email
    }

    $conn->close();
    
    // Redirecionar com sucesso
    header('Location: detalhe_chamado.php?id=' . $chamado_id . '&sucesso=chamado_fechado');
    exit();
    
} catch (Exception $e) {
    $conn->close();
    error_log("Erro ao fechar chamado: " . $e->getMessage());
    header('Location: detalhe_chamado.php?id=' . $chamado_id . '&erro=erro_servidor');
    exit();
}
?>