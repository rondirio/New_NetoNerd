<?php
require_once '../controller/auth_middleware.php';
include_once '../config/bandoDeDados/conexao.php';

requireAdmin();

$conn = getConnection();

// Buscar técnicos para atribuição
$tecnicos = [];
$sql_tecnicos = "SELECT id, nome FROM tecnicos WHERE status_tecnico = 'Active' ORDER BY nome";
$result_tecnicos = $conn->query($sql_tecnicos);
if ($result_tecnicos) {
    while ($row = $result_tecnicos->fetch_assoc()) {
        $tecnicos[] = $row;
    }
}

// Categorias disponíveis (tabela categorias_chamado, não mais lista hardcoded)
$categorias = [];
$sql_categorias = "SELECT id, nome FROM categorias_chamado WHERE ativo = 1 ORDER BY nome";
$result_categorias = $conn->query($sql_categorias);
if ($result_categorias) {
    while ($row = $result_categorias->fetch_assoc()) {
        $categorias[] = $row;
    }
}

$mensagem = '';
$tipo_mensagem = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();
    $transacao_aberta = false;
    try {
        $tipo_cliente = $_POST['tipo_cliente'] ?? 'novo';
        $cliente_id = null;
        $cliente_nome = null;
        $cliente_email = null;
        $cliente_telefone = null;

        if ($tipo_cliente === 'existente' && !empty($_POST['cliente_id'])) {
            $cliente_id = intval($_POST['cliente_id']);
            // Buscar dados do cliente
            $stmt = $conn->prepare("SELECT nome FROM clientes WHERE id = ?");
            $stmt->bind_param("i", $cliente_id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $cliente_nome = $row['nome'];
            }
            $stmt->close();
        } else {
            // Cliente não registrado
            $cliente_nome = trim($_POST['cliente_nome']);
            $cliente_email = trim($_POST['cliente_email']);
            $cliente_telefone = trim($_POST['cliente_telefone']);
        }

        $titulo = str_replace('#', '-', trim($_POST['titulo']));
        $categoria_id = !empty($_POST['categoria_id']) ? intval($_POST['categoria_id']) : null;
        $descricao = str_replace('#', '-', trim($_POST['descricao']));
        $prioridade = $_POST['prioridade'] ?? 'media';
        $tecnico_id = !empty($_POST['tecnico_id']) ? intval($_POST['tecnico_id']) : null;

        if (!$categoria_id) {
            throw new Exception("Categoria é obrigatória.");
        }

        $stmt_cat = $conn->prepare("SELECT nome FROM categorias_chamado WHERE id = ?");
        $stmt_cat->bind_param("i", $categoria_id);
        $stmt_cat->execute();
        $categoria_row = $stmt_cat->get_result()->fetch_assoc();
        $stmt_cat->close();

        if (!$categoria_row) {
            throw new Exception("Categoria inválida.");
        }

        $categoria = $categoria_row['nome'];
        $admin_id = $_SESSION['id'];

        $conn->begin_transaction();
        $transacao_aberta = true;

        // Gerar protocolo. FOR UPDATE trava a linha de maior protocolo do ano
        // contra leituras concorrentes; chamados.protocolo tem UNIQUE como
        // segunda rede de segurança (retry abaixo se ainda assim colidir).
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

            $ultimo_protocolo = isset($row['ultimo_protocolo']) ? intval(substr($row['ultimo_protocolo'], 4)) : 0;
            $novo_protocolo = $ultimo_protocolo + 1;
            $protocolo = $ano_atual . str_pad($novo_protocolo, 4, '0', STR_PAD_LEFT);

            // Inserir chamado
            $sql = "INSERT INTO chamados (cliente_id, cliente_nome, cliente_email, cliente_telefone, titulo, categoria, categoria_id, descricao, protocolo, nome_usuario, prioridade, tecnico_id, criado_por_admin)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Erro ao preparar query: " . $conn->error);
            }

            $stmt->bind_param("isssssisssiii",
                $cliente_id,
                $cliente_nome,
                $cliente_email,
                $cliente_telefone,
                $titulo,
                $categoria,
                $categoria_id,
                $descricao,
                $protocolo,
                $cliente_nome,
                $prioridade,
                $tecnico_id,
                $admin_id
            );

            if ($stmt->execute()) {
                $chamado_id = $conn->insert_id;
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

        // Processar upload de arquivos
        if (isset($_FILES['anexos']) && !empty($_FILES['anexos']['name'][0])) {
            $upload_dir = '../uploads/anexos/';
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
            $tamanho_maximo = 10 * 1024 * 1024;

            $sql_anexo = "INSERT INTO anexos_chamado (chamado_id, nome_arquivo, nome_original, caminho_arquivo, tipo_mime, tamanho_bytes, usuario_upload_id, tipo_usuario)
                          VALUES (?, ?, ?, ?, ?, ?, ?, 'admin')";
            $stmt_anexo = $conn->prepare($sql_anexo);

            if ($stmt_anexo) {
                $total_arquivos = count($_FILES['anexos']['name']);
                for ($i = 0; $i < $total_arquivos; $i++) {
                    if ($_FILES['anexos']['error'][$i] === UPLOAD_ERR_OK) {
                        $nome_original = $_FILES['anexos']['name'][$i];
                        $tamanho = $_FILES['anexos']['size'][$i];
                        $tmp_name = $_FILES['anexos']['tmp_name'][$i];

                        // Validar usando finfo (magic bytes), não o Content-Type declarado pelo cliente (falsificável)
                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                        $tipo_arquivo = $finfo->file($tmp_name);

                        if (in_array($tipo_arquivo, $tipos_permitidos) && $tamanho <= $tamanho_maximo) {
                            $extensao = pathinfo($nome_original, PATHINFO_EXTENSION);
                            $nome_arquivo = $chamado_id . '_' . uniqid() . '.' . $extensao;
                            $caminho_arquivo = $upload_dir . $nome_arquivo;

                            if (move_uploaded_file($tmp_name, $caminho_arquivo)) {
                                $stmt_anexo->bind_param("issssii", $chamado_id, $nome_arquivo, $nome_original, $caminho_arquivo, $tipo_arquivo, $tamanho, $_SESSION['id']);
                                $stmt_anexo->execute();
                            }
                        }
                    }
                }
                $stmt_anexo->close();
            }
        }

        $conn->commit();
        $transacao_aberta = false;

        $mensagem = "Chamado criado com sucesso! Protocolo: #" . $protocolo;
        $tipo_mensagem = 'success';

    } catch (Exception $e) {
        if ($transacao_aberta) {
            $conn->rollback();
        }
        $mensagem = "Erro ao criar chamado: " . $e->getMessage();
        $tipo_mensagem = 'danger';
    }
}

$page_title = "Abrir Chamado - NetoNerd ITSM";
$extra_css = '<style>
    .nn-cliente-toggle {
        display: flex;
        gap: 15px;
        margin-bottom: 20px;
    }

    .nn-cliente-toggle .nn-btn {
        flex: 1;
        justify-content: center;
    }

    .nn-cliente-toggle .nn-btn.active {
        background: var(--gradient-primary);
        color: white;
    }

    .nn-cliente-section {
        display: none;
        padding: 20px;
        background: var(--bg-light);
        border-radius: var(--radius-md);
        margin-bottom: 20px;
    }

    .nn-cliente-section.active {
        display: block;
    }

    .nn-prioridade-card {
        border: 2px solid var(--bg-lighter);
        border-radius: var(--radius-md);
        padding: 15px;
        cursor: pointer;
        transition: all 0.3s;
        text-align: center;
        display: block;
    }

    .nn-prioridade-card:hover {
        border-color: var(--primary-blue);
    }

    .nn-prioridade-card.selected {
        border-color: var(--primary-blue);
        background: rgba(11, 61, 145, 0.08);
    }

    .nn-prioridade-card input {
        display: none;
    }

    .nn-prioridade-icon {
        font-size: 2rem;
        margin-bottom: 10px;
    }

    .nn-drop-zone {
        border: 2px dashed var(--bg-lighter);
        border-radius: var(--radius-md);
        padding: 40px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }

    .nn-drop-zone:hover, .nn-drop-zone.dragover {
        border-color: var(--primary-blue);
        background: rgba(11, 61, 145, 0.05);
    }

    .nn-file-item-simple {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 10px;
        background: var(--bg-light);
        border-radius: var(--radius-sm);
        margin-bottom: 5px;
    }
</style>';
require_once '../includes/header.php';
?>

<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h1 class="nn-card-title">
                    <i class="fas fa-plus-circle"></i>
                    Abrir Novo Chamado
                </h1>
            </div>
            <div class="nn-card-body">
                <p class="nn-text-medium">Crie um chamado para cliente registrado ou não registrado</p>
            </div>
        </div>

        <?php if ($mensagem): ?>
            <div class="nn-alert nn-alert-<?php echo $tipo_mensagem === 'danger' ? 'danger' : 'success'; ?> nn-animate-fade">
                <i class="fas fa-<?php echo $tipo_mensagem === 'danger' ? 'exclamation-circle' : 'check-circle'; ?>"></i>
                <?php echo htmlspecialchars($mensagem); ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" id="formChamado">
            <?php echo csrfField(); ?>
            <div class="nn-card">
                <div class="nn-card-header">
                    <h2 class="nn-card-title">
                        <i class="fas fa-user"></i>
                        Dados do Cliente
                    </h2>
                </div>
                <div class="nn-card-body">
                    <div class="nn-cliente-toggle">
                        <button type="button" class="nn-btn nn-btn-secondary active" onclick="toggleCliente('novo')">
                            <i class="fas fa-user-plus"></i>
                            Cliente Novo / Não Registrado
                        </button>
                        <button type="button" class="nn-btn nn-btn-secondary" onclick="toggleCliente('existente')">
                            <i class="fas fa-user-check"></i>
                            Cliente Registrado
                        </button>
                    </div>

                    <input type="hidden" name="tipo_cliente" id="tipo_cliente" value="novo">

                    <!-- Cliente Novo -->
                    <div class="nn-cliente-section active" id="cliente-novo">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="nn-form-group">
                                    <label class="nn-form-label" for="cliente_nome">Nome do Cliente *</label>
                                    <input type="text" class="nn-form-control" name="cliente_nome" id="cliente_nome" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="nn-form-group">
                                    <label class="nn-form-label" for="cliente_telefone">Telefone *</label>
                                    <input type="tel" class="nn-form-control" name="cliente_telefone" id="cliente_telefone" data-mask="phone" maxlength="15" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="nn-form-group">
                                    <label class="nn-form-label" for="cliente_email">Email (opcional)</label>
                                    <input type="email" class="nn-form-control" name="cliente_email" id="cliente_email">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cliente Existente -->
                    <div class="nn-cliente-section" id="cliente-existente">
                        <div class="nn-form-group" style="position: relative;">
                            <label class="nn-form-label" for="cliente_busca">Buscar Cliente * <small class="nn-text-light">(digite ao menos 3 letras do nome)</small></label>
                            <input type="text" class="nn-form-control" id="cliente_busca" autocomplete="off" placeholder="Nome do cliente...">
                            <input type="hidden" name="cliente_id" id="cliente_id">
                            <div id="cliente-resultados" class="nn-card" style="position: absolute; width: 100%; z-index: 1000; max-height: 250px; overflow-y: auto; padding: 0; display: none;"></div>
                        </div>
                        <div id="cliente-info" class="nn-alert nn-alert-info" style="display: none;">
                            <strong>Cliente selecionado:</strong> <span id="info-nome"></span><br>
                            <strong>Email:</strong> <span id="info-email"></span><br>
                            <strong>Telefone:</strong> <span id="info-telefone"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="nn-card">
                <div class="nn-card-header">
                    <h2 class="nn-card-title">
                        <i class="fas fa-clipboard-list"></i>
                        Detalhes do Chamado
                    </h2>
                </div>
                <div class="nn-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="nn-form-group">
                                <label class="nn-form-label" for="categoria_id">Categoria *</label>
                                <select class="nn-form-control" name="categoria_id" id="categoria_id" required>
                                    <option value="">-- Selecione --</option>
                                    <?php foreach ($categorias as $cat): ?>
                                        <option value="<?php echo $cat['id']; ?>">
                                            <?php echo htmlspecialchars($cat['nome']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="nn-form-group">
                                <label class="nn-form-label" for="tecnico_id">Técnico Responsável</label>
                                <select class="nn-form-control" name="tecnico_id" id="tecnico_id">
                                    <option value="">-- Atribuição automática --</option>
                                    <?php foreach ($tecnicos as $tecnico): ?>
                                        <option value="<?php echo $tecnico['id']; ?>">
                                            <?php echo htmlspecialchars($tecnico['nome']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="nn-form-group">
                        <label class="nn-form-label" for="titulo">Título do Chamado *</label>
                        <input type="text" class="nn-form-control" name="titulo" id="titulo" required maxlength="255"
                               placeholder="Ex: Computador não liga após queda de energia">
                    </div>

                    <div class="nn-form-group">
                        <label class="nn-form-label" for="descricao">Descrição do Problema *</label>
                        <textarea class="nn-form-control" name="descricao" id="descricao" rows="5" required maxlength="5000"
                                  placeholder="Descreva o problema detalhadamente..."></textarea>
                    </div>
                </div>
            </div>

            <div class="nn-card">
                <div class="nn-card-header">
                    <h2 class="nn-card-title">
                        <i class="fas fa-exclamation-triangle"></i>
                        Prioridade
                    </h2>
                </div>
                <div class="nn-card-body">
                    <div class="row">
                        <div class="col-md-3 col-6">
                            <label class="nn-prioridade-card">
                                <input type="radio" name="prioridade" value="baixa">
                                <div class="nn-prioridade-icon">🟢</div>
                                <div style="font-weight: 700;">Baixa</div>
                                <small class="nn-text-light">Pode aguardar</small>
                            </label>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="nn-prioridade-card selected">
                                <input type="radio" name="prioridade" value="media" checked>
                                <div class="nn-prioridade-icon">🟡</div>
                                <div style="font-weight: 700;">Média</div>
                                <small class="nn-text-light">Normal</small>
                            </label>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="nn-prioridade-card">
                                <input type="radio" name="prioridade" value="alta">
                                <div class="nn-prioridade-icon">🔴</div>
                                <div style="font-weight: 700;">Alta</div>
                                <small class="nn-text-light">Urgente</small>
                            </label>
                        </div>
                        <div class="col-md-3 col-6">
                            <label class="nn-prioridade-card">
                                <input type="radio" name="prioridade" value="critica">
                                <div class="nn-prioridade-icon">⚫</div>
                                <div style="font-weight: 700;">Crítica</div>
                                <small class="nn-text-light">Impede trabalho</small>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="nn-card">
                <div class="nn-card-header">
                    <h2 class="nn-card-title">
                        <i class="fas fa-paperclip"></i>
                        Anexos (opcional)
                    </h2>
                </div>
                <div class="nn-card-body">
                    <div class="nn-drop-zone" id="dropZone" onclick="document.getElementById('anexos').click()">
                        <i class="fas fa-cloud-upload-alt" style="font-size: 2.5rem; margin-bottom: 10px; color: var(--text-light);"></i>
                        <p>Arraste arquivos aqui ou clique para selecionar</p>
                        <small class="nn-text-light">Imagens, PDFs, DOC (máx. 10MB cada)</small>
                    </div>
                    <input type="file" name="anexos[]" id="anexos" multiple accept="image/*,.pdf,.doc,.docx,.txt" style="display: none">
                    <div id="fileList" class="nn-mt-2"></div>
                </div>
            </div>

            <div class="nn-d-flex nn-justify-between">
                <a href="dashboard.php" class="nn-btn nn-btn-secondary nn-btn-lg">
                    <i class="fas fa-arrow-left"></i>
                    Voltar
                </a>
                <button type="submit" class="nn-btn nn-btn-primary nn-btn-lg">
                    <i class="fas fa-paper-plane"></i>
                    Criar Chamado
                </button>
            </div>
        </form>

    </div>
</div>

<?php
$extra_js = '<script>
    function toggleCliente(tipo) {
        document.getElementById("tipo_cliente").value = tipo;

        document.querySelectorAll(".nn-cliente-toggle .nn-btn").forEach(function (btn) { btn.classList.remove("active"); });
        event.target.closest(".nn-btn").classList.add("active");

        document.getElementById("cliente-novo").classList.remove("active");
        document.getElementById("cliente-existente").classList.remove("active");
        document.getElementById("cliente-" + tipo).classList.add("active");

        if (tipo === "novo") {
            document.getElementById("cliente_nome").required = true;
            document.getElementById("cliente_telefone").required = true;
            document.getElementById("cliente_busca").required = false;
        } else {
            document.getElementById("cliente_nome").required = false;
            document.getElementById("cliente_telefone").required = false;
            document.getElementById("cliente_busca").required = true;
        }
    }

    // Busca de cliente com autocomplete (mínimo 3 letras)
    var buscaInput = document.getElementById("cliente_busca");
    var resultadosDiv = document.getElementById("cliente-resultados");
    var clienteIdInput = document.getElementById("cliente_id");
    var infoDiv = document.getElementById("cliente-info");
    var buscaTimeout = null;

    buscaInput.addEventListener("input", function () {
        var termo = this.value.trim();
        clienteIdInput.value = "";
        infoDiv.style.display = "none";

        clearTimeout(buscaTimeout);

        if (termo.length < 3) {
            resultadosDiv.innerHTML = "";
            resultadosDiv.style.display = "none";
            return;
        }

        buscaTimeout = setTimeout(function () {
            fetch("buscar_clientes.php?termo=" + encodeURIComponent(termo))
                .then(function (res) { return res.json(); })
                .then(function (clientes) {
                    resultadosDiv.innerHTML = "";
                    resultadosDiv.style.display = clientes.length ? "block" : "none";
                    clientes.forEach(function (cliente) {
                        var item = document.createElement("button");
                        item.type = "button";
                        item.className = "nn-btn nn-btn-secondary";
                        item.style.cssText = "width:100%; justify-content:flex-start; border-radius:0; text-align:left;";
                        item.textContent = cliente.nome + (cliente.email ? " - " + cliente.email : "");
                        item.addEventListener("click", function () {
                            clienteIdInput.value = cliente.id;
                            buscaInput.value = cliente.nome;
                            resultadosDiv.innerHTML = "";
                            resultadosDiv.style.display = "none";

                            document.getElementById("info-nome").textContent = cliente.nome;
                            document.getElementById("info-email").textContent = cliente.email || "Não informado";
                            document.getElementById("info-telefone").textContent = cliente.telefone || "Não informado";
                            infoDiv.style.display = "block";
                        });
                        resultadosDiv.appendChild(item);
                    });
                });
        }, 300);
    });

    // Prioridade cards
    document.querySelectorAll(".nn-prioridade-card").forEach(function (card) {
        card.addEventListener("click", function () {
            document.querySelectorAll(".nn-prioridade-card").forEach(function (c) { c.classList.remove("selected"); });
            this.classList.add("selected");
        });
    });

    // Drop zone
    var dropZone = document.getElementById("dropZone");
    var fileInput = document.getElementById("anexos");
    var fileList = document.getElementById("fileList");

    ["dragenter", "dragover", "dragleave", "drop"].forEach(function (eventName) {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ["dragenter", "dragover"].forEach(function (eventName) {
        dropZone.addEventListener(eventName, function () { dropZone.classList.add("dragover"); });
    });

    ["dragleave", "drop"].forEach(function (eventName) {
        dropZone.addEventListener(eventName, function () { dropZone.classList.remove("dragover"); });
    });

    dropZone.addEventListener("drop", function (e) {
        fileInput.files = e.dataTransfer.files;
        updateFileList();
    });

    fileInput.addEventListener("change", updateFileList);

    function updateFileList() {
        fileList.innerHTML = "";
        Array.from(fileInput.files).forEach(function (file) {
            var div = document.createElement("div");
            div.className = "nn-file-item-simple";
            div.innerHTML = "<span><i class=\\"fas fa-file\\"></i> " + file.name + " (" + (file.size / 1024).toFixed(1) + " KB)</span>";
            fileList.appendChild(div);
        });
    }
</script>';
require_once '../includes/footer.php';
?>
