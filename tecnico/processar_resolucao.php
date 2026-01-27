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
$tecnico_id = $_SESSION['usuario_id'];
$chamado_id = intval($_POST['chamado_id'] ?? 0);

// Validações iniciais
if ($chamado_id === 0) {
    header('Location: meus_chamados.php?erro=chamado_invalido');
    exit();
}

try {
    $conn->begin_transaction();

    // Verificar se chamado existe e está atribuído ao técnico
    $stmt = $conn->prepare("
        SELECT * FROM chamados
        WHERE id = ? AND tecnico_id = ? AND status != 'resolvido'
    ");
    $stmt->bind_param("ii", $chamado_id, $tecnico_id);
    $stmt->execute();
    $chamado = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$chamado) {
        throw new Exception("Chamado não encontrado, não atribuído a você, ou já foi resolvido");
    }

    // Coletar dados do formulário
    $historico_atendimento = trim($_POST['historico_atendimento'] ?? '');
    $stylemanager_software = isset($_POST['stylemanager_software']) ? 1 : 0;
    $pagamento_forma = $stylemanager_software ? null : ($_POST['pagamento_forma'] ?? '');

    // Validações de campos obrigatórios
    if (empty($historico_atendimento)) {
        throw new Exception("O histórico do atendimento é obrigatório");
    }

    if (strlen($historico_atendimento) < 50) {
        throw new Exception("O histórico do atendimento deve ter pelo menos 50 caracteres. Seja mais detalhado!");
    }

    // Validar pagamento (se não for StyleManager software)
    if (!$stylemanager_software && empty($pagamento_forma)) {
        throw new Exception("A forma de pagamento é obrigatória para serviços que não sejam StyleManager Software");
    }

    // Validar formas de pagamento válidas
    if (!$stylemanager_software) {
        $formas_validas = ['PIX', 'Dinheiro', 'Cartão', 'Débito'];
        if (!in_array($pagamento_forma, $formas_validas)) {
            throw new Exception("Forma de pagamento inválida");
        }
    }

    // Validar fotos
    if (!isset($_FILES['fotos']) || empty($_FILES['fotos']['name'][0])) {
        throw new Exception("É obrigatório adicionar pelo menos 1 foto do serviço realizado");
    }

    // ============================================
    // PROCESSAR UPLOAD DE FOTOS
    // ============================================

    $upload_dir = '../uploads/chamados/' . $chamado_id . '/';

    // Criar diretório se não existir
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0755, true)) {
            throw new Exception("Erro ao criar diretório de upload");
        }
    }

    $fotos_salvas = [];
    $total_fotos = count($_FILES['fotos']['name']);

    for ($i = 0; $i < $total_fotos; $i++) {
        if ($_FILES['fotos']['error'][$i] !== UPLOAD_ERR_OK) {
            continue; // Pular arquivos com erro
        }

        $file_name = $_FILES['fotos']['name'][$i];
        $file_tmp = $_FILES['fotos']['tmp_name'][$i];
        $file_size = $_FILES['fotos']['size'][$i];
        $file_error = $_FILES['fotos']['error'][$i];

        // Validar tamanho (5MB)
        if ($file_size > 5 * 1024 * 1024) {
            throw new Exception("Arquivo $file_name é muito grande. Máximo: 5MB");
        }

        // Validar tipo de arquivo
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_tmp);
        finfo_close($finfo);

        if (!in_array($mime_type, $allowed_types)) {
            throw new Exception("Arquivo $file_name não é uma imagem válida");
        }

        // Gerar nome único
        $extensao = pathinfo($file_name, PATHINFO_EXTENSION);
        $nome_unico = 'foto_' . time() . '_' . uniqid() . '.' . $extensao;
        $caminho_completo = $upload_dir . $nome_unico;
        $caminho_relativo = 'uploads/chamados/' . $chamado_id . '/' . $nome_unico;

        // Mover arquivo
        if (!move_uploaded_file($file_tmp, $caminho_completo)) {
            throw new Exception("Erro ao salvar arquivo $file_name");
        }

        // Registrar no banco
        $stmt = $conn->prepare("
            INSERT INTO chamado_fotos (chamado_id, tecnico_id, nome_arquivo, caminho_arquivo, descricao)
            VALUES (?, ?, ?, ?, ?)
        ");
        $descricao = "Foto do serviço realizado";
        $stmt->bind_param("iisss", $chamado_id, $tecnico_id, $nome_unico, $caminho_relativo, $descricao);
        $stmt->execute();
        $stmt->close();

        $fotos_salvas[] = $nome_unico;
    }

    if (count($fotos_salvas) === 0) {
        throw new Exception("Nenhuma foto foi enviada com sucesso. Adicione pelo menos 1 foto!");
    }

    // ============================================
    // ATUALIZAR CHAMADO COMO RESOLVIDO
    // ============================================

    $stmt = $conn->prepare("
        UPDATE chamados
        SET
            status = 'resolvido',
            historico_atendimento = ?,
            stylemanager_software = ?,
            pagamento_forma = ?,
            data_resolucao = NOW(),
            data_fechamento = NOW()
        WHERE id = ?
    ");
    $stmt->bind_param("sisi", $historico_atendimento, $stylemanager_software, $pagamento_forma, $chamado_id);
    $stmt->execute();
    $stmt->close();

    // O trigger calcular_tempo_atendimento será executado automaticamente

    // ============================================
    // REGISTRAR CONCLUSÃO NAS ATUALIZAÇÕES
    // ============================================

    $descricao_conclusao = "Chamado resolvido pelo técnico. ";
    if ($stylemanager_software) {
        $descricao_conclusao .= "Tipo: StyleManager Software (sem cobrança). ";
    } else {
        $descricao_conclusao .= "Pagamento: $pagamento_forma. ";
    }
    $descricao_conclusao .= count($fotos_salvas) . " foto(s) anexada(s).";

    $stmt = $conn->prepare("
        INSERT INTO chamado_atualizacoes (chamado_id, tecnico_id, tipo_atualizacao, descricao)
        VALUES (?, ?, 'conclusao', ?)
    ");
    $stmt->bind_param("iis", $chamado_id, $tecnico_id, $descricao_conclusao);
    $stmt->execute();
    $stmt->close();

    // ============================================
    // REGISTRAR NO HISTÓRICO DE CHAMADOS
    // ============================================

    $historico_comentario = "Chamado resolvido. ";
    $historico_comentario .= $stylemanager_software ? "StyleManager Software (sem cobrança)" : "Pago via $pagamento_forma";

    $stmt = $conn->prepare("
        INSERT INTO historico_chamados (chamado_id, usuario_id, status_anterior, status_novo, comentario)
        VALUES (?, ?, ?, 'resolvido', ?)
    ");
    $status_anterior = $chamado['status'];
    $stmt->bind_param("iiss", $chamado_id, $tecnico_id, $status_anterior, $historico_comentario);
    $stmt->execute();
    $stmt->close();

    // ============================================
    // LOG DO SISTEMA
    // ============================================

    $log_acao = "Resolveu chamado #$chamado_id. ";
    $log_acao .= $stylemanager_software ? "StyleManager Software. " : "Pagamento: $pagamento_forma. ";
    $log_acao .= count($fotos_salvas) . " foto(s).";

    $stmt = $conn->prepare("INSERT INTO logs_sistema (usuario_id, acao) VALUES (?, ?)");
    $stmt->bind_param("is", $tecnico_id, $log_acao);
    $stmt->execute();
    $stmt->close();

    $conn->commit();

    // Redirecionar com sucesso
    header('Location: meus_chamados.php?sucesso=resolvido');
    exit();

} catch (Exception $e) {
    $conn->rollback();

    // Limpar fotos salvas em caso de erro
    if (isset($fotos_salvas) && !empty($fotos_salvas)) {
        foreach ($fotos_salvas as $foto) {
            $caminho = $upload_dir . $foto;
            if (file_exists($caminho)) {
                unlink($caminho);
            }
        }
    }

    error_log("Erro ao resolver chamado: " . $e->getMessage());
    header('Location: resolver_chamado.php?id=' . $chamado_id . '&erro=' . urlencode($e->getMessage()));
    exit();
}
?>
