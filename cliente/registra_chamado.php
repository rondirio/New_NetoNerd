<?php
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireCliente();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: abrir_chamado.php');
    exit();
}

requireCsrfToken();

$transacao_aberta = false;

try {
    $conn = getConnection();

    // Captura e sanitiza dados do formulário
    $titulo      = str_replace('#', '-', trim($_POST['titulo'] ?? ''));
    $categoria_id = !empty($_POST['categoria_id']) ? intval($_POST['categoria_id']) : null;
    $descricao   = str_replace('#', '-', trim($_POST['descricao'] ?? ''));
    $prioridade  = trim($_POST['prioridade'] ?? 'media');
    $usuario     = str_replace('#', '_', trim($_POST['usuario'] ?? ''));

    // Validação de campos obrigatórios
    if (empty($titulo) || empty($categoria_id) || empty($descricao)) {
        header('Location: abrir_chamado.php?erro=campos_obrigatorios');
        exit();
    }

    // Garantir que prioridade é um valor válido
    $prioridades_validas = ['baixa', 'media', 'alta', 'critica'];
    if (!in_array($prioridade, $prioridades_validas)) {
        $prioridade = 'media';
    }

    $stmt_cat = $conn->prepare("SELECT nome FROM categorias_chamado WHERE id = ?");
    $stmt_cat->bind_param("i", $categoria_id);
    $stmt_cat->execute();
    $categoria_row = $stmt_cat->get_result()->fetch_assoc();
    $stmt_cat->close();

    if (!$categoria_row) {
        header('Location: abrir_chamado.php?erro=categoria_invalida');
        exit();
    }

    $categoria = $categoria_row['nome'];

    $conn->begin_transaction();
    $transacao_aberta = true;

    // Gerar protocolo sequencial com ano. FOR UPDATE trava a linha de maior
    // protocolo do ano contra leituras concorrentes; chamados.protocolo tem
    // UNIQUE como segunda rede de segurança (retry abaixo se ainda colidir).
    $tentativas_restantes = 3;
    $chamado_id = null;

    while (true) {
        $ano_atual = date('Y');
        $query = "SELECT MAX(protocolo) as ultimo_protocolo FROM chamados WHERE protocolo LIKE ? FOR UPDATE";
        $stmt_proto = $conn->prepare($query);
        $like = $ano_atual . '%';
        $stmt_proto->bind_param("s", $like);
        $stmt_proto->execute();
        $row = $stmt_proto->get_result()->fetch_assoc();
        $stmt_proto->close();

        $ultimo = $row['ultimo_protocolo'] ? intval(substr($row['ultimo_protocolo'], 4)) : 0;
        $protocolo = $ano_atual . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);

        // Inserir chamado incluindo prioridade e categoria
        $sql = "INSERT INTO chamados (cliente_id, titulo, categoria, categoria_id, descricao, protocolo, nome_usuario, prioridade)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erro ao preparar query: " . $conn->error);
        }

        $stmt->bind_param("ississss",
            $_SESSION['id'],
            $titulo,
            $categoria,
            $categoria_id,
            $descricao,
            $protocolo,
            $usuario,
            $prioridade
        );

        if ($stmt->execute()) {
            $chamado_id = $stmt->insert_id;
            $stmt->close();
            break;
        }

        $erro_duplicado = $conn->errno === 1062;
        $stmt->close();

        if (!$erro_duplicado || --$tentativas_restantes <= 0) {
            throw new Exception("Erro ao inserir chamado: " . $conn->error);
        }
        // Protocolo colidiu (corrida rara mesmo com FOR UPDATE) — tenta de novo.
    }

    // Processar anexos (se houver)
    if (!empty($_FILES['anexos']['name'][0])) {
        $upload_dir = '../uploads/chamados/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $tipos_permitidos = [
            'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp',
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'text/plain'
        ];
        $tamanho_maximo = 10 * 1024 * 1024; // 10MB

        $stmt_anexo = $conn->prepare(
            "INSERT INTO anexos_chamado (chamado_id, nome_arquivo, nome_original, caminho_arquivo, tipo_mime, tamanho_bytes, usuario_upload_id, tipo_usuario)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'cliente')"
        );

        if ($stmt_anexo) {
            foreach ($_FILES['anexos']['tmp_name'] as $i => $tmp_name) {
                if ($_FILES['anexos']['error'][$i] !== UPLOAD_ERR_OK) continue;

                $nome_original = basename($_FILES['anexos']['name'][$i]);
                $tipo          = $_FILES['anexos']['type'][$i];
                $tamanho       = $_FILES['anexos']['size'][$i];

                // Validar usando finfo (magic bytes), não o MIME do cliente
                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $tipo_real = $finfo->file($tmp_name);

                if (!in_array($tipo_real, $tipos_permitidos)) continue;
                if ($tamanho > $tamanho_maximo) continue;

                $extensao    = strtolower(pathinfo($nome_original, PATHINFO_EXTENSION));
                $nome_salvo  = $chamado_id . '_' . uniqid() . '.' . $extensao;
                $caminho     = $upload_dir . $nome_salvo;

                if (move_uploaded_file($tmp_name, $caminho)) {
                    $stmt_anexo->bind_param("issssii",
                        $chamado_id,
                        $nome_salvo,
                        $nome_original,
                        $caminho,
                        $tipo_real,
                        $tamanho,
                        $_SESSION['id']
                    );
                    $stmt_anexo->execute();
                }
            }
            $stmt_anexo->close();
        }
    }

    $conn->commit();
    $transacao_aberta = false;

    $conn->close();

    // Redirecionar para os chamados com protocolo visível
    header('Location: meus_chamados.php?sucesso=chamado_criado&protocolo=' . urlencode($protocolo));
    exit();

} catch (Exception $e) {
    if ($transacao_aberta) {
        $conn->rollback();
    }
    error_log("registra_chamado: " . $e->getMessage());
    header('Location: abrir_chamado.php?erro=erro_interno');
    exit();
}
