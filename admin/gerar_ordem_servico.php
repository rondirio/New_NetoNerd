<?php
/**
 * Gerar Ordem de Serviço - NetoNerd ITSM v2.0
 * Sistema inteligente: busca cliente existente ou permite novo
 */
session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireAdmin();

$conn = getConnection();

// Se vier de um chamado específico
$chamado_id = isset($_GET['chamado']) ? intval($_GET['chamado']) : null;
$chamado_data = null;

if ($chamado_id) {
    $sql = "
        SELECT 
            c.*,
            IFNULL(cl.nome, c.nome_usuario) as cliente_nome,
            cl.email as cliente_email,
            cl.telefone as cliente_telefone,
            cl.id as cliente_id,
            t.nome as tecnico_nome,
            t.id as tecnico_id
        FROM chamados c
        LEFT JOIN clientes cl ON c.cliente_id = cl.id
        LEFT JOIN tecnicos t ON c.tecnico_id = t.id
        WHERE c.id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $chamado_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $chamado_data = $result->fetch_assoc();
    }
    $stmt->close();
}

// Buscar técnicos ativos
$tecnicos = $conn->query("
    SELECT id, nome, matricula 
    FROM tecnicos 
    WHERE Ativo = 1 
    ORDER BY nome
");

// Gerar número da OS
$ano_atual = date('Y');
$sql_count = "SELECT COUNT(*) as total FROM ordens_servico WHERE YEAR(data_criacao) = ?";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param('i', $ano_atual);
$stmt_count->execute();
$count_result = $stmt_count->get_result()->fetch_assoc();
$proximo_numero = str_pad($count_result['total'] + 1, 4, '0', STR_PAD_LEFT);
$numero_os = "OS{$ano_atual}{$proximo_numero}";
$stmt_count->close();

$page_title = "Gerar Ordem de Serviço - NetoNerd ITSM";
require_once '../includes/header.php';
?>

<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <!-- Cabeçalho -->
        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h1 class="nn-card-title">
                    <i class="fas fa-file-invoice"></i>
                    Gerar Ordem de Serviço
                </h1>
                <div style="display:flex; gap:10px;">
                    <a href="dashboard.php" class="nn-btn nn-btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                
                    <a href="visualizar_ordem_servico.php" class="nn-btn nn-btn-secondary">
                        <i class="far fa-eye"></i> Visualizar ordens
                    </a>
                </div>
            </div>
        </div>

        <!-- Formulário -->
        <form action="processar_ordem_servico.php" method="POST" id="formOS">
            <div class="row">
                <!-- Coluna Esquerda -->
                <div class="col-lg-8">
                    
                    <!-- Dados da OS -->
                    <div class="nn-card nn-animate-slide">
                        <div class="nn-card-header">
                            <h2 class="nn-card-title">
                                <i class="fas fa-info-circle"></i>
                                Dados da Ordem de Serviço
                            </h2>
                        </div>
                        <div class="nn-card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="nn-form-group">
                                        <label class="nn-form-label">Número da OS</label>
                                        <input type="text" name="numero_os" class="nn-form-control" 
                                               value="<?= $numero_os ?>" readonly 
                                               style="background: #e9ecef; font-weight: bold; font-size: 1.2em;">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="nn-form-group">
                                        <label class="nn-form-label">Data</label>
                                        <input type="text" class="nn-form-control" 
                                               value="<?= date('d/m/Y H:i') ?>" readonly
                                               style="background: #e9ecef;">
                                    </div>
                                </div>
                            </div>

                            <?php if ($chamado_id): ?>
                                <input type="hidden" name="chamado_id" value="<?= $chamado_id ?>">
                                <div class="nn-alert nn-alert-info">
                                    <i class="fas fa-link"></i>
                                    <strong>Vinculado ao Chamado:</strong> #<?= htmlspecialchars($chamado_data['protocolo']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Dados do Cliente - NOVO SISTEMA -->
                    <div class="nn-card nn-animate-slide">
                        <div class="nn-card-header">
                            <h2 class="nn-card-title">
                                <i class="fas fa-user"></i>
                                Dados do Cliente
                            </h2>
                            <span class="nn-badge nn-badge-info">
                                <i class="fas fa-info-circle"></i>
                                Sistema inteligente: busca automática por nome e telefone
                            </span>
                        </div>
                        <div class="nn-card-body">
                            
                            <!-- Cliente será vinculado automaticamente se existir -->
                            <input type="hidden" name="cliente_id" id="cliente_id" value="">
                            
                            <div class="nn-alert nn-alert-info" id="cliente-status" style="display: none;">
                                <i class="fas fa-check-circle"></i>
                                <span id="cliente-status-text"></span>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="nn-form-group">
                                        <label class="nn-form-label">Nome Completo *</label>
                                        <input type="text" 
                                               name="cliente_nome" 
                                               id="cliente_nome" 
                                               class="nn-form-control" 
                                               value="<?= $chamado_data['cliente_nome'] ?? '' ?>" 
                                               required
                                               onblur="buscarCliente()">
                                        <small class="text-muted">Digite o nome e telefone para buscar automaticamente</small>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="nn-form-group">
                                        <label class="nn-form-label">Telefone *</label>
                                        <input type="text" 
                                               name="cliente_telefone" 
                                               id="cliente_telefone" 
                                               class="nn-form-control"
                                               value="<?= $chamado_data['cliente_telefone'] ?? '' ?>"
                                               required
                                               onblur="buscarCliente()"
                                               placeholder="(00) 00000-0000">
                                        <small class="text-muted">Obrigatório para identificação</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="nn-form-group">
                                        <label class="nn-form-label">Email</label>
                                        <input type="email" 
                                               name="cliente_email" 
                                               id="cliente_email" 
                                               class="nn-form-control"
                                               value="<?= $chamado_data['cliente_email'] ?? '' ?>"
                                               placeholder="email@exemplo.com">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="nn-form-group">
                                        <label class="nn-form-label">CPF</label>
                                        <input type="text" 
                                               name="cliente_cpf" 
                                               id="cliente_cpf" 
                                               class="nn-form-control"
                                               placeholder="000.000.000-00">
                                    </div>
                                </div>
                            </div>

                            <div class="nn-form-group">
                                <label class="nn-form-label">Endereço</label>
                                <input type="text" 
                                       name="cliente_endereco" 
                                       id="cliente_endereco" 
                                       class="nn-form-control"
                                       placeholder="Rua, número, bairro, cidade">
                            </div>

                            <!-- Botão para cadastrar cliente (se não existir) -->
                            <div id="cadastrar-cliente-box" style="display: none;">
                                <div class="nn-alert nn-alert-warning">
                                    <i class="fas fa-user-plus"></i>
                                    Cliente não encontrado no sistema. 
                                    <label style="margin-left: 10px;">
                                        <input type="checkbox" name="cadastrar_cliente" id="cadastrar_cliente" value="1">
                                        Cadastrar este cliente no sistema após salvar a OS
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Dados do Equipamento -->
                    <div class="nn-card nn-animate-slide">
                        <div class="nn-card-header">
                            <h2 class="nn-card-title">
                                <i class="fas fa-laptop"></i>
                                Dados do Equipamento
                            </h2>
                        </div>
                        <div class="nn-card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="nn-form-group">
                                        <label class="nn-form-label">Tipo</label>
                                        <input type="text" name="equipamento_tipo" class="nn-form-control" 
                                               placeholder="Ex: Notebook, Desktop, Impressora">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="nn-form-group">
                                        <label class="nn-form-label">Marca</label>
                                        <input type="text" name="equipamento_marca" class="nn-form-control" 
                                               placeholder="Ex: Dell, HP, Lenovo">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="nn-form-group">
                                        <label class="nn-form-label">Modelo</label>
                                        <input type="text" name="equipamento_modelo" class="nn-form-control">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="nn-form-group">
                                        <label class="nn-form-label">Número de Série</label>
                                        <input type="text" name="equipamento_serial" class="nn-form-control">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Descrição do Serviço -->
                    <div class="nn-card nn-animate-slide">
                        <div class="nn-card-header">
                            <h2 class="nn-card-title">
                                <i class="fas fa-clipboard-list"></i>
                                Descrição do Serviço
                            </h2>
                        </div>
                        <div class="nn-card-body">
                            <div class="nn-form-group">
                                <label class="nn-form-label">Problema Relatado *</label>
                                <textarea name="problema_relatado" class="nn-form-control" rows="4" required 
                                          placeholder="Descreva o problema relatado pelo cliente..."><?= $chamado_data['descricao'] ?? '' ?></textarea>
                            </div>

                            <div class="nn-form-group">
                                <label class="nn-form-label">Serviços Executados</label>
                                <textarea name="servicos_executados" class="nn-form-control" rows="4" 
                                          placeholder="Descreva os serviços realizados..."></textarea>
                            </div>

                            <div class="nn-form-group">
                                <label class="nn-form-label">Peças Utilizadas</label>
                                <textarea name="pecas_utilizadas" class="nn-form-control" rows="3" 
                                          placeholder="Liste as peças substituídas ou utilizadas..."></textarea>
                            </div>

                            <div class="nn-form-group">
                                <label class="nn-form-label">Observações</label>
                                <textarea name="observacoes" class="nn-form-control" rows="3" 
                                          placeholder="Observações adicionais..."></textarea>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Coluna Direita -->
                <div class="col-lg-4">
                    
                    <!-- Técnico Responsável -->
                    <div class="nn-card nn-animate-slide">
                        <div class="nn-card-header">
                            <h3 class="nn-card-title">
                                <i class="fas fa-user-cog"></i>
                                Técnico Responsável
                            </h3>
                        </div>
                        <div class="nn-card-body">
                            <div class="nn-form-group">
                                <label class="nn-form-label">Técnico *</label>
                                <select name="tecnico_id" class="nn-form-control" required>
                                    <option value="">Selecione...</option>
                                    <?php 
                                    $tecnicos->data_seek(0);
                                    while ($tec = $tecnicos->fetch_assoc()): 
                                    ?>
                                        <option value="<?= $tec['id'] ?>" 
                                                <?= ($chamado_data && $chamado_data['tecnico_id'] == $tec['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($tec['nome']) ?> (<?= htmlspecialchars($tec['matricula']) ?>)
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Valores -->
                    <div class="nn-card nn-animate-slide">
                        <div class="nn-card-header">
                            <h3 class="nn-card-title">
                                <i class="fas fa-dollar-sign"></i>
                                Valores
                            </h3>
                        </div>
                        <div class="nn-card-body">
                            <div class="nn-form-group">
                                <label class="nn-form-label">Mão de Obra (R$)</label>
                                <input type="number" name="valor_mao_obra" id="valor_mao_obra" class="nn-form-control" 
                                       step="0.01" min="0" value="0.00" onchange="calcularTotal()">
                            </div>

                            <div class="nn-form-group">
                                <label class="nn-form-label">Peças (R$)</label>
                                <input type="number" name="valor_pecas" id="valor_pecas" class="nn-form-control" 
                                       step="0.01" min="0" value="0.00" onchange="calcularTotal()">
                            </div>

                            <div class="nn-form-group">
                                <label class="nn-form-label" style="font-size: 1.2em; font-weight: bold;">Total (R$)</label>
                                <input type="number" name="valor_total" id="valor_total" class="nn-form-control" 
                                       step="0.01" min="0" value="0.00" readonly 
                                       style="background: #e9ecef; font-weight: bold; font-size: 1.3em; color: var(--success);">
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="nn-card nn-animate-slide">
                        <div class="nn-card-header">
                            <h3 class="nn-card-title">
                                <i class="fas fa-tasks"></i>
                                Status
                            </h3>
                        </div>
                        <div class="nn-card-body">
                            <div class="nn-form-group">
                                <label class="nn-form-label">Status da OS *</label>
                                <select name="status" class="nn-form-control" required>
                                    <option value="aberta">Aberta</option>
                                    <option value="em_andamento">Em Andamento</option>
                                    <option value="concluida">Concluída</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Ações -->
                    <div class="nn-card nn-animate-slide">
                        <div class="nn-card-body">
                            <button type="submit" name="acao" value="salvar" class="nn-btn nn-btn-success w-100 mb-2">
                                <i class="fas fa-save"></i>
                                Salvar Ordem de Serviço
                            </button>

                            <button type="submit" name="acao" value="salvar_e_imprimir" class="nn-btn nn-btn-primary w-100 mb-2">
                                <i class="fas fa-print"></i>
                                Salvar e Imprimir
                            </button>

                            <a href="dashboard.php" class="nn-btn nn-btn-secondary w-100">
                                <i class="fas fa-times"></i>
                                Cancelar
                            </a>
                        </div>
                    </div>

                </div>
            </div>
        </form>

    </div>
</div>

<script>
// Calcular total
function calcularTotal() {
    const maoObra = parseFloat(document.getElementById('valor_mao_obra').value) || 0;
    const pecas = parseFloat(document.getElementById('valor_pecas').value) || 0;
    const total = maoObra + pecas;
    
    document.getElementById('valor_total').value = total.toFixed(2);
}

// Buscar cliente existente por nome e telefone
async function buscarCliente() {
    const nome = document.getElementById('cliente_nome').value.trim();
    const telefone = document.getElementById('cliente_telefone').value.trim();
    
    if (!nome || !telefone) {
        return;
    }
    
    try {
        const response = await fetch('buscar_cliente_ajax.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `nome=${encodeURIComponent(nome)}&telefone=${encodeURIComponent(telefone)}`
        });
        
        const data = await response.json();
        
        if (data.encontrado) {
            // Cliente encontrado - preencher dados
            document.getElementById('cliente_id').value = data.cliente.id;
            document.getElementById('cliente_email').value = data.cliente.email || '';
            document.getElementById('cliente_endereco').value = data.cliente.endereco || '';
            document.getElementById('cliente_cpf').value = data.cliente.cpf || '';
            
            // Mostrar mensagem de cliente encontrado
            document.getElementById('cliente-status').style.display = 'block';
            document.getElementById('cliente-status-text').textContent = 
                `Cliente encontrado no sistema! ID: ${data.cliente.id}`;
            document.getElementById('cadastrar-cliente-box').style.display = 'none';
        } else {
            // Cliente não encontrado
            document.getElementById('cliente_id').value = '';
            document.getElementById('cliente-status').style.display = 'none';
            document.getElementById('cadastrar-cliente-box').style.display = 'block';
        }
    } catch (error) {
        console.error('Erro ao buscar cliente:', error);
    }
}

// Inicialização
document.addEventListener('DOMContentLoaded', function() {
    calcularTotal();
    
    <?php if ($chamado_data): ?>
        buscarCliente();
    <?php endif; ?>
});
</script>

<?php
$conn->close();
require_once '../includes/footer.php';
?>