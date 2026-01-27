<?php
session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

// PROTEÇÃO: Apenas técnicos e admins
requireTecnico();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: meus_chamados.php?erro=metodo_invalido');
    exit();
}

$conn = getConnection();

$chamado_id = intval($_POST['chamado_id'] ?? 0);
$acao = $_POST['acao'] ?? '';
$tecnico_id = $_SESSION['usuario_id'];

// Validações
if ($chamado_id === 0 || empty($acao)) {
    header('Location: meus_chamados.php?erro=dados_invalidos');
    exit();
}

try {
    $conn->begin_transaction();

    // Verificar se chamado existe e está atribuído ao técnico
    $stmt = $conn->prepare("SELECT * FROM chamados WHERE id = ? AND tecnico_id = ?");
    $stmt->bind_param("ii", $chamado_id, $tecnico_id);
    $stmt->execute();
    $chamado = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$chamado) {
        throw new Exception("Chamado não encontrado ou não atribuído a você");
    }

    switch ($acao) {
        case 'iniciar':
            // Iniciar atendimento
            if ($chamado['status'] !== 'aberto') {
                throw new Exception("Apenas chamados abertos podem ser iniciados");
            }

            $stmt = $conn->prepare("
                UPDATE chamados
                SET status = 'em andamento',
                    data_inicio_atendimento = NOW()
                WHERE id = ?
            ");
            $stmt->bind_param("i", $chamado_id);
            $stmt->execute();
            $stmt->close();

            // Registrar atualização
            $stmt = $conn->prepare("
                INSERT INTO chamado_atualizacoes (chamado_id, tecnico_id, tipo_atualizacao, descricao)
                VALUES (?, ?, 'inicio_atendimento', 'Técnico iniciou o atendimento')
            ");
            $stmt->bind_param("ii", $chamado_id, $tecnico_id);
            $stmt->execute();
            $stmt->close();

            $mensagem_sucesso = 'iniciado';
            break;

        case 'pausar':
            // Marcar como pendente
            if ($chamado['status'] !== 'em andamento') {
                throw new Exception("Apenas chamados em andamento podem ser pausados");
            }

            $stmt = $conn->prepare("UPDATE chamados SET status = 'pendente' WHERE id = ?");
            $stmt->bind_param("i", $chamado_id);
            $stmt->execute();
            $stmt->close();

            // Registrar atualização
            $stmt = $conn->prepare("
                INSERT INTO chamado_atualizacoes (chamado_id, tecnico_id, tipo_atualizacao, descricao)
                VALUES (?, ?, 'pausa', 'Atendimento pausado pelo técnico')
            ");
            $stmt->bind_param("ii", $chamado_id, $tecnico_id);
            $stmt->execute();
            $stmt->close();

            $mensagem_sucesso = 'atualizado';
            break;

        case 'retomar':
            // Retomar atendimento
            if ($chamado['status'] !== 'pendente') {
                throw new Exception("Apenas chamados pendentes podem ser retomados");
            }

            $stmt = $conn->prepare("UPDATE chamados SET status = 'em andamento' WHERE id = ?");
            $stmt->bind_param("i", $chamado_id);
            $stmt->execute();
            $stmt->close();

            // Registrar atualização
            $stmt = $conn->prepare("
                INSERT INTO chamado_atualizacoes (chamado_id, tecnico_id, tipo_atualizacao, descricao)
                VALUES (?, ?, 'inicio_atendimento', 'Atendimento retomado pelo técnico')
            ");
            $stmt->bind_param("ii", $chamado_id, $tecnico_id);
            $stmt->execute();
            $stmt->close();

            $mensagem_sucesso = 'iniciado';
            break;

        case 'atualizar':
            // Adicionar atualização
            $tipo_atualizacao = $_POST['tipo_atualizacao'] ?? '';
            $descricao = trim($_POST['descricao'] ?? '');

            if (empty($tipo_atualizacao) || empty($descricao)) {
                throw new Exception("Tipo e descrição são obrigatórios");
            }

            $stmt = $conn->prepare("
                INSERT INTO chamado_atualizacoes (chamado_id, tecnico_id, tipo_atualizacao, descricao)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->bind_param("iiss", $chamado_id, $tecnico_id, $tipo_atualizacao, $descricao);
            $stmt->execute();
            $stmt->close();

            // Se for "aguardando_cliente" ou "necessita_peca", mudar status para pendente
            if (in_array($tipo_atualizacao, ['aguardando_cliente', 'necessita_peca'])) {
                $stmt = $conn->prepare("UPDATE chamados SET status = 'pendente' WHERE id = ?");
                $stmt->bind_param("i", $chamado_id);
                $stmt->execute();
                $stmt->close();
            }

            $mensagem_sucesso = 'atualizado';
            break;

        default:
            throw new Exception("Ação inválida");
    }

    // Log do sistema
    $log_acao = "Técnico executou ação '$acao' no chamado #$chamado_id";
    $stmt = $conn->prepare("INSERT INTO logs_sistema (usuario_id, acao) VALUES (?, ?)");
    $stmt->bind_param("is", $tecnico_id, $log_acao);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    header("Location: meus_chamados.php?sucesso=$mensagem_sucesso");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    error_log("Erro ao processar chamado: " . $e->getMessage());
    header('Location: meus_chamados.php?erro=' . urlencode($e->getMessage()));
    exit();
}
?>
