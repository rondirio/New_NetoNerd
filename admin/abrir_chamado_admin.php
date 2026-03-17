<?php
session_start();
include_once '../config/bandoDeDados/conexao.php';

// Buscar clientes existentes para autocompletar
$clientes = [];
$sql_clientes = "SELECT id, nome, email, telefone FROM clientes ORDER BY nome";
$result_clientes = $conn->query($sql_clientes);
if ($result_clientes) {
    while ($row = $result_clientes->fetch_assoc()) {
        $clientes[] = $row;
    }
}

// Buscar técnicos para atribuição
$tecnicos = [];
$sql_tecnicos = "SELECT id, nome FROM tecnicos WHERE status_tecnico = 'Active' ORDER BY nome";
$result_tecnicos = $conn->query($sql_tecnicos);
if ($result_tecnicos) {
    while ($row = $result_tecnicos->fetch_assoc()) {
        $tecnicos[] = $row;
    }
}

// Categorias disponíveis
$categorias = [
    'Hardware' => ['Computador não liga', 'Tela quebrada', 'Teclado com defeito', 'Mouse não funciona', 'Periféricos'],
    'Software' => ['Instalação de programa', 'Atualização', 'Erro de sistema', 'Lentidão', 'Vírus/Malware'],
    'Rede' => ['Internet lenta', 'Wi-Fi não conecta', 'Sem acesso à rede', 'Problema de VPN', 'Configuração de rede'],
    'Segurança' => ['Antivírus', 'Firewall', 'Backup', 'Recuperação de dados', 'Criptografia'],
    'Impressão' => ['Impressora offline', 'Problema de driver', 'Papel atolado', 'Qualidade de impressão'],
    'Email' => ['Não recebe emails', 'Erro ao enviar', 'Configuração de conta', 'Outlook/Thunderbird'],
    'Outros' => ['Consultoria', 'Treinamento', 'Suporte geral', 'Dúvidas']
];

$mensagem = '';
$tipo_mensagem = '';

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        $categoria = str_replace('#', '-', trim($_POST['categoria']));
        $descricao = str_replace('#', '-', trim($_POST['descricao']));
        $prioridade = $_POST['prioridade'] ?? 'media';
        $tecnico_id = !empty($_POST['tecnico_id']) ? intval($_POST['tecnico_id']) : null;

        // Gerar protocolo
        $query = "SELECT MAX(protocolo) as ultimo_protocolo FROM chamados";
        $result = $conn->query($query);
        $row = $result->fetch_assoc();
        $ultimo_protocolo = isset($row['ultimo_protocolo']) ? intval(substr($row['ultimo_protocolo'], 4)) : 0;
        $novo_protocolo = $ultimo_protocolo + 1;
        $ano_atual = date('Y');
        $protocolo = $ano_atual . str_pad($novo_protocolo, 4, '0', STR_PAD_LEFT);

        // Inserir chamado
        $sql = "INSERT INTO chamados (cliente_id, cliente_nome, cliente_email, cliente_telefone, titulo, categoria, descricao, protocolo, nome_usuario, prioridade, tecnico_id, criado_por_admin)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Erro ao preparar query: " . $conn->error);
        }

        $admin_id = $_SESSION['id'] ?? 1;
        $stmt->bind_param("isssssssssii",
            $cliente_id,
            $cliente_nome,
            $cliente_email,
            $cliente_telefone,
            $titulo,
            $categoria,
            $descricao,
            $protocolo,
            $cliente_nome,
            $prioridade,
            $tecnico_id,
            $admin_id
        );

        if (!$stmt->execute()) {
            throw new Exception("Erro ao inserir chamado: " . $stmt->error);
        }

        $chamado_id = $conn->insert_id;
        $stmt->close();

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

            $sql_anexo = "INSERT INTO anexos_chamado (chamado_id, nome_arquivo, nome_original, caminho_arquivo, tipo_arquivo, tamanho)
                          VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_anexo = $conn->prepare($sql_anexo);

            if ($stmt_anexo) {
                $total_arquivos = count($_FILES['anexos']['name']);
                for ($i = 0; $i < $total_arquivos; $i++) {
                    if ($_FILES['anexos']['error'][$i] === UPLOAD_ERR_OK) {
                        $nome_original = $_FILES['anexos']['name'][$i];
                        $tipo_arquivo = $_FILES['anexos']['type'][$i];
                        $tamanho = $_FILES['anexos']['size'][$i];
                        $tmp_name = $_FILES['anexos']['tmp_name'][$i];

                        if (in_array($tipo_arquivo, $tipos_permitidos) && $tamanho <= $tamanho_maximo) {
                            $extensao = pathinfo($nome_original, PATHINFO_EXTENSION);
                            $nome_arquivo = $chamado_id . '_' . uniqid() . '.' . $extensao;
                            $caminho_arquivo = $upload_dir . $nome_arquivo;

                            if (move_uploaded_file($tmp_name, $caminho_arquivo)) {
                                $stmt_anexo->bind_param("issssi", $chamado_id, $nome_arquivo, $nome_original, $caminho_arquivo, $tipo_arquivo, $tamanho);
                                $stmt_anexo->execute();
                            }
                        }
                    }
                }
                $stmt_anexo->close();
            }
        }

        $mensagem = "Chamado criado com sucesso! Protocolo: #" . $protocolo;
        $tipo_mensagem = 'success';

    } catch (Exception $e) {
        $mensagem = "Erro ao criar chamado: " . $e->getMessage();
        $tipo_mensagem = 'danger';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abrir Chamado - Admin - NetoNerd</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="../src/css/mobile-fixes.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <style>
        :root {
            --primary: #0A1128;
            --secondary: #001F54;
            --accent: #FDB827;
            --light: #F4F4F9;
            --dark: #061A40;
        }

        body {
            background-color: var(--light);
            color: var(--dark);
        }

        .sidebar {
            background-color: var(--primary);
            color: var(--light);
            min-height: 100vh;
            width: 250px;
            position: fixed;
            left: 0;
            top: 0;
        }

        .sidebar h2 {
            color: var(--accent);
        }

        .sidebar a {
            color: var(--light);
            text-decoration: none;
            padding: 10px 15px;
            display: block;
            border-radius: 5px;
            margin: 5px 0;
        }

        .sidebar a:hover, .sidebar a.active {
            background-color: var(--secondary);
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
        }

        .form-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 30px;
            border-left: 5px solid var(--accent);
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--accent);
        }

        .cliente-toggle {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .cliente-toggle .btn {
            flex: 1;
            padding: 15px;
            border-radius: 10px;
            font-weight: 600;
        }

        .cliente-toggle .btn.active {
            background: var(--accent);
            color: var(--dark);
            border-color: var(--accent);
        }

        .cliente-section {
            display: none;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .cliente-section.active {
            display: block;
        }

        .prioridade-card {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
        }

        .prioridade-card:hover {
            border-color: var(--accent);
        }

        .prioridade-card.selected {
            border-color: var(--accent);
            background: rgba(253, 184, 39, 0.1);
        }

        .prioridade-card input {
            display: none;
        }

        .prioridade-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .drop-zone {
            border: 2px dashed #ccc;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }

        .drop-zone:hover, .drop-zone.dragover {
            border-color: var(--accent);
            background: rgba(253, 184, 39, 0.05);
        }

        .file-list {
            margin-top: 15px;
        }

        .file-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            margin-bottom: 5px;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <div class="sidebar p-3">
            <h2 class="text-center mb-4">NetoNerd</h2>
            <ul class="nav flex-column">
                <li class="nav-item"><a href="dashboard.php" class="nav-link"><i class="fas fa-home me-2"></i>Dashboard</a></li>
                <li class="nav-item"><a href="apresenta_tecnicos.php" class="nav-link"><i class="fas fa-users me-2"></i>Técnicos</a></li>
                <li class="nav-item"><a href="chamados_ativos.php" class="nav-link"><i class="fas fa-ticket-alt me-2"></i>Chamados</a></li>
                <li class="nav-item"><a href="abrir_chamado_admin.php" class="nav-link active"><i class="fas fa-plus-circle me-2"></i>Novo Chamado</a></li>
                <li class="nav-item"><a href="relatorios.php" class="nav-link"><i class="fas fa-chart-bar me-2"></i>Relatórios</a></li>
                <li class="nav-item"><a href="configura.php" class="nav-link"><i class="fas fa-cog me-2"></i>Configurações</a></li>
                <li class="nav-item mt-4"><a href="../tecnico/logoff.php" class="nav-link btn btn-outline-light"><i class="fas fa-sign-out-alt me-2"></i>Sair</a></li>
            </ul>
        </div>

        <div class="main-content flex-grow-1">
            <header class="mb-4">
                <h1><i class="fas fa-plus-circle me-2"></i>Abrir Novo Chamado</h1>
                <p class="text-muted">Crie um chamado para cliente registrado ou não registrado</p>
            </header>

            <?php if ($mensagem): ?>
                <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($mensagem); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" id="formChamado">
                <div class="form-card mb-4">
                    <div class="section-title"><i class="fas fa-user me-2"></i>Dados do Cliente</div>

                    <div class="cliente-toggle">
                        <button type="button" class="btn btn-outline-secondary active" onclick="toggleCliente('novo')">
                            <i class="fas fa-user-plus me-2"></i>Cliente Novo / Não Registrado
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="toggleCliente('existente')">
                            <i class="fas fa-user-check me-2"></i>Cliente Registrado
                        </button>
                    </div>

                    <input type="hidden" name="tipo_cliente" id="tipo_cliente" value="novo">

                    <!-- Cliente Novo -->
                    <div class="cliente-section active" id="cliente-novo">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Nome do Cliente *</label>
                                <input type="text" class="form-control" name="cliente_nome" id="cliente_nome" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Telefone *</label>
                                <input type="tel" class="form-control" name="cliente_telefone" id="cliente_telefone" required>
                            </div>
                            <div class="col-12 mb-3">
                                <label class="form-label">Email (opcional)</label>
                                <input type="email" class="form-control" name="cliente_email" id="cliente_email">
                            </div>
                        </div>
                    </div>

                    <!-- Cliente Existente -->
                    <div class="cliente-section" id="cliente-existente">
                        <div class="mb-3">
                            <label class="form-label">Selecionar Cliente *</label>
                            <select class="form-select" name="cliente_id" id="cliente_id">
                                <option value="">-- Selecione um cliente --</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo $cliente['id']; ?>" data-email="<?php echo htmlspecialchars($cliente['email']); ?>" data-telefone="<?php echo htmlspecialchars($cliente['telefone']); ?>">
                                        <?php echo htmlspecialchars($cliente['nome']); ?> - <?php echo htmlspecialchars($cliente['email']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div id="cliente-info" class="alert alert-info d-none">
                            <strong>Email:</strong> <span id="info-email"></span><br>
                            <strong>Telefone:</strong> <span id="info-telefone"></span>
                        </div>
                    </div>
                </div>

                <div class="form-card mb-4">
                    <div class="section-title"><i class="fas fa-clipboard-list me-2"></i>Detalhes do Chamado</div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Categoria *</label>
                            <select class="form-select" name="categoria" id="categoria" required>
                                <option value="">-- Selecione --</option>
                                <?php foreach ($categorias as $cat => $subcats): ?>
                                    <optgroup label="<?php echo htmlspecialchars($cat); ?>">
                                        <?php foreach ($subcats as $subcat): ?>
                                            <option value="<?php echo htmlspecialchars($cat . ' - ' . $subcat); ?>">
                                                <?php echo htmlspecialchars($subcat); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Técnico Responsável</label>
                            <select class="form-select" name="tecnico_id" id="tecnico_id">
                                <option value="">-- Atribuição automática --</option>
                                <?php foreach ($tecnicos as $tecnico): ?>
                                    <option value="<?php echo $tecnico['id']; ?>">
                                        <?php echo htmlspecialchars($tecnico['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Título do Chamado *</label>
                        <input type="text" class="form-control" name="titulo" required maxlength="255"
                               placeholder="Ex: Computador não liga após queda de energia">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descrição do Problema *</label>
                        <textarea class="form-control" name="descricao" rows="5" required maxlength="5000"
                                  placeholder="Descreva o problema detalhadamente..."></textarea>
                    </div>
                </div>

                <div class="form-card mb-4">
                    <div class="section-title"><i class="fas fa-exclamation-triangle me-2"></i>Prioridade</div>

                    <div class="row">
                        <div class="col-md-3 col-6 mb-3">
                            <label class="prioridade-card">
                                <input type="radio" name="prioridade" value="baixa">
                                <div class="prioridade-icon text-success">🟢</div>
                                <div class="fw-bold">Baixa</div>
                                <small class="text-muted">Pode aguardar</small>
                            </label>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <label class="prioridade-card selected">
                                <input type="radio" name="prioridade" value="media" checked>
                                <div class="prioridade-icon text-warning">🟡</div>
                                <div class="fw-bold">Média</div>
                                <small class="text-muted">Normal</small>
                            </label>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <label class="prioridade-card">
                                <input type="radio" name="prioridade" value="alta">
                                <div class="prioridade-icon text-danger">🔴</div>
                                <div class="fw-bold">Alta</div>
                                <small class="text-muted">Urgente</small>
                            </label>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <label class="prioridade-card">
                                <input type="radio" name="prioridade" value="critica">
                                <div class="prioridade-icon">⚫</div>
                                <div class="fw-bold">Crítica</div>
                                <small class="text-muted">Impede trabalho</small>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-card mb-4">
                    <div class="section-title"><i class="fas fa-paperclip me-2"></i>Anexos (opcional)</div>

                    <div class="drop-zone" id="dropZone" onclick="document.getElementById('anexos').click()">
                        <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                        <p class="mb-1">Arraste arquivos aqui ou clique para selecionar</p>
                        <small class="text-muted">Imagens, PDFs, DOC (máx. 10MB cada)</small>
                    </div>
                    <input type="file" name="anexos[]" id="anexos" multiple accept="image/*,.pdf,.doc,.docx,.txt" style="display: none">
                    <div class="file-list" id="fileList"></div>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="dashboard.php" class="btn btn-outline-secondary btn-lg">
                        <i class="fas fa-arrow-left me-2"></i>Voltar
                    </a>
                    <button type="submit" class="btn btn-warning btn-lg">
                        <i class="fas fa-paper-plane me-2"></i>Criar Chamado
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleCliente(tipo) {
            document.getElementById('tipo_cliente').value = tipo;

            document.querySelectorAll('.cliente-toggle .btn').forEach(btn => btn.classList.remove('active'));
            event.target.closest('.btn').classList.add('active');

            document.getElementById('cliente-novo').classList.remove('active');
            document.getElementById('cliente-existente').classList.remove('active');
            document.getElementById('cliente-' + tipo).classList.add('active');

            // Ajustar required
            if (tipo === 'novo') {
                document.getElementById('cliente_nome').required = true;
                document.getElementById('cliente_telefone').required = true;
                document.getElementById('cliente_id').required = false;
            } else {
                document.getElementById('cliente_nome').required = false;
                document.getElementById('cliente_telefone').required = false;
                document.getElementById('cliente_id').required = true;
            }
        }

        // Mostrar info do cliente selecionado
        document.getElementById('cliente_id').addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const infoDiv = document.getElementById('cliente-info');

            if (this.value) {
                document.getElementById('info-email').textContent = selected.dataset.email || 'Não informado';
                document.getElementById('info-telefone').textContent = selected.dataset.telefone || 'Não informado';
                infoDiv.classList.remove('d-none');
            } else {
                infoDiv.classList.add('d-none');
            }
        });

        // Prioridade cards
        document.querySelectorAll('.prioridade-card').forEach(card => {
            card.addEventListener('click', function() {
                document.querySelectorAll('.prioridade-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
            });
        });

        // Drop zone
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('anexos');
        const fileList = document.getElementById('fileList');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'));
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'));
        });

        dropZone.addEventListener('drop', function(e) {
            fileInput.files = e.dataTransfer.files;
            updateFileList();
        });

        fileInput.addEventListener('change', updateFileList);

        function updateFileList() {
            fileList.innerHTML = '';
            Array.from(fileInput.files).forEach((file, index) => {
                const div = document.createElement('div');
                div.className = 'file-item';
                div.innerHTML = `
                    <span><i class="fas fa-file me-2"></i>${file.name} (${(file.size / 1024).toFixed(1)} KB)</span>
                `;
                fileList.appendChild(div);
            });
        }
    </script>
</body>
</html>
