<?php
require_once '../controller/auth_middleware.php';
require_once '../controller/historico_chamados.php';
require_once '../config/bandoDeDados/conexao.php';

requireCliente();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: home.php?erro=metodo_invalido');
    exit();
}

requireCsrfToken();

$conn = getConnection();
$usuario_id = $_SESSION['id'];

// Validar ID do chamado
if (!isset($_POST['id']) || empty($_POST['id'])) {
    header('Location: home.php?erro=id_invalido');
    exit();
}

$chamado_id = intval($_POST['id']);

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
    header('Location: visualizar_chamado.php?id=' . $chamado_id . '&erro=status_invalido');
    exit();
}

// Fechar o chamado
try {
    $conn->begin_transaction();

    $stmt = $conn->prepare("
        UPDATE chamados
        SET status = 'fechado',
            data_fechamento = CURRENT_TIMESTAMP
        WHERE id = ?
    ");

    $stmt->bind_param("i", $chamado_id);

    if (!$stmt->execute()) {
        throw new Exception("Erro ao fechar chamado: " . $stmt->error);
    }

    $stmt->close();

    // Registrar no histórico
    registrarHistoricoStatus($conn, $chamado_id, $usuario_id, 'resolvido', 'fechado', 'Chamado fechado pelo cliente após confirmação de resolução');

    $conn->commit();

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
    header('Location: visualizar_chamado.php?id=' . $chamado_id . '&sucesso=chamado_fechado');
    exit();
    
} catch (Exception $e) {
    $conn->rollback();
    $conn->close();
    error_log("Erro ao fechar chamado: " . $e->getMessage());
    header('Location: visualizar_chamado.php?id=' . $chamado_id . '&erro=erro_servidor');
    exit();
}
?>