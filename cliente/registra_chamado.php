<?php
session_start();
require_once '../config/bandoDeDados/conexao.php';

// Verificar autenticação
if (!isset($_SESSION['id'])) {
    header('Location: ../publics/login.php?erro=nao_autenticado');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: abrir_chamado.php');
    exit();
}

try {
    $conn = getConnection();

    // Captura e sanitiza dados do formulário
    $titulo      = str_replace('#', '-', trim($_POST['titulo'] ?? ''));
    $categoria   = str_replace('#', '-', trim($_POST['categoria'] ?? ''));
    $subcategoria = trim($_POST['subcategoria'] ?? '');
    $descricao   = str_replace('#', '-', trim($_POST['descricao'] ?? ''));
    $prioridade  = trim($_POST['prioridade'] ?? 'media');
    $usuario     = str_replace('#', '_', trim($_POST['usuario'] ?? ''));

    // Validação de campos obrigatórios
    if (empty($titulo) || empty($categoria) || empty($descricao)) {
        header('Location: abrir_chamado.php?erro=campos_obrigatorios');
        exit();
    }

    // Garantir que prioridade é um valor válido
    $prioridades_validas = ['baixa', 'media', 'alta', 'critica'];
    if (!in_array($prioridade, $prioridades_validas)) {
        $prioridade = 'media';
    }

    // Gerar protocolo sequencial com ano
    $query = "SELECT MAX(protocolo) as ultimo_protocolo FROM chamados";
    $result = $conn->query($query);
    if (!$result) {
        throw new Exception("Erro ao buscar protocolo: " . $conn->error);
    }
    $row = $result->fetch_assoc();
    $ultimo = $row['ultimo_protocolo'] ? intval(substr($row['ultimo_protocolo'], 4)) : 0;
    $protocolo = date('Y') . str_pad($ultimo + 1, 4, '0', STR_PAD_LEFT);

    // Inserir chamado incluindo prioridade e subcategoria
    $sql = "INSERT INTO chamados (cliente_id, titulo, categoria, descricao, protocolo, nome_usuario, prioridade)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Erro ao preparar query: " . $conn->error);
    }

    $titulo_completo = $subcategoria ? "$titulo — $subcategoria" : $titulo;
    $stmt->bind_param("issssss",
        $_SESSION['id'],
        $titulo_completo,
        $categoria,
        $descricao,
        $protocolo,
        $usuario,
        $prioridade
    );

    if (!$stmt->execute()) {
        throw new Exception("Erro ao inserir chamado: " . $stmt->error);
    }

    $chamado_id = $stmt->insert_id;
    $stmt->close();

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
            "INSERT INTO anexos_chamado (chamado_id, nome_arquivo, nome_original, caminho_arquivo, tipo_arquivo, tamanho)
             VALUES (?, ?, ?, ?, ?, ?)"
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
                    $stmt_anexo->bind_param("issssi",
                        $chamado_id,
                        $nome_salvo,
                        $nome_original,
                        $caminho,
                        $tipo_real,
                        $tamanho
                    );
                    $stmt_anexo->execute();
                }
            }
            $stmt_anexo->close();
        }
    }

    $conn->close();

    // Redirecionar para os chamados com protocolo visível
    header('Location: meus_chamados.php?sucesso=chamado_criado&protocolo=' . urlencode($protocolo));
    exit();

} catch (Exception $e) {
    error_log("registra_chamado: " . $e->getMessage());
    header('Location: abrir_chamado.php?erro=erro_interno');
    exit();
}
