<?php
/**
 * Visualizar Ordem de Serviço - NetoNerd ITSM v2.0
 * Com edição de status e exclusão
 */
session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireAdmin();

$conn = getConnection();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: listar_ordens_servico.php?erro=id_invalido');
    exit();
}

$os_id = intval($_GET['id']);

// Buscar OS com dados do técnico - CORRIGIDO: created_by aponta para tecnicos
$sql = "
    SELECT 
        os.*,
        t.nome as tecnico_nome,
        t.matricula as tecnico_matricula,
        t.email as tecnico_email,
        tc.nome as criado_por_nome,
        tc.matricula as criado_por_matricula
    FROM ordens_servico os
    INNER JOIN tecnicos t ON os.tecnico_id = t.id
    INNER JOIN tecnicos tc ON os.created_by = tc.id
    WHERE os.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $os_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: listar_ordens_servico.php?erro=os_nao_encontrada');
    exit();
}

$os = $result->fetch_assoc();
$stmt->close();

// Determinar cor do status
$status_color = match($os['status']) {
    'aberta' => '#007bff',
    'em_andamento' => '#17a2b8',
    'concluida' => '#28a745',
    'cancelada' => '#dc3545',
    default => '#6c757d'
};

$status_texto = match($os['status']) {
    'aberta' => 'ABERTA',
    'em_andamento' => 'EM ANDAMENTO',
    'concluida' => 'CONCLUÍDA',
    'cancelada' => 'CANCELADA',
    default => strtoupper($os['status'])
};

$page_title = "OS " . $os['numero_os'] . " - NetoNerd ITSM";
require_once '../includes/header.php';
?>

<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <!-- Cabeçalho -->
        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <div>
                    <h1 class="nn-card-title">
                        <i class="fas fa-file-invoice"></i>
                        Ordem de Serviço: <?= htmlspecialchars($os['numero_os']) ?>
                    </h1>
                    <p class="text-muted mb-0">
                        <i class="fas fa-calendar"></i>
                        Criada em <?= date('d/m/Y \à\s H:i', strtotime($os['data_criacao'])) ?>
                        por <?= htmlspecialchars($os['criado_por_nome']) ?>
                        <?php if (isset($os['criado_por_matricula'])): ?>
                            <span class="nn-badge nn-badge-secondary"><?= htmlspecialchars($os['criado_por_matricula']) ?></span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="nn-badge" style="background: <?= $status_color ?>; color: white; font-size: 16px; padding: 10px 20px;">
                        <?= $status_texto ?>
                    </span>
                    
                    <a href="imprimir_ordem_servico.php?id=<?= $os_id ?>" 
                       class="nn-btn nn-btn-primary" 
                       target="_blank">
                        <i class="fas fa-print"></i> Imprimir
                    </a>
                    
                    <a href="listar_ordens_servico.php" class="nn-btn nn-btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
        </div>

        <!-- Alertas -->
        <?php if (isset($_GET['sucesso'])): ?>
            <div class="nn-alert nn-alert-success nn-animate-fade">
                <i class="fas fa-check-circle"></i>
                <?php
                if (isset($_GET['msg'])) {
                    echo htmlspecialchars($_GET['msg']);
                } else {
                    switch($_GET['sucesso']) {
                        case 'status_atualizado':
                            echo 'Status da ordem atualizado com sucesso!';
                            break;
                        default:
                            echo 'Operação realizada com sucesso!';
                    }
                }
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['erro'])): ?>
            <div class="nn-alert nn-alert-danger nn-animate-fade">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($_GET['erro']); ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Coluna Principal -->
            <div class="col-lg-8">
                
                <!-- Dados do Cliente -->
                <div class="nn-card nn-animate-slide">
                    <div class="nn-card-header">
                        <h2 class="nn-card-title">
                            <i class="fas fa-user"></i>
                            Dados do Cliente
                        </h2>
                        <?php if ($os['cliente_id']): ?>
                            <span class="nn-badge nn-badge-success">
                                <i class="fas fa-check-circle"></i>
                                Cadastrado no Sistema (ID: <?= $os['cliente_id'] ?>)
                            </span>
                        <?php else: ?>
                            <span class="nn-badge nn-badge-warning">
                                <i class="fas fa-exclamation-circle"></i>
                                Não cadastrado
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="nn-card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="nn-form-label">
                                    <i class="fas fa-user"></i> Nome Completo
                                </label>
                                <div class="nn-info-value">
                                    <?= htmlspecialchars($os['cliente_nome']) ?>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="nn-form-label">
                                    <i class="fas fa-phone"></i> Telefone
                                </label>
                                <div class="nn-info-value">
                                    <a href="tel:<?= htmlspecialchars($os['cliente_telefone']) ?>">
                                        <?= htmlspecialchars($os['cliente_telefone']) ?>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="nn-form-label">
                                    <i class="fas fa-envelope"></i> Email
                                </label>
                                <div class="nn-info-value">
                                    <?php if ($os['cliente_email']): ?>
                                        <a href="mailto:<?= htmlspecialchars($os['cliente_email']) ?>">
                                            <?= htmlspecialchars($os['cliente_email']) ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Não informado</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="nn-form-label">
                                    <i class="fas fa-id-card"></i> CPF
                                </label>
                                <div class="nn-info-value">
                                    <?= $os['cliente_cpf'] ? htmlspecialchars($os['cliente_cpf']) : '<span class="text-muted">Não informado</span>' ?>
                                </div>
                            </div>
                        </div>

                        <?php if ($os['cliente_endereco']): ?>
                            <div class="row">
                                <div class="col-12">
                                    <label class="nn-form-label">
                                        <i class="fas fa-map-marker-alt"></i> Endereço
                                    </label>
                                    <div class="nn-info-value">
                                        <?= htmlspecialchars($os['cliente_endereco']) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Dados do Equipamento -->
                <?php if ($os['equipamento_tipo']): ?>
                <div class="nn-card nn-animate-slide">
                    <div class="nn-card-header">
                        <h2 class="nn-card-title">
                            <i class="fas fa-laptop"></i>
                            Dados do Equipamento
                        </h2>
                    </div>
                    <div class="nn-card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="nn-form-label">Tipo</label>
                                <div class="nn-info-value"><?= htmlspecialchars($os['equipamento_tipo']) ?></div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="nn-form-label">Marca</label>
                                <div class="nn-info-value">
                                    <?= $os['equipamento_marca'] ? htmlspecialchars($os['equipamento_marca']) : '<span class="text-muted">Não informado</span>' ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="nn-form-label">Modelo</label>
                                <div class="nn-info-value">
                                    <?= $os['equipamento_modelo'] ? htmlspecialchars($os['equipamento_modelo']) : '<span class="text-muted">Não informado</span>' ?>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="nn-form-label">Número de Série</label>
                                <div class="nn-info-value">
                                    <?= $os['equipamento_serial'] ? htmlspecialchars($os['equipamento_serial']) : '<span class="text-muted">Não informado</span>' ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Problema Relatado -->
                <div class="nn-card nn-animate-slide">
                    <div class="nn-card-header" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%); color: #000;">
                        <h2 class="nn-card-title" style="color: #000;">
                            <i class="fas fa-clipboard-list"></i>
                            Problema Relatado
                        </h2>
                    </div>
                    <div class="nn-card-body">
                        <div class="nn-info-text">
                            <?= nl2br(htmlspecialchars($os['problema_relatado'])) ?>
                        </div>
                    </div>
                </div>

                <!-- Serviços Executados -->
                <?php if ($os['servicos_executados']): ?>
                <div class="nn-card nn-animate-slide">
                    <div class="nn-card-header" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%); color: white;">
                        <h2 class="nn-card-title" style="color: white;">
                            <i class="fas fa-tools"></i>
                            Serviços Executados
                        </h2>
                    </div>
                    <div class="nn-card-body">
                        <div class="nn-info-text">
                            <?= nl2br(htmlspecialchars($os['servicos_executados'])) ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Peças Utilizadas -->
                <?php if ($os['pecas_utilizadas']): ?>
                <div class="nn-card nn-animate-slide">
                    <div class="nn-card-header" style="background: linear-gradient(135deg, #6c757d 0%, #545b62 100%); color: white;">
                        <h2 class="nn-card-title" style="color: white;">
                            <i class="fas fa-cogs"></i>
                            Peças Utilizadas
                        </h2>
                    </div>
                    <div class="nn-card-body">
                        <div class="nn-info-text">
                            <?= nl2br(htmlspecialchars($os['pecas_utilizadas'])) ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Observações -->
                <?php if ($os['observacoes']): ?>
                <div class="nn-card nn-animate-slide">
                    <div class="nn-card-header" style="background: linear-gradient(135deg, #6c757d 0%, #545b62 100%); color: white;">
                        <h2 class="nn-card-title" style="color: white;">
                            <i class="fas fa-comment"></i>
                            Observações
                        </h2>
                    </div>
                    <div class="nn-card-body">
                        <div class="nn-info-text">
                            <?= nl2br(htmlspecialchars($os['observacoes'])) ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- Coluna Lateral -->
            <div class="col-lg-4">
                
                <!-- Ações Rápidas -->
                <div class="nn-card nn-animate-slide">
                    <div class="nn-card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <h3 class="nn-card-title" style="color: white;">
                            <i class="fas fa-bolt"></i>
                            Ações Rápidas
                        </h3>
                    </div>
                    <div class="nn-card-body">
                        
                        <!-- Alterar Status -->
                        <form action="atualizar_status_os.php" method="POST" class="mb-3">
                            <input type="hidden" name="os_id" value="<?= $os_id ?>">
                            
                            <label class="nn-form-label">
                                <i class="fas fa-exchange-alt"></i>
                                Alterar Status
                            </label>
                            <select name="novo_status" class="nn-form-control mb-2" required>
                                <option value="aberta" <?= $os['status'] === 'aberta' ? 'selected' : '' ?>>Aberta</option>
                                <option value="em_andamento" <?= $os['status'] === 'em_andamento' ? 'selected' : '' ?>>Em Andamento</option>
                                <option value="concluida" <?= $os['status'] === 'concluida' ? 'selected' : '' ?>>Concluída</option>
                                <option value="cancelada" <?= $os['status'] === 'cancelada' ? 'selected' : '' ?>>Cancelada</option>
                            </select>
                            
                            <button type="submit" class="nn-btn nn-btn-primary w-100">
                                <i class="fas fa-save"></i>
                                Atualizar Status
                            </button>
                        </form>

                        <hr>

                        <!-- Excluir OS -->
                        <button type="button" 
                                class="nn-btn nn-btn-danger w-100" 
                                onclick="confirmarExclusao()">
                            <i class="fas fa-trash"></i>
                            Excluir Ordem de Serviço
                        </button>
                    </div>
                </div>

                <!-- Técnico Responsável -->
                <div class="nn-card nn-animate-slide">
                    <div class="nn-card-header">
                        <h3 class="nn-card-title">
                            <i class="fas fa-user-cog"></i>
                            Técnico Responsável
                        </h3>
                    </div>
                    <div class="nn-card-body">
                        <div class="mb-3">
                            <label class="nn-form-label">Nome</label>
                            <div class="nn-info-value">
                                <strong><?= htmlspecialchars($os['tecnico_nome']) ?></strong>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="nn-form-label">Matrícula</label>
                            <div class="nn-info-value">
                                <span class="nn-badge nn-badge-info">
                                    <?= htmlspecialchars($os['tecnico_matricula']) ?>
                                </span>
                            </div>
                        </div>
                        <?php if ($os['tecnico_email']): ?>
                            <div>
                                <label class="nn-form-label">Email</label>
                                <div class="nn-info-value">
                                    <a href="mailto:<?= htmlspecialchars($os['tecnico_email']) ?>">
                                        <?= htmlspecialchars($os['tecnico_email']) ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Valores -->
                <div class="nn-card nn-animate-slide">
                    <div class="nn-card-header" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white;">
                        <h3 class="nn-card-title" style="color: white;">
                            <i class="fas fa-dollar-sign"></i>
                            Valores
                        </h3>
                    </div>
                    <div class="nn-card-body">
                        <div class="mb-3">
                            <label class="nn-form-label">Mão de Obra</label>
                            <div class="nn-info-value">
                                R$ <?= number_format($os['valor_mao_obra'], 2, ',', '.') ?>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="nn-form-label">Peças</label>
                            <div class="nn-info-value">
                                R$ <?= number_format($os['valor_pecas'], 2, ',', '.') ?>
                            </div>
                        </div>
                        <hr>
                        <div>
                            <label class="nn-form-label" style="font-size: 1.2em; font-weight: bold;">
                                Valor Total
                            </label>
                            <div class="nn-info-value" style="font-size: 1.8em; font-weight: bold; color: var(--success);">
                                R$ <?= number_format($os['valor_total'], 2, ',', '.') ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Datas -->
                <div class="nn-card nn-animate-slide">
                    <div class="nn-card-header">
                        <h3 class="nn-card-title">
                            <i class="fas fa-calendar-alt"></i>
                            Datas
                        </h3>
                    </div>
                    <div class="nn-card-body">
                        <div class="mb-3">
                            <label class="nn-form-label">
                                <i class="fas fa-calendar-plus"></i> Abertura
                            </label>
                            <div class="nn-info-value">
                                <?= date('d/m/Y H:i', strtotime($os['data_criacao'])) ?>
                            </div>
                        </div>

                        <?php if ($os['data_inicio']): ?>
                            <div class="mb-3">
                                <label class="nn-form-label">
                                    <i class="fas fa-play-circle"></i> Início do Atendimento
                                </label>
                                <div class="nn-info-value">
                                    <?= date('d/m/Y H:i', strtotime($os['data_inicio'])) ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($os['data_conclusao']): ?>
                            <div class="mb-3">
                                <label class="nn-form-label">
                                    <i class="fas fa-check-circle"></i> Conclusão
                                </label>
                                <div class="nn-info-value">
                                    <?= date('d/m/Y H:i', strtotime($os['data_conclusao'])) ?>
                                </div>
                            </div>

                            <?php
                            // Calcular tempo de atendimento
                            $inicio = new DateTime($os['data_inicio'] ?: $os['data_criacao']);
                            $conclusao = new DateTime($os['data_conclusao']);
                            $intervalo = $inicio->diff($conclusao);
                            ?>
                            <div>
                                <label class="nn-form-label">
                                    <i class="fas fa-clock"></i> Tempo de Atendimento
                                </label>
                                <div class="nn-info-value">
                                    <span class="nn-badge nn-badge-info">
                                        <?php
                                        if ($intervalo->days > 0) {
                                            echo $intervalo->days . ' dia(s) ';
                                        }
                                        echo $intervalo->h . 'h ' . $intervalo->i . 'min';
                                        ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (!$os['data_conclusao']): ?>
                            <?php
                            // Calcular tempo em aberto
                            $abertura = new DateTime($os['data_criacao']);
                            $agora = new DateTime();
                            $tempo_aberto = $abertura->diff($agora);
                            ?>
                            <div>
                                <label class="nn-form-label">
                                    <i class="fas fa-hourglass-half"></i> Tempo em Aberto
                                </label>
                                <div class="nn-info-value">
                                    <span class="nn-badge nn-badge-warning">
                                        <?php
                                        if ($tempo_aberto->days > 0) {
                                            echo $tempo_aberto->days . ' dia(s) ';
                                        }
                                        echo $tempo_aberto->h . 'h ' . $tempo_aberto->i . 'min';
                                        ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Chamado Vinculado -->
                <?php if ($os['chamado_id']): ?>
                <div class="nn-card nn-animate-slide">
                    <div class="nn-card-header">
                        <h3 class="nn-card-title">
                            <i class="fas fa-link"></i>
                            Chamado Vinculado
                        </h3>
                    </div>
                    <div class="nn-card-body">
                        <p class="mb-3">
                            Esta OS está vinculada a um chamado no sistema.
                        </p>
                        <a href="visualizar_chamado.php?id=<?= $os['chamado_id'] ?>" 
                           class="nn-btn nn-btn-primary w-100">
                            <i class="fas fa-eye"></i>
                            Ver Chamado
                        </a>
                    </div>
                </div>
                <?php endif; ?>

            </div>
        </div>

    </div>
</div>

<!-- Modal de Confirmação de Exclusão -->
<div class="modal fade" id="modalExcluir" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--danger); color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-exclamation-triangle"></i>
                    Confirmar Exclusão
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="nn-alert nn-alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <strong>ATENÇÃO:</strong> Esta ação não pode ser desfeita!
                </div>
                
                <p>Você está prestes a excluir a seguinte ordem de serviço:</p>
                
                <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 15px 0;">
                    <p><strong>Número:</strong> <?= htmlspecialchars($os['numero_os']) ?></p>
                    <p><strong>Cliente:</strong> <?= htmlspecialchars($os['cliente_nome']) ?></p>
                    <p><strong>Valor Total:</strong> R$ <?= number_format($os['valor_total'], 2, ',', '.') ?></p>
                    <p class="mb-0"><strong>Status:</strong> <?= $status_texto ?></p>
                </div>

                <p>Tem certeza que deseja excluir esta ordem?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="nn-btn nn-btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <form action="excluir_ordem_servico.php" method="POST" style="display: inline;">
                    <input type="hidden" name="os_id" value="<?= $os_id ?>">
                    <button type="submit" class="nn-btn nn-btn-danger">
                        <i class="fas fa-trash"></i>
                        Sim, Excluir Ordem
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.nn-info-value {
    font-size: 1.1em;
    color: #333;
    padding: 8px 0;
}

.nn-info-text {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid var(--primary);
    white-space: pre-wrap;
    line-height: 1.8;
    text-align: justify;
}
</style>

<script>
function confirmarExclusao() {
    const modal = new bootstrap.Modal(document.getElementById('modalExcluir'));
    modal.show();
}
</script>

<?php
$conn->close();
require_once '../includes/footer.php';
?>