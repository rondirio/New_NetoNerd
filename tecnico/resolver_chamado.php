<?php
/**
 * Resolver Chamado - NetoNerd ITSM v2.0
 * Formulário completo de resolução com campos obrigatórios
 */

session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

// PROTEÇÃO: Apenas técnicos e admins
requireTecnico();

$conn = getConnection();
$tecnico_id = $_SESSION['usuario_id'];
$chamado_id = intval($_GET['id'] ?? 0);

if ($chamado_id === 0) {
    header('Location: meus_chamados.php?erro=chamado_invalido');
    exit();
}

// Buscar dados do chamado
$stmt = $conn->prepare("
    SELECT
        c.*,
        cl.nome as cliente_nome,
        cl.email as cliente_email,
        cl.telefone as cliente_telefone,
        cl.endereco as cliente_endereco
    FROM chamados c
    INNER JOIN clientes cl ON c.cliente_id = cl.id
    WHERE c.id = ? AND c.tecnico_id = ?
");
$stmt->bind_param("ii", $chamado_id, $tecnico_id);
$stmt->execute();
$chamado = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$chamado) {
    header('Location: meus_chamados.php?erro=chamado_nao_encontrado');
    exit();
}

if ($chamado['status'] === 'resolvido') {
    header('Location: meus_chamados.php?erro=chamado_ja_resolvido');
    exit();
}

// Buscar atualizações do chamado
$stmt = $conn->prepare("
    SELECT * FROM chamado_atualizacoes
    WHERE chamado_id = ?
    ORDER BY data_atualizacao DESC
");
$stmt->bind_param("i", $chamado_id);
$stmt->execute();
$atualizacoes = $stmt->get_result();

// Configuração da página
$page_title = "Resolver Chamado #" . $chamado['protocolo'] . " - NetoNerd ITSM";

// Incluir header
require_once '../includes/header.php';
?>

<!-- Conteúdo Principal -->
<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <!-- Cabeçalho da Página -->
        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h1 class="nn-card-title">
                    <i class="fas fa-check-circle"></i>
                    Resolver Chamado #<?php echo htmlspecialchars($chamado['protocolo']); ?>
                </h1>
                <div>
                    <a href="meus_chamados.php" class="nn-btn nn-btn-secondary">
                        <i class="fas fa-arrow-left"></i>
                        Voltar
                    </a>
                </div>
            </div>
        </div>

        <!-- Alertas -->
        <?php if (isset($_GET['erro'])): ?>
            <div class="nn-alert nn-alert-danger nn-animate-fade">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($_GET['erro']); ?>
            </div>
        <?php endif; ?>

        <!-- Informações do Chamado -->
        <div class="nn-card nn-animate-slide">
            <div class="nn-card-header">
                <h2 class="nn-card-title">
                    <i class="fas fa-info-circle"></i>
                    Informações do Chamado
                </h2>
            </div>
            <div class="nn-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5><strong><?php echo htmlspecialchars($chamado['titulo']); ?></strong></h5>
                        <p class="mb-3"><?php echo nl2br(htmlspecialchars($chamado['descricao'])); ?></p>

                        <?php
                        $prioridadeClass = match(strtolower($chamado['prioridade'])) {
                            'critica' => 'nn-badge-critical',
                            'alta' => 'nn-badge-high',
                            'media' => 'nn-badge-medium',
                            'baixa' => 'nn-badge-low',
                            default => 'nn-badge-secondary'
                        };
                        ?>
                        <span class="nn-badge <?php echo $prioridadeClass; ?>">
                            <?php echo ucfirst($chamado['prioridade']); ?>
                        </span>

                        <hr class="my-3">

                        <div class="mb-2">
                            <i class="fas fa-user text-primary"></i>
                            <strong>Cliente:</strong>
                            <?php echo htmlspecialchars($chamado['cliente_nome']); ?>
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-phone text-success"></i>
                            <strong>Telefone:</strong>
                            <?php echo htmlspecialchars($chamado['cliente_telefone']); ?>
                        </div>
                        <?php if ($chamado['cliente_endereco']): ?>
                            <div class="mb-2">
                                <i class="fas fa-map-marker-alt text-danger"></i>
                                <strong>Endereço:</strong>
                                <?php echo htmlspecialchars($chamado['cliente_endereco']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-6">
                        <div class="mb-3">
                            <i class="fas fa-calendar text-primary"></i>
                            <strong>Aberto em:</strong><br>
                            <small><?php echo date('d/m/Y H:i', strtotime($chamado['data_abertura'])); ?></small>
                        </div>

                        <?php if ($chamado['data_inicio_atendimento']): ?>
                            <div class="mb-3">
                                <i class="fas fa-play text-success"></i>
                                <strong>Iniciado em:</strong><br>
                                <small><?php echo date('d/m/Y H:i', strtotime($chamado['data_inicio_atendimento'])); ?></small>
                            </div>

                            <?php
                            $minutos = (time() - strtotime($chamado['data_inicio_atendimento'])) / 60;
                            $horas = floor($minutos / 60);
                            $mins = $minutos % 60;
                            ?>
                            <div class="mb-3">
                                <i class="fas fa-clock text-info"></i>
                                <strong>Tempo de atendimento:</strong><br>
                                <small><?php echo $horas; ?>h <?php echo floor($mins); ?>min</small>
                            </div>
                        <?php endif; ?>

                        <?php if ($atualizacoes->num_rows > 0): ?>
                            <hr>
                            <strong><i class="fas fa-history"></i> Histórico de Atualizações:</strong>
                            <div class="mt-2" style="max-height: 200px; overflow-y: auto;">
                                <?php while ($atu = $atualizacoes->fetch_assoc()): ?>
                                    <div class="nn-alert nn-alert-secondary mb-2">
                                        <small>
                                            <strong><?php echo ucfirst(str_replace('_', ' ', $atu['tipo_atualizacao'])); ?></strong><br>
                                            <?php echo htmlspecialchars($atu['descricao']); ?><br>
                                            <em><?php echo date('d/m/Y H:i', strtotime($atu['data_atualizacao'])); ?></em>
                                        </small>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Formulário de Resolução -->
        <form action="processar_resolucao.php" method="POST" enctype="multipart/form-data" id="form_resolucao">
            <input type="hidden" name="chamado_id" value="<?php echo $chamado['id']; ?>">

            <!-- 1. Histórico do Atendimento -->
            <div class="nn-card nn-animate-fade">
                <div class="nn-card-header">
                    <h2 class="nn-card-title">
                        <i class="fas fa-file-alt"></i>
                        Histórico Detalhado do Atendimento
                        <span class="text-danger">*</span>
                    </h2>
                </div>
                <div class="nn-card-body">
                    <p class="text-muted mb-3">
                        Descreva detalhadamente o que foi realizado, problemas encontrados e soluções aplicadas.
                        <strong>Mínimo 50 caracteres.</strong>
                    </p>
                    <textarea name="historico_atendimento" class="nn-form-control" rows="8" required placeholder="Exemplo:
- Chegada ao local às 14:30
- Identificado problema na fonte do computador
- Substituída fonte de 500W por nova fonte de 600W
- Testado sistema por 30 minutos sob carga
- Verificado temperaturas e estabilidade
- Todos os testes concluídos com sucesso
- Cliente orientado sobre manutenção preventiva"></textarea>
                </div>
            </div>

            <!-- 2. StyleManager Software -->
            <div class="nn-card nn-animate-fade">
                <div class="nn-card-header">
                    <h2 class="nn-card-title">
                        <i class="fas fa-code"></i>
                        Tipo de Serviço
                    </h2>
                </div>
                <div class="nn-card-body">
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="stylemanager_software"
                               name="stylemanager_software" value="1" onchange="togglePagamento()"
                               style="width: 3em; height: 1.5em; cursor: pointer;">
                        <label class="form-check-label" for="stylemanager_software" style="margin-left: 0.5em; font-size: 1.1rem; cursor: pointer;">
                            <strong>Este atendimento foi suporte ao StyleManager (software)?</strong>
                        </label>
                    </div>

                    <div class="nn-alert nn-alert-info" id="stylemanager_info" style="display: none;">
                        <i class="fas fa-info-circle"></i>
                        <strong>Serviços StyleManager (software) não são cobrados</strong><br>
                        <small>Estes serviços são considerados suporte técnico ou possíveis erros de desenvolvimento e não geram cobrança ao cliente.</small>
                    </div>
                </div>
            </div>

            <!-- 3. Forma de Pagamento -->
            <div class="nn-card nn-animate-fade" id="pagamento_container">
                <div class="nn-card-header">
                    <h2 class="nn-card-title">
                        <i class="fas fa-credit-card"></i>
                        Forma de Pagamento
                        <span class="text-danger" id="pagamento_required">*</span>
                    </h2>
                </div>
                <div class="nn-card-body">
                    <p class="text-muted mb-3">Selecione como o cliente pagou pelo serviço.</p>

                    <div class="row g-3">
                        <div class="col-md-3 col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pagamento_forma"
                                       id="pag_pix" value="PIX" required>
                                <label class="form-check-label" for="pag_pix">
                                    <i class="fas fa-qrcode text-primary"></i> PIX
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pagamento_forma"
                                       id="pag_dinheiro" value="Dinheiro">
                                <label class="form-check-label" for="pag_dinheiro">
                                    <i class="fas fa-money-bill text-success"></i> Dinheiro
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pagamento_forma"
                                       id="pag_cartao" value="Cartão">
                                <label class="form-check-label" for="pag_cartao">
                                    <i class="fas fa-credit-card text-info"></i> Cartão
                                </label>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="pagamento_forma"
                                       id="pag_debito" value="Débito">
                                <label class="form-check-label" for="pag_debito">
                                    <i class="fas fa-credit-card text-warning"></i> Débito
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 4. Upload de Fotos -->
            <div class="nn-card nn-animate-fade">
                <div class="nn-card-header">
                    <h2 class="nn-card-title">
                        <i class="fas fa-camera"></i>
                        Fotos do Serviço Realizado
                        <span class="text-danger">*</span>
                    </h2>
                </div>
                <div class="nn-card-body">
                    <p class="text-muted mb-3">
                        Adicione pelo menos 1 foto do serviço realizado. Você pode adicionar múltiplas fotos.
                    </p>

                    <input type="file" name="fotos[]" id="fotos" class="nn-form-control"
                           accept="image/*" multiple required>

                    <div id="preview_container" style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 15px;"></div>

                    <small class="text-muted mt-2 d-block">
                        <i class="fas fa-info-circle"></i>
                        Formatos aceitos: JPG, PNG, GIF. Tamanho máximo por foto: 5MB
                    </small>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="nn-card nn-animate-fade">
                <div class="nn-card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="nn-btn nn-btn-success nn-btn-lg">
                            <i class="fas fa-check-circle"></i>
                            Resolver e Finalizar Chamado
                        </button>
                        <a href="meus_chamados.php" class="nn-btn nn-btn-secondary nn-btn-lg">
                            <i class="fas fa-times"></i>
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </form>

    </div>
</div>

<style>
/* Estilo específico para o container de pagamento desabilitado */
#pagamento_container.disabled {
    opacity: 0.5;
    pointer-events: none;
}

#pagamento_container.disabled .nn-card-body {
    background-color: #f0f0f0;
}

/* Preview de imagens */
#preview_container img {
    max-width: 150px;
    max-height: 150px;
    border: 2px solid var(--border-color);
    border-radius: 8px;
    object-fit: cover;
}
</style>

<script>
// Toggle de pagamento quando StyleManager é marcado
function togglePagamento() {
    const checkbox = document.getElementById('stylemanager_software');
    const pagamentoContainer = document.getElementById('pagamento_container');
    const stylemanagerInfo = document.getElementById('stylemanager_info');
    const radiosPagamento = document.querySelectorAll('input[name="pagamento_forma"]');

    if (checkbox.checked) {
        // StyleManager marcado: desabilitar pagamento
        pagamentoContainer.classList.add('disabled');
        stylemanagerInfo.style.display = 'block';

        // Remover obrigatoriedade dos radios
        radiosPagamento.forEach(radio => {
            radio.required = false;
            radio.checked = false;
        });
    } else {
        // StyleManager desmarcado: habilitar pagamento
        pagamentoContainer.classList.remove('disabled');
        stylemanagerInfo.style.display = 'none';

        // Adicionar obrigatoriedade aos radios
        radiosPagamento.forEach(radio => {
            radio.required = true;
        });
    }
}

// Preview de imagens
document.getElementById('fotos').addEventListener('change', function(e) {
    const previewContainer = document.getElementById('preview_container');
    previewContainer.innerHTML = '';

    const files = e.target.files;

    if (files.length === 0) return;

    for (let i = 0; i < files.length; i++) {
        const file = files[i];

        // Validar tamanho (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('Arquivo ' + file.name + ' é muito grande. Máximo: 5MB');
            continue;
        }

        // Validar tipo
        if (!file.type.match('image.*')) {
            alert('Arquivo ' + file.name + ' não é uma imagem');
            continue;
        }

        // Criar preview
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'img-thumbnail';
            previewContainer.appendChild(img);
        };
        reader.readAsDataURL(file);
    }
});

// Validação do formulário antes de enviar
document.getElementById('form_resolucao').addEventListener('submit', function(e) {
    const historico = document.querySelector('textarea[name="historico_atendimento"]').value.trim();
    const stylemanager = document.getElementById('stylemanager_software').checked;
    const pagamentoSelecionado = document.querySelector('input[name="pagamento_forma"]:checked');
    const fotos = document.getElementById('fotos').files.length;

    // Validar histórico
    if (historico.length < 50) {
        alert('O histórico do atendimento deve ter pelo menos 50 caracteres. Seja mais detalhado!');
        e.preventDefault();
        return false;
    }

    // Validar pagamento (se não for StyleManager)
    if (!stylemanager && !pagamentoSelecionado) {
        alert('Selecione a forma de pagamento!');
        e.preventDefault();
        return false;
    }

    // Validar fotos
    if (fotos === 0) {
        alert('Adicione pelo menos 1 foto do serviço realizado!');
        e.preventDefault();
        return false;
    }

    // Confirmação final
    if (!confirm('Tem certeza que deseja finalizar este chamado? Esta ação não pode ser desfeita.')) {
        e.preventDefault();
        return false;
    }

    return true;
});
</script>

<?php
// Incluir footer
$stmt->close();
$conn->close();
require_once '../includes/footer.php';
?>
