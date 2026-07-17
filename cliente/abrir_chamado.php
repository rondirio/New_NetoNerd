<?php
require_once "../controller/validador_acesso.php";
require_once "../controller/auth_middleware.php";
include '../config/bandoDeDados/conexao.php';

requireCliente();

$dados_cliente = obterDadosCliente();

$conn = getConnection();
$usuario_id = $_SESSION['id'];

// Buscar dados do cliente para preencher automaticamente
$stmt = $conn->prepare("SELECT nome, email, telefone FROM clientes WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$cliente = $result->fetch_assoc();
$stmt->close();

// Buscar categorias do banco
$categorias = [];
$result_categorias = $conn->query("SELECT id, nome, icone FROM categorias_chamado WHERE ativo = 1 ORDER BY nome");
if ($result_categorias) {
    while ($row = $result_categorias->fetch_assoc()) {
        $categorias[] = $row;
    }
}

$conn->close();

$page_title = "Abrir Novo Chamado - NetoNerd ITSM";
$extra_css = '<style>
    .nn-wizard-steps {
        display: flex;
        justify-content: space-between;
        margin-bottom: 40px;
        position: relative;
    }

    .nn-wizard-steps::before {
        content: "";
        position: absolute;
        top: 20px;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--bg-lighter);
        z-index: 0;
    }

    .nn-wizard-steps-progress {
        position: absolute;
        top: 20px;
        left: 0;
        height: 3px;
        background: var(--gradient-primary);
        transition: width 0.3s;
        z-index: 1;
    }

    .nn-wizard-step {
        flex: 1;
        text-align: center;
        position: relative;
        z-index: 2;
    }

    .nn-wizard-step-circle {
        width: 40px;
        height: 40px;
        background: white;
        border: 3px solid var(--bg-lighter);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 10px;
        font-weight: 700;
        color: var(--text-light);
        transition: all 0.3s;
    }

    .nn-wizard-step.active .nn-wizard-step-circle {
        background: var(--gradient-primary);
        border-color: var(--primary-blue);
        color: white;
        transform: scale(1.1);
    }

    .nn-wizard-step.completed .nn-wizard-step-circle {
        background: var(--success);
        border-color: var(--success);
        color: white;
    }

    .nn-wizard-step-label {
        font-size: 0.9rem;
        color: var(--text-light);
        font-weight: 500;
    }

    .nn-wizard-step.active .nn-wizard-step-label {
        color: var(--primary-blue);
        font-weight: 600;
    }

    .nn-form-section {
        display: none;
    }

    .nn-form-section.active {
        display: block;
        animation: fadeInUp 0.5s;
    }

    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .nn-label-required::after {
        content: "*";
        color: var(--danger);
        margin-left: 4px;
    }

    .nn-category-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }

    .nn-category-card {
        background: var(--bg-light);
        border: 2px solid var(--bg-lighter);
        border-radius: var(--radius-md);
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }

    .nn-category-card:hover {
        background: var(--bg-lighter);
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }

    .nn-category-card.selected {
        background: var(--gradient-primary);
        border-color: var(--primary-blue);
        color: white;
        box-shadow: var(--shadow-lg);
    }

    .nn-category-icon {
        font-size: 2rem;
        margin-bottom: 10px;
    }

    .nn-category-name {
        font-weight: 600;
        font-size: 1rem;
    }

    .nn-priority-selector {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-bottom: 20px;
    }

    .nn-priority-option {
        background: white;
        border: 2px solid var(--bg-lighter);
        border-radius: var(--radius-md);
        padding: 20px 15px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
    }

    .nn-priority-option:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-md);
    }

    .nn-priority-option.selected {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
    }

    .nn-priority-baixa.selected { background: #d4edda; border-color: var(--success); }
    .nn-priority-media.selected { background: #fff3cd; border-color: var(--warning); }
    .nn-priority-alta.selected { background: #f8d7da; border-color: var(--danger); }
    .nn-priority-urgente.selected { background: var(--bg-dark); border-color: var(--bg-dark); color: white; }

    .nn-priority-icon { font-size: 1.5rem; margin-bottom: 8px; }
    .nn-priority-label { font-weight: 600; font-size: 0.95rem; }
    .nn-priority-description { font-size: 0.8rem; color: var(--text-medium); margin-top: 5px; }
    .nn-priority-urgente.selected .nn-priority-description { color: rgba(255,255,255,0.8); }

    .nn-file-upload-area {
        border: 2px dashed var(--bg-lighter);
        border-radius: var(--radius-md);
        padding: 40px;
        text-align: center;
        background: var(--bg-light);
        transition: all 0.3s;
        cursor: pointer;
    }

    .nn-file-upload-area:hover,
    .nn-file-upload-area.dragover {
        border-color: var(--primary-blue);
        background: #eef3fc;
    }

    .nn-upload-icon { font-size: 3rem; color: var(--primary-blue); margin-bottom: 15px; }
    .nn-upload-text { font-size: 1.1rem; color: var(--text-medium); margin-bottom: 5px; }
    .nn-upload-hint { font-size: 0.9rem; color: var(--text-light); }

    .nn-file-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 15px;
        background: white;
        border: 1px solid var(--bg-lighter);
        border-radius: var(--radius-sm);
        margin-bottom: 10px;
    }

    .nn-file-info { display: flex; align-items: center; }
    .nn-file-icon { font-size: 1.5rem; margin-right: 12px; }
    .nn-file-name { font-weight: 600; }
    .nn-file-size { font-size: 0.85rem; color: var(--text-light); margin-left: 10px; }
    .nn-btn-remove-file { color: var(--danger); cursor: pointer; font-size: 1.2rem; }

    .nn-wizard-actions {
        display: flex;
        justify-content: space-between;
        margin-top: 40px;
        padding-top: 30px;
        border-top: 2px solid var(--bg-lighter);
    }

    .nn-summary-item {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid var(--bg-lighter);
    }

    .nn-summary-item:last-child { border-bottom: none; }
    .nn-summary-label { font-weight: 600; color: var(--text-medium); }
    .nn-summary-value { color: var(--text-dark); text-align: right; max-width: 60%; }

    .nn-char-counter {
        text-align: right;
        font-size: 0.85rem;
        color: var(--text-light);
        margin-top: 5px;
    }

    .nn-help-text {
        font-size: 0.9rem;
        color: var(--text-medium);
        margin-top: 5px;
        display: flex;
        align-items: start;
    }

    .nn-help-text::before {
        content: "\\1F4A1";
        margin-right: 8px;
    }

    @media (max-width: 768px) {
        .nn-category-grid { grid-template-columns: 1fr; }
        .nn-priority-selector { grid-template-columns: 1fr; }
        .nn-wizard-steps { flex-direction: column; }
        .nn-wizard-steps::before { display: none; }
        .nn-wizard-step { margin-bottom: 20px; }
    }
</style>';
require_once '../includes/header.php';
?>

<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <div class="nn-card nn-animate-fade" style="max-width: 900px; margin: 0 auto;">
            <div class="nn-card-header nn-text-center" style="flex-direction: column; align-items: center; border-bottom: none;">
                <h1 class="nn-card-title">Abrir Novo Chamado</h1>
                <p class="nn-text-medium">Siga os passos para criar seu chamado de suporte</p>
            </div>

            <div class="nn-card-body">
                <!-- Steps -->
                <div class="nn-wizard-steps">
                    <div class="nn-wizard-steps-progress" id="stepsProgress"></div>
                    <div class="nn-wizard-step active" data-step="1">
                        <div class="nn-wizard-step-circle">1</div>
                        <div class="nn-wizard-step-label">Categoria</div>
                    </div>
                    <div class="nn-wizard-step" data-step="2">
                        <div class="nn-wizard-step-circle">2</div>
                        <div class="nn-wizard-step-label">Detalhes</div>
                    </div>
                    <div class="nn-wizard-step" data-step="3">
                        <div class="nn-wizard-step-circle">3</div>
                        <div class="nn-wizard-step-label">Prioridade</div>
                    </div>
                    <div class="nn-wizard-step" data-step="4">
                        <div class="nn-wizard-step-circle">4</div>
                        <div class="nn-wizard-step-label">Revisão</div>
                    </div>
                </div>

                <!-- Form -->
                <form id="chamadoForm" method="POST" action="registra_chamado.php" enctype="multipart/form-data">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="usuario" value="<?php echo htmlspecialchars($cliente['nome']); ?>">

                    <!-- Step 1: Categoria -->
                    <div class="nn-form-section active" data-section="1">
                        <h4 class="nn-mb-2">Selecione a categoria do problema</h4>

                        <div class="nn-category-grid">
                            <?php
                            $icons = [
                                'Hardware' => '🖥️',
                                'Software' => '💻',
                                'Rede' => '🌐',
                                'Acesso' => '🔒',
                                'Impressora' => '🖨️',
                                'Email' => '📧',
                                'Telefonia' => '📞',
                                'Outros' => '📦'
                            ];

                            foreach ($categorias as $cat):
                            ?>
                                <div class="nn-category-card" onclick="selectCategory('<?php echo $cat['id']; ?>', this)">
                                    <div class="nn-category-icon"><?php echo $icons[$cat['nome']] ?? '📦'; ?></div>
                                    <div class="nn-category-name"><?php echo htmlspecialchars($cat['nome']); ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <input type="hidden" name="categoria_id" id="categoriaInput" required>
                    </div>

                    <!-- Step 2: Detalhes -->
                    <div class="nn-form-section" data-section="2">
                        <h4 class="nn-mb-2">Descreva seu problema</h4>

                        <div class="nn-form-group">
                            <label class="nn-form-label nn-label-required" for="tituloInput">Título do chamado</label>
                            <input type="text" name="titulo" id="tituloInput" class="nn-form-control"
                                   placeholder="Ex: Computador não liga após atualização"
                                   maxlength="100" required>
                            <div class="nn-char-counter">
                                <span id="tituloCounter">0</span>/100 caracteres
                            </div>
                        </div>

                        <div class="nn-form-group">
                            <label class="nn-form-label nn-label-required" for="descricaoInput">Descrição detalhada</label>
                            <textarea name="descricao" id="descricaoInput" class="nn-form-control"
                                      rows="6" placeholder="Descreva o problema com o máximo de detalhes possível..."
                                      maxlength="1000" required></textarea>
                            <div class="nn-char-counter">
                                <span id="descricaoCounter">0</span>/1000 caracteres
                            </div>
                            <div class="nn-help-text">
                                Inclua: O que aconteceu? Quando começou? O que você já tentou fazer?
                            </div>
                        </div>

                        <div class="nn-form-group">
                            <label class="nn-form-label">Anexar arquivos (opcional)</label>
                            <div class="nn-file-upload-area" id="dropZone">
                                <div class="nn-upload-icon"><i class="fas fa-paperclip"></i></div>
                                <div class="nn-upload-text">Arraste arquivos aqui ou clique para selecionar</div>
                                <div class="nn-upload-hint">Imagens, PDFs e documentos até 10MB</div>
                                <input type="file" id="fileInput" name="anexos[]" multiple style="display: none;"
                                       accept="image/*,.pdf,.doc,.docx,.txt">
                            </div>
                            <div id="fileList"></div>
                        </div>
                    </div>

                    <!-- Step 3: Prioridade -->
                    <div class="nn-form-section" data-section="3">
                        <h4 class="nn-mb-2">Qual a urgência do problema?</h4>

                        <div class="nn-priority-selector">
                            <div class="nn-priority-option nn-priority-baixa" onclick="selectPriority('baixa', this)">
                                <div class="nn-priority-icon">🟢</div>
                                <div class="nn-priority-label">Baixa</div>
                                <div class="nn-priority-description">Pode esperar alguns dias</div>
                            </div>

                            <div class="nn-priority-option nn-priority-media" onclick="selectPriority('media', this)">
                                <div class="nn-priority-icon">🟡</div>
                                <div class="nn-priority-label">Média</div>
                                <div class="nn-priority-description">Precisa resolver logo</div>
                            </div>

                            <div class="nn-priority-option nn-priority-alta" onclick="selectPriority('alta', this)">
                                <div class="nn-priority-icon">🔴</div>
                                <div class="nn-priority-label">Alta</div>
                                <div class="nn-priority-description">É urgente</div>
                            </div>

                            <div class="nn-priority-option nn-priority-urgente" onclick="selectPriority('urgente', this)">
                                <div class="nn-priority-icon">⚫</div>
                                <div class="nn-priority-label">Urgente</div>
                                <div class="nn-priority-description">Impede o trabalho</div>
                            </div>
                        </div>

                        <input type="hidden" name="prioridade" id="prioridadeInput" value="media">

                        <div class="nn-help-text nn-mt-2">
                            A prioridade ajuda nossa equipe a organizar os atendimentos. Seja honesto para que possamos ajudar da melhor forma.
                        </div>
                    </div>

                    <!-- Step 4: Revisão -->
                    <div class="nn-form-section" data-section="4">
                        <h4 class="nn-mb-2">Revise as informações do chamado</h4>

                        <div class="nn-card" style="background: var(--bg-light); box-shadow: none;">
                            <h5 class="nn-mb-2">Resumo do Chamado</h5>

                            <div class="nn-summary-item">
                                <span class="nn-summary-label">Categoria:</span>
                                <span class="nn-summary-value" id="summaryCategoria">-</span>
                            </div>
                            <div class="nn-summary-item">
                                <span class="nn-summary-label">Título:</span>
                                <span class="nn-summary-value" id="summaryTitulo">-</span>
                            </div>
                            <div class="nn-summary-item">
                                <span class="nn-summary-label">Descrição:</span>
                                <span class="nn-summary-value" id="summaryDescricao">-</span>
                            </div>
                            <div class="nn-summary-item">
                                <span class="nn-summary-label">Prioridade:</span>
                                <span class="nn-summary-value" id="summaryPrioridade">Média</span>
                            </div>
                            <div class="nn-summary-item">
                                <span class="nn-summary-label">Arquivos anexados:</span>
                                <span class="nn-summary-value" id="summaryArquivos">Nenhum</span>
                            </div>
                        </div>

                        <div class="nn-alert nn-alert-info nn-mt-2">
                            <i class="fas fa-envelope"></i>
                            Você receberá um email com o número do protocolo e atualizações sobre seu chamado.
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="nn-wizard-actions">
                        <button type="button" class="nn-btn nn-btn-secondary" id="btnPrev" onclick="prevStep()" style="display: none;">
                            <i class="fas fa-arrow-left"></i>
                            Voltar
                        </button>
                        <div></div>
                        <button type="button" class="nn-btn nn-btn-primary" id="btnNext" onclick="nextStep()">
                            Próximo
                            <i class="fas fa-arrow-right"></i>
                        </button>
                        <button type="submit" class="nn-btn nn-btn-primary" id="btnSubmit" style="display: none;">
                            <i class="fas fa-rocket"></i>
                            Abrir Chamado
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

<?php
$extra_js = '<script>
    let currentStep = 1;
    const totalSteps = 4;
    let selectedFiles = [];

    function nextStep() {
        if (!validateStep(currentStep)) {
            return;
        }

        if (currentStep < totalSteps) {
            document.querySelector(`[data-section="${currentStep}"]`).classList.remove("active");
            document.querySelector(`[data-step="${currentStep}"]`).classList.add("completed");

            currentStep++;

            document.querySelector(`[data-section="${currentStep}"]`).classList.add("active");
            document.querySelector(`[data-step="${currentStep}"]`).classList.add("active");

            updateButtons();
            updateProgress();

            if (currentStep === 4) {
                updateSummary();
            }
        }
    }

    function prevStep() {
        if (currentStep > 1) {
            document.querySelector(`[data-section="${currentStep}"]`).classList.remove("active");
            document.querySelector(`[data-step="${currentStep}"]`).classList.remove("active");

            currentStep--;

            document.querySelector(`[data-section="${currentStep}"]`).classList.add("active");
            document.querySelector(`[data-step="${currentStep + 1}"]`).classList.remove("completed");

            updateButtons();
            updateProgress();
        }
    }

    function updateButtons() {
        document.getElementById("btnPrev").style.display = currentStep === 1 ? "none" : "inline-flex";
        document.getElementById("btnNext").style.display = currentStep === totalSteps ? "none" : "inline-flex";
        document.getElementById("btnSubmit").style.display = currentStep === totalSteps ? "inline-flex" : "none";
    }

    function updateProgress() {
        const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
        document.getElementById("stepsProgress").style.width = progress + "%";
    }

    function validateStep(step) {
        switch(step) {
            case 1:
                if (!document.getElementById("categoriaInput").value) {
                    alert("Por favor, selecione uma categoria");
                    return false;
                }
                return true;

            case 2:
                if (!document.getElementById("tituloInput").value.trim()) {
                    alert("Por favor, preencha o título do chamado");
                    return false;
                }
                if (!document.getElementById("descricaoInput").value.trim()) {
                    alert("Por favor, descreva o problema");
                    return false;
                }
                return true;

            case 3:
                return true;

            default:
                return true;
        }
    }

    function selectCategory(categoryId, element) {
        document.querySelectorAll(".nn-category-card").forEach(card => {
            card.classList.remove("selected");
        });
        element.classList.add("selected");

        document.getElementById("categoriaInput").value = categoryId;
    }

    function selectPriority(priority, element) {
        document.querySelectorAll(".nn-priority-option").forEach(opt => {
            opt.classList.remove("selected");
        });
        element.classList.add("selected");
        document.getElementById("prioridadeInput").value = priority;
    }

    document.getElementById("tituloInput").addEventListener("input", function() {
        document.getElementById("tituloCounter").textContent = this.value.length;
    });

    document.getElementById("descricaoInput").addEventListener("input", function() {
        document.getElementById("descricaoCounter").textContent = this.value.length;
    });

    const dropZone = document.getElementById("dropZone");
    const fileInput = document.getElementById("fileInput");
    const fileList = document.getElementById("fileList");

    dropZone.addEventListener("click", () => fileInput.click());

    dropZone.addEventListener("dragover", (e) => {
        e.preventDefault();
        dropZone.classList.add("dragover");
    });

    dropZone.addEventListener("dragleave", () => {
        dropZone.classList.remove("dragover");
    });

    dropZone.addEventListener("drop", (e) => {
        e.preventDefault();
        dropZone.classList.remove("dragover");
        handleFiles(e.dataTransfer.files);
    });

    fileInput.addEventListener("change", (e) => {
        handleFiles(e.target.files);
    });

    function handleFiles(files) {
        Array.from(files).forEach(file => {
            if (file.size > 10 * 1024 * 1024) {
                alert(`Arquivo ${file.name} é muito grande. Máximo: 10MB`);
                return;
            }

            selectedFiles.push(file);
            addFileToList(file);
        });
    }

    function addFileToList(file) {
        const fileItem = document.createElement("div");
        fileItem.className = "nn-file-item";
        fileItem.innerHTML = `
            <div class="nn-file-info">
                <span class="nn-file-icon"><i class="fas fa-file"></i></span>
                <div>
                    <div class="nn-file-name">${file.name}</div>
                    <span class="nn-file-size">${formatFileSize(file.size)}</span>
                </div>
            </div>
            <span class="nn-btn-remove-file" onclick="removeFile(\'${file.name}\')">&times;</span>
        `;
        fileList.appendChild(fileItem);
    }

    function removeFile(fileName) {
        selectedFiles = selectedFiles.filter(f => f.name !== fileName);
        const fileItems = fileList.querySelectorAll(".nn-file-item");
        fileItems.forEach(item => {
            if (item.querySelector(".nn-file-name").textContent === fileName) {
                item.remove();
            }
        });
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return "0 Bytes";
        const k = 1024;
        const sizes = ["Bytes", "KB", "MB"];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return Math.round(bytes / Math.pow(k, i) * 100) / 100 + " " + sizes[i];
    }

    function updateSummary() {
        const categoriaCard = document.querySelector(".nn-category-card.selected .nn-category-name");
        const categoria = categoriaCard ? categoriaCard.textContent : "";
        const titulo = document.getElementById("tituloInput").value;
        const descricao = document.getElementById("descricaoInput").value;
        const prioridade = document.getElementById("prioridadeInput").value;

        document.getElementById("summaryCategoria").textContent = categoria || "-";
        document.getElementById("summaryTitulo").textContent = titulo || "-";
        document.getElementById("summaryDescricao").textContent = descricao.substring(0, 100) + (descricao.length > 100 ? "..." : "") || "-";
        document.getElementById("summaryPrioridade").textContent = prioridade.charAt(0).toUpperCase() + prioridade.slice(1);
        document.getElementById("summaryArquivos").textContent = selectedFiles.length > 0 ?
            `${selectedFiles.length} arquivo(s)` : "Nenhum";
    }

    document.getElementById("chamadoForm").addEventListener("submit", function(e) {
        if (!confirm("Tem certeza que deseja abrir este chamado?")) {
            e.preventDefault();
            return false;
        }

        const btn = document.getElementById("btnSubmit");
        btn.innerHTML = "<i class=\'fas fa-spinner fa-spin\'></i> Enviando...";
        btn.disabled = true;
    });

    updateProgress();
    document.querySelector(".nn-priority-media").classList.add("selected");
</script>';
require_once '../includes/footer.php';
?>
