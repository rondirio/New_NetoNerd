<?php 
require_once "../controller/validador_acesso.php";
include '../config/bandoDeDados/conexao.php';

$dados_cliente = obterDadosCliente();
// print_r($dados_cliente['genero']);

$conn = getConnection();
$usuario_id = $_SESSION['id'];

// Buscar dados do cliente para preencher automaticamente
$stmt = $conn->prepare("SELECT nome, email, telefone FROM clientes WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$cliente = $result->fetch_assoc();
$stmt->close();

// Buscar categorias do banco (se existirem)
$categorias = [
    'Hardware' => ['Computador não liga', 'Tela quebrada', 'Teclado com defeito', 'Mouse não funciona', 'Periféricos'],
    'Software' => ['Instalação de programa', 'Atualização', 'Erro de sistema', 'Lentidão', 'Vírus/Malware'],
    'Rede' => ['Internet lenta', 'Wi-Fi não conecta', 'Sem acesso à rede', 'Problema de VPN', 'Configuração de rede'],
    'Segurança' => ['Antivírus', 'Firewall', 'Backup', 'Recuperação de dados', 'Criptografia'],
    'Impressão' => ['Impressora offline', 'Problema de driver', 'Papel atolado', 'Qualidade de impressão'],
    'Email' => ['Não recebe emails', 'Erro ao enviar', 'Configuração de conta', 'Outlook/Thunderbird'],
    'Outros' => ['Consultoria', 'Treinamento', 'Suporte geral', 'Dúvidas']
];

$conn->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abrir Novo Chamado - NetoNerd</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../src/css/main.css">
    <link rel="stylesheet" type="text/css" href="../src/css/estilo_navegar_cliente.css">
    <style>
        .form-wizard {
            background: white;
            border-radius: 15px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin: 30px auto;
            max-width: 900px;
        }
        
        .wizard-header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        .wizard-title {
            font-size: 2rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 10px;
        }
        
        .wizard-subtitle {
            color: #6c757d;
            font-size: 1.1rem;
        }
        
        .wizard-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }
        
        .wizard-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 3px;
            background: #e9ecef;
            z-index: 0;
        }
        
        .wizard-steps-progress {
            position: absolute;
            top: 20px;
            left: 0;
            height: 3px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: width 0.3s;
            z-index: 1;
        }
        
        .wizard-step {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 2;
        }
        
        .step-circle {
            width: 40px;
            height: 40px;
            background: white;
            border: 3px solid #e9ecef;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 10px;
            font-weight: 700;
            color: #adb5bd;
            transition: all 0.3s;
        }
        
        .wizard-step.active .step-circle {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
            color: white;
            transform: scale(1.1);
        }
        
        .wizard-step.completed .step-circle {
            background: #28a745;
            border-color: #28a745;
            color: white;
        }
        
        .step-label {
            font-size: 0.9rem;
            color: #adb5bd;
            font-weight: 500;
        }
        
        .wizard-step.active .step-label {
            color: #667eea;
            font-weight: 600;
        }
        
        .form-section {
            display: none;
        }
        
        .form-section.active {
            display: block;
            animation: fadeInUp 0.5s;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .form-group label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
        }
        
        .label-required::after {
            content: '*';
            color: #dc3545;
            margin-left: 4px;
        }
        
        .form-control, .form-control:focus {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .category-card {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .category-card:hover {
            background: #e9ecef;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .category-card.selected {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
            color: white;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
        }
        
        .category-icon {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .category-name {
            font-weight: 600;
            font-size: 1rem;
        }
        
        .priority-selector {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .priority-option {
            background: white;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px 15px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .priority-option:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .priority-option.selected {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .priority-baixa.selected {
            background: #d4edda;
            border-color: #28a745;
        }
        
        .priority-media.selected {
            background: #fff3cd;
            border-color: #ffc107;
        }
        
        .priority-alta.selected {
            background: #f8d7da;
            border-color: #dc3545;
        }
        
        .priority-urgente.selected {
            background: #212529;
            border-color: #212529;
            color: white;
        }
        
        .priority-icon {
            font-size: 1.5rem;
            margin-bottom: 8px;
        }
        
        .priority-label {
            font-weight: 600;
            font-size: 0.95rem;
        }
        
        .priority-description {
            font-size: 0.8rem;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .priority-urgente.selected .priority-description {
            color: rgba(255,255,255,0.8);
        }
        
        .file-upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            background: #f8f9fa;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .file-upload-area:hover {
            border-color: #667eea;
            background: #f0f3ff;
        }
        
        .file-upload-area.dragover {
            border-color: #667eea;
            background: #f0f3ff;
        }
        
        .upload-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 15px;
        }
        
        .upload-text {
            font-size: 1.1rem;
            color: #495057;
            margin-bottom: 5px;
        }
        
        .upload-hint {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .file-list {
            margin-top: 20px;
        }
        
        .file-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 12px 15px;
            background: white;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 10px;
        }
        
        .file-info {
            display: flex;
            align-items: center;
        }
        
        .file-icon {
            font-size: 1.5rem;
            margin-right: 12px;
        }
        
        .file-name {
            font-weight: 600;
            color: #212529;
        }
        
        .file-size {
            font-size: 0.85rem;
            color: #6c757d;
            margin-left: 10px;
        }
        
        .btn-remove-file {
            color: #dc3545;
            cursor: pointer;
            font-size: 1.2rem;
        }
        
        .wizard-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #e9ecef;
        }
        
        .btn-wizard {
            padding: 12px 35px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        .btn-prev {
            background: white;
            border: 2px solid #e9ecef;
            color: #495057;
        }
        
        .btn-prev:hover {
            background: #f8f9fa;
            border-color: #dee2e6;
        }
        
        .btn-next, .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        }
        
        .btn-next:hover, .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .summary-section {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
        }
        
        .summary-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #212529;
            margin-bottom: 20px;
        }
        
        .summary-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .summary-item:last-child {
            border-bottom: none;
        }
        
        .summary-label {
            font-weight: 600;
            color: #495057;
        }
        
        .summary-value {
            color: #212529;
            text-align: right;
            max-width: 60%;
        }
        
        .char-counter {
            text-align: right;
            font-size: 0.85rem;
            color: #6c757d;
            margin-top: 5px;
        }
        
        .help-text {
            font-size: 0.9rem;
            color: #6c757d;
            margin-top: 5px;
            display: flex;
            align-items: start;
        }
        
        .help-text::before {
            content: '💡';
            margin-right: 8px;
        }
        
        @media (max-width: 768px) {
            .category-grid {
                grid-template-columns: 1fr;
            }
            
            .priority-selector {
                grid-template-columns: 1fr;
            }
            
            .wizard-steps {
                flex-direction: column;
            }
            
            .wizard-steps::before {
                display: none;
            }
            
            .wizard-step {
                margin-bottom: 20px;
            }
        }
        .logo{
            width: 90px;
            height: 90px;
            /* object-fit: contain; */
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
   <?php include('../routes/headr.php');?>
    <div class="top-navbar">
        <div class="container">
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($cliente['nome'], 0, 0)); ?>
                </div>
                <div class="user-details">
                    <h6>
                        <?php 
                        echo ($dados_cliente['genero'] === 'Feminino' ? 'Bem-vinda, ' : 'Bem-vindo, ') . 
                             htmlspecialchars(explode(' ', $cliente['nome'])[0]); 
                        ?>
                    </h6>
                    <small><?php echo htmlspecialchars($cliente['email']); ?></small>
                </div>
            </div>
            <div>
                <a href="logoff.php" class="btn btn-light btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </div>
        </div>
    </div>
     <?php include('../routes/header.php');?>

    <div class="container">
        <div class="form-wizard">
            <!-- Header -->
            <div class="wizard-header">
                <div class="wizard-title">Abrir Novo Chamado</div>
                <div class="wizard-subtitle">Siga os passos para criar seu chamado de suporte</div>
            </div>

            <!-- Steps -->
            <div class="wizard-steps">
                <div class="wizard-steps-progress" id="stepsProgress"></div>
                <div class="wizard-step active" data-step="1">
                    <div class="step-circle">1</div>
                    <div class="step-label">Categoria</div>
                </div>
                <div class="wizard-step" data-step="2">
                    <div class="step-circle">2</div>
                    <div class="step-label">Detalhes</div>
                </div>
                <div class="wizard-step" data-step="3">
                    <div class="step-circle">3</div>
                    <div class="step-label">Prioridade</div>
                </div>
                <div class="wizard-step" data-step="4">
                    <div class="step-circle">4</div>
                    <div class="step-label">Revisão</div>
                </div>
            </div>

            <!-- Form -->
            <form id="chamadoForm" method="POST" action="registra_chamado.php" enctype="multipart/form-data">
                <input type="hidden" name="usuario" value="<?php echo htmlspecialchars($cliente['nome']); ?>">
                <!-- Step 1: Categoria -->
                <div class="form-section active" data-section="1">
                    <h4 style="margin-bottom: 25px; font-weight: 600;">Selecione a categoria do problema</h4>
                    
                    <div class="category-grid">
                        <?php 
                        $icons = [
                            'Hardware' => '🖥️',
                            'Software' => '💻',
                            'Rede' => '🌐',
                            'Segurança' => '🔒',
                            'Impressão' => '🖨️',
                            'Email' => '📧',
                            'Outros' => '📦'
                        ];
                        
                        foreach ($categorias as $cat => $subcats): 
                        ?>
                            <div class="category-card" onclick="selectCategory('<?php echo $cat; ?>')">
                                <div class="category-icon"><?php echo $icons[$cat]; ?></div>
                                <div class="category-name"><?php echo $cat; ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <input type="hidden" name="categoria" id="categoriaInput" required>
                    
                    <div id="subcategorySection" style="display: none; margin-top: 25px;">
                        <label class="label-required">Especifique o problema</label>
                        <select class="form-control" name="subcategoria" id="subcategoriaSelect">
                            <option value="">Selecione...</option>
                        </select>
                        <div class="help-text">
                            Escolha a opção que melhor descreve seu problema
                        </div>
                    </div>
                </div>

                <!-- Step 2: Detalhes -->
                <div class="form-section" data-section="2">
                    <h4 style="margin-bottom: 25px; font-weight: 600;">Descreva seu problema</h4>
                    
                    <div class="form-group">
                        <label class="label-required">Título do chamado</label>
                        <input type="text" name="titulo" id="tituloInput" class="form-control" 
                               placeholder="Ex: Computador não liga após atualização" 
                               maxlength="100" required>
                        <div class="char-counter">
                            <span id="tituloCounter">0</span>/100 caracteres
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="label-required">Descrição detalhada</label>
                        <textarea name="descricao" id="descricaoInput" class="form-control" 
                                  rows="6" placeholder="Descreva o problema com o máximo de detalhes possível..." 
                                  maxlength="1000" required></textarea>
                        <div class="char-counter">
                            <span id="descricaoCounter">0</span>/1000 caracteres
                        </div>
                        <div class="help-text">
                            Inclua: O que aconteceu? Quando começou? O que você já tentou fazer?
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Anexar arquivos (opcional)</label>
                        <div class="file-upload-area" id="dropZone">
                            <div class="upload-icon">📎</div>
                            <div class="upload-text">Arraste arquivos aqui ou clique para selecionar</div>
                            <div class="upload-hint">Imagens, PDFs e documentos até 10MB</div>
                            <input type="file" id="fileInput" name="anexos[]" multiple style="display: none;" 
                                   accept="image/*,.pdf,.doc,.docx,.txt">
                        </div>
                        <div class="file-list" id="fileList"></div>
                    </div>
                </div>

                <!-- Step 3: Prioridade -->
                <div class="form-section" data-section="3">
                    <h4 style="margin-bottom: 25px; font-weight: 600;">Qual a urgência do problema?</h4>
                    
                    <div class="priority-selector">
                        <div class="priority-option priority-baixa" onclick="selectPriority('baixa', this)">
                            <div class="priority-icon">🟢</div>
                            <div class="priority-label">Baixa</div>
                            <div class="priority-description">Pode esperar alguns dias</div>
                        </div>
                        
                        <div class="priority-option priority-media" onclick="selectPriority('media', this)">
                            <div class="priority-icon">🟡</div>
                            <div class="priority-label">Média</div>
                            <div class="priority-description">Precisa resolver logo</div>
                        </div>
                        
                        <div class="priority-option priority-alta" onclick="selectPriority('alta', this)">
                            <div class="priority-icon">🔴</div>
                            <div class="priority-label">Alta</div>
                            <div class="priority-description">É urgente</div>
                        </div>
                        
                        <div class="priority-option priority-urgente" onclick="selectPriority('urgente', this)">
                            <div class="priority-icon">⚫</div>
                            <div class="priority-label">Urgente</div>
                            <div class="priority-description">Impede o trabalho</div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="prioridade" id="prioridadeInput" value="media">
                    
                    <div class="help-text" style="margin-top: 20px;">
                        A prioridade ajuda nossa equipe a organizar os atendimentos. Seja honesto para que possamos ajudar da melhor forma.
                    </div>
                </div>

                <!-- Step 4: Revisão -->
                <div class="form-section" data-section="4">
                    <h4 style="margin-bottom: 25px; font-weight: 600;">Revise as informações do chamado</h4>
                    
                    <div class="summary-section">
                        <div class="summary-title">Resumo do Chamado</div>
                        
                        <div class="summary-item">
                            <span class="summary-label">Categoria:</span>
                            <span class="summary-value" id="summaryCategoria">-</span>
                        </div>
                        
                        <div class="summary-item">
                            <span class="summary-label">Subcategoria:</span>
                            <span class="summary-value" id="summarySubcategoria">-</span>
                        </div>
                        
                        <div class="summary-item">
                            <span class="summary-label">Título:</span>
                            <span class="summary-value" id="summaryTitulo">-</span>
                        </div>
                        
                        <div class="summary-item">
                            <span class="summary-label">Descrição:</span>
                            <span class="summary-value" id="summaryDescricao">-</span>
                        </div>
                        
                        <div class="summary-item">
                            <span class="summary-label">Prioridade:</span>
                            <span class="summary-value" id="summaryPrioridade">Média</span>
                        </div>
                        
                        <div class="summary-item">
                            <span class="summary-label">Arquivos anexados:</span>
                            <span class="summary-value" id="summaryArquivos">Nenhum</span>
                        </div>
                    </div>
                    
                    <div class="alert alert-info" style="border-radius: 10px;">
                        <strong>📧 Você receberá um email</strong> com o número do protocolo e atualizações sobre seu chamado.
                    </div>
                </div>

                <!-- Actions -->
                <div class="wizard-actions">
                    <button type="button" class="btn btn-wizard btn-prev" id="btnPrev" onclick="prevStep()" style="display: none;">
                        ← Voltar
                    </button>
                    <div></div>
                    <button type="button" class="btn btn-wizard btn-next" id="btnNext" onclick="nextStep()">
                        Próximo →
                    </button>
                    <button type="submit" class="btn btn-wizard btn-submit" id="btnSubmit" style="display: none;">
                        🚀 Abrir Chamado
                    </button>
                </div>
            </form>
        </div>
    </div>

    <footer class="bg-primary text-white text-center py-4 mt-5">
        <div class="container">
            <p class="mb-2">© 2025 NetoNerd - Todos os direitos reservados</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        let currentStep = 1;
        const totalSteps = 4;
        let selectedFiles = [];
        
        const subcategorias = <?php echo json_encode($categorias); ?>;
        
        // Navegação entre steps
        function nextStep() {
            if (!validateStep(currentStep)) {
                return;
            }
            
            if (currentStep < totalSteps) {
                document.querySelector(`[data-section="${currentStep}"]`).classList.remove('active');
                document.querySelector(`[data-step="${currentStep}"]`).classList.add('completed');
                
                currentStep++;
                
                document.querySelector(`[data-section="${currentStep}"]`).classList.add('active');
                document.querySelector(`[data-step="${currentStep}"]`).classList.add('active');
                
                updateButtons();
                updateProgress();
                
                if (currentStep === 4) {
                    updateSummary();
                }
            }
        }
        
        function prevStep() {
            if (currentStep > 1) {
                document.querySelector(`[data-section="${currentStep}"]`).classList.remove('active');
                document.querySelector(`[data-step="${currentStep}"]`).classList.remove('active');
                
                currentStep--;
                
                document.querySelector(`[data-section="${currentStep}"]`).classList.add('active');
                document.querySelector(`[data-step="${currentStep + 1}"]`).classList.remove('completed');
                
                updateButtons();
                updateProgress();
            }
        }
        
        function updateButtons() {
            document.getElementById('btnPrev').style.display = currentStep === 1 ? 'none' : 'block';
            document.getElementById('btnNext').style.display = currentStep === totalSteps ? 'none' : 'block';
            document.getElementById('btnSubmit').style.display = currentStep === totalSteps ? 'block' : 'none';
        }
        
        function updateProgress() {
            const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
            document.getElementById('stepsProgress').style.width = progress + '%';
        }
        
        // Validação
        function validateStep(step) {
            switch(step) {
                case 1:
                    if (!document.getElementById('categoriaInput').value) {
                        alert('Por favor, selecione uma categoria');
                        return false;
                    }
                    if (!document.getElementById('subcategoriaSelect').value) {
                        alert('Por favor, selecione uma subcategoria');
                        return false;
                    }
                    return true;
                    
                case 2:
                    if (!document.getElementById('tituloInput').value.trim()) {
                        alert('Por favor, preencha o título do chamado');
                        return false;
                    }
                    if (!document.getElementById('descricaoInput').value.trim()) {
                        alert('Por favor, descreva o problema');
                        return false;
                    }
                    return true;
                    
                case 3:
                    return true; // Prioridade já tem valor padrão
                    
                default:
                    return true;
            }
        }
        
        // Seleção de categoria
        function selectCategory(category) {
            document.querySelectorAll('.category-card').forEach(card => {
                card.classList.remove('selected');
            });
            event.currentTarget.classList.add('selected');
            
            document.getElementById('categoriaInput').value = category;
            
            // Preencher subcategorias
            const select = document.getElementById('subcategoriaSelect');
            select.innerHTML = '<option value="">Selecione...</option>';
            
            if (subcategorias[category]) {
                subcategorias[category].forEach(sub => {
                    const option = document.createElement('option');
                    option.value = sub;
                    option.textContent = sub;
                    select.appendChild(option);
                });
            }
            
            document.getElementById('subcategorySection').style.display = 'block';
        }
        
        // Seleção de prioridade
        function selectPriority(priority, element) {
            document.querySelectorAll('.priority-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            element.classList.add('selected');
            document.getElementById('prioridadeInput').value = priority;
        }
        
        // Contadores de caracteres
        document.getElementById('tituloInput').addEventListener('input', function() {
            document.getElementById('tituloCounter').textContent = this.value.length;
        });
        
        document.getElementById('descricaoInput').addEventListener('input', function() {
            document.getElementById('descricaoCounter').textContent = this.value.length;
        });
        
        // Upload de arquivos
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const fileList = document.getElementById('fileList');
        
        dropZone.addEventListener('click', () => fileInput.click());
        
        dropZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            dropZone.classList.add('dragover');
        });
        
        dropZone.addEventListener('dragleave', () => {
            dropZone.classList.remove('dragover');
        });
        
        dropZone.addEventListener('drop', (e) => {
            e.preventDefault();
            dropZone.classList.remove('dragover');
            handleFiles(e.dataTransfer.files);
        });
        
        fileInput.addEventListener('change', (e) => {
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
            const fileItem = document.createElement('div');
            fileItem.className = 'file-item';
            fileItem.innerHTML = `
                <div class="file-info">
                    <span class="file-icon">📄</span>
                    <div>
                        <div class="file-name">${file.name}</div>
                        <span class="file-size">${formatFileSize(file.size)}</span>
                    </div>
                </div>
                <span class="btn-remove-file" onclick="removeFile('${file.name}')">×</span>
            `;
            fileList.appendChild(fileItem);
        }
        
        function removeFile(fileName) {
            selectedFiles = selectedFiles.filter(f => f.name !== fileName);
            const fileItems = fileList.querySelectorAll('.file-item');
            fileItems.forEach(item => {
                if (item.querySelector('.file-name').textContent === fileName) {
                    item.remove();
                }
            });
        }
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
        }
        
        // Atualizar resumo
        function updateSummary() {
            const categoria = document.getElementById('categoriaInput').value;
            const subcategoria = document.getElementById('subcategoriaSelect').value;
            const titulo = document.getElementById('tituloInput').value;
            const descricao = document.getElementById('descricaoInput').value;
            const prioridade = document.getElementById('prioridadeInput').value;
            
            document.getElementById('summaryCategoria').textContent = categoria || '-';
            document.getElementById('summarySubcategoria').textContent = subcategoria || '-';
            document.getElementById('summaryTitulo').textContent = titulo || '-';
            document.getElementById('summaryDescricao').textContent = descricao.substring(0, 100) + (descricao.length > 100 ? '...' : '') || '-';
            document.getElementById('summaryPrioridade').textContent = prioridade.charAt(0).toUpperCase() + prioridade.slice(1);
            document.getElementById('summaryArquivos').textContent = selectedFiles.length > 0 ? 
                `${selectedFiles.length} arquivo(s)` : 'Nenhum';
        }
        
        // Submissão do formulário
        document.getElementById('chamadoForm').addEventListener('submit', function(e) {
            if (!confirm('Tem certeza que deseja abrir este chamado?')) {
                e.preventDefault();
                return false;
            }
            
            // Mostra loading
            const btn = document.getElementById('btnSubmit');
            btn.innerHTML = '⏳ Enviando...';
            btn.disabled = true;
        });
        
        // Inicializar
        updateProgress();
        
        // Selecionar prioridade média por padrão
        document.querySelector('.priority-media').classList.add('selected');
    </script>
</body>
</html>