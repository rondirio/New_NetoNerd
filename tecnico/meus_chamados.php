<?php
/**
 * Meus Chamados - NetoNerd ITSM v2.0
 * Listagem completa de chamados atribuídos ao técnico
 */

session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

// PROTEÇÃO: Apenas técnicos e admins
requireTecnico();

$conn = getConnection();
$tecnico_id = $_SESSION['usuario_id'];

// Filtros
$filtro_status = $_GET['status'] ?? '';
$filtro_prioridade = $_GET['prioridade'] ?? '';

// Buscar chamados do técnico
$sql = "
    SELECT
        c.*,
        IFNULL(cl.nome, c.nome_usuario) as cliente_nome,
        cl.email as cliente_email,
        cl.telefone as cliente_telefone,
        TIMESTAMPDIFF(HOUR, c.data_abertura, NOW()) as horas_abertas,
        CASE
            WHEN c.data_inicio_atendimento IS NOT NULL THEN
                TIMESTAMPDIFF(MINUTE, c.data_inicio_atendimento, NOW())
            ELSE NULL
        END as minutos_em_atendimento
    FROM chamados c
    LEFT JOIN clientes cl ON c.cliente_id = cl.id
    WHERE c.tecnico_id = ?
      AND c.status != 'cancelado'
";

$params = [$tecnico_id];
$types = 'i';

if ($filtro_status) {
    $sql .= " AND c.status = ?";
    $params[] = $filtro_status;
    $types .= 's';
}

if ($filtro_prioridade) {
    $sql .= " AND c.prioridade = ?";
    $params[] = $filtro_prioridade;
    $types .= 's';
}

$sql .= " ORDER BY
    CASE c.status
        WHEN 'em andamento' THEN 1
        WHEN 'aberto' THEN 2
        WHEN 'pendente' THEN 3
        WHEN 'resolvido' THEN 4
    END,
    CASE c.prioridade
        WHEN 'critica' THEN 1
        WHEN 'alta' THEN 2
        WHEN 'media' THEN 3
        WHEN 'baixa' THEN 4
    END,
    c.data_abertura ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Armazena resultados em array
$chamados = [];
while ($row = $result->fetch_assoc()) {
    $chamados[] = $row;
}

// Estatísticas do técnico
$stats_sql = "
    SELECT
        COUNT(*) as total,
        COUNT(CASE WHEN status = 'aberto' THEN 1 END) as abertos,
        COUNT(CASE WHEN status = 'em andamento' THEN 1 END) as em_andamento,
        COUNT(CASE WHEN status = 'pendente' THEN 1 END) as pendentes,
        COUNT(CASE WHEN status = 'resolvido' THEN 1 END) as resolvidos,
        AVG(CASE WHEN tempo_atendimento_minutos IS NOT NULL THEN tempo_atendimento_minutos END) as tempo_medio
    FROM chamados
    WHERE tecnico_id = ? AND status != 'cancelado'
";
$stmt_stats = $conn->prepare($stats_sql);
$stmt_stats->bind_param("i", $tecnico_id);
$stmt_stats->execute();
$stats = $stmt_stats->get_result()->fetch_assoc();

// Configuração da página
$page_title = "Meus Chamados - NetoNerd ITSM";

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
                    <i class="fas fa-clipboard-list"></i>
                    Meus Chamados
                </h1>
                <div>
                    <span class="nn-badge nn-badge-info">
                        <i class="fas fa-user"></i>
                        <?php echo htmlspecialchars($_SESSION['nome']); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Alertas -->
        <?php if (isset($_GET['sucesso'])): ?>
            <div class="nn-alert nn-alert-success nn-animate-fade">
                <i class="fas fa-check-circle"></i>
                <?php
                switch($_GET['sucesso']) {
                    case 'iniciado': echo 'Atendimento iniciado com sucesso!'; break;
                    case 'atualizado': echo 'Chamado atualizado com sucesso!'; break;
                    case 'resolvido': echo 'Chamado marcado como resolvido!'; break;
                    default: echo 'Operação realizada com sucesso!';
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

        <!-- Dashboard Stats -->
        <div class="nn-stats-grid nn-animate-slide">
            <div class="nn-stat-card primary">
                <div class="nn-stat-icon primary">
                    <i class="fas fa-folder-open"></i>
                </div>
                <div class="nn-stat-value"><?php echo $stats['abertos']; ?></div>
                <div class="nn-stat-label">Abertos</div>
            </div>

            <div class="nn-stat-card info">
                <div class="nn-stat-icon info">
                    <i class="fas fa-spinner"></i>
                </div>
                <div class="nn-stat-value"><?php echo $stats['em_andamento']; ?></div>
                <div class="nn-stat-label">Em Andamento</div>
            </div>

            <div class="nn-stat-card warning">
                <div class="nn-stat-icon warning">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="nn-stat-value"><?php echo $stats['pendentes']; ?></div>
                <div class="nn-stat-label">Pendentes</div>
            </div>

            <div class="nn-stat-card success">
                <div class="nn-stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="nn-stat-value"><?php echo $stats['resolvidos']; ?></div>
                <div class="nn-stat-label">Resolvidos</div>
                <?php if ($stats['tempo_medio']): ?>
                    <small style="font-size: 0.85rem; opacity: 0.9;">
                        Tempo médio: <?php echo round($stats['tempo_medio']); ?>min
                    </small>
                <?php endif; ?>
            </div>
        </div>

        <!-- Filtros -->
        <div class="nn-card nn-animate-slide">
            <div class="nn-card-header">
                <h2 class="nn-card-title">
                    <i class="fas fa-filter"></i>
                    Filtros
                </h2>
            </div>
            <div class="nn-card-body">
                <form method="GET" action="">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <div class="nn-form-group">
                                <label class="nn-form-label">
                                    <i class="fas fa-info-circle"></i>
                                    Status
                                </label>
                                <select name="status" class="nn-form-control">
                                    <option value="">Todos</option>
                                    <option value="aberto" <?php echo $filtro_status === 'aberto' ? 'selected' : ''; ?>>Abertos</option>
                                    <option value="em andamento" <?php echo $filtro_status === 'em andamento' ? 'selected' : ''; ?>>Em Andamento</option>
                                    <option value="pendente" <?php echo $filtro_status === 'pendente' ? 'selected' : ''; ?>>Pendentes</option>
                                    <option value="resolvido" <?php echo $filtro_status === 'resolvido' ? 'selected' : ''; ?>>Resolvidos</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-5">
                            <div class="nn-form-group">
                                <label class="nn-form-label">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Prioridade
                                </label>
                                <select name="prioridade" class="nn-form-control">
                                    <option value="">Todas</option>
                                    <option value="critica" <?php echo $filtro_prioridade === 'critica' ? 'selected' : ''; ?>>Crítica</option>
                                    <option value="alta" <?php echo $filtro_prioridade === 'alta' ? 'selected' : ''; ?>>Alta</option>
                                    <option value="media" <?php echo $filtro_prioridade === 'media' ? 'selected' : ''; ?>>Média</option>
                                    <option value="baixa" <?php echo $filtro_prioridade === 'baixa' ? 'selected' : ''; ?>>Baixa</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <div class="nn-form-group" style="width: 100%;">
                                <button type="submit" class="nn-btn nn-btn-primary" style="width: 100%; margin-bottom: 1rem;">
                                    <i class="fas fa-search"></i>
                                    Filtrar
                                </button>
                                <a href="meus_chamados.php" class="nn-btn nn-btn-secondary" style="width: 100%;">
                                    <i class="fas fa-redo"></i>
                                    Limpar
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de Chamados -->
        <?php if (count($chamados) === 0): ?>
            <div class="nn-alert nn-alert-info nn-animate-fade">
                <i class="fas fa-info-circle"></i>
                Você não tem chamados atribuídos no momento com estes filtros.
            </div>
        <?php else: ?>
            <?php foreach ($chamados as $chamado):
                // Classes de prioridade
                $prioridadeClass = match(strtolower($chamado['prioridade'])) {
                    'critica' => 'nn-badge-critical',
                    'alta' => 'nn-badge-high',
                    'media' => 'nn-badge-medium',
                    'baixa' => 'nn-badge-low',
                    default => 'nn-badge-secondary'
                };

                // Classes de status
                $statusClass = match(strtolower($chamado['status'])) {
                    'aberto' => 'nn-badge-primary',
                    'em andamento' => 'nn-badge-info',
                    'pendente' => 'nn-badge-warning',
                    'resolvido' => 'nn-badge-success',
                    default => 'nn-badge-secondary'
                };

                // Borda colorida por prioridade
                $borderColor = match(strtolower($chamado['prioridade'])) {
                    'critica' => 'border-left: 5px solid var(--priority-critical)',
                    'alta' => 'border-left: 5px solid var(--priority-high)',
                    'media' => 'border-left: 5px solid var(--priority-medium)',
                    'baixa' => 'border-left: 5px solid var(--priority-low)',
                    default => 'border-left: 5px solid var(--secondary-gray)'
                };
            ?>
                <div class="nn-card nn-animate-fade mb-3" style="<?php echo $borderColor; ?>">
                    <div class="nn-card-body">
                        <div class="row">
                            <!-- Informações do Chamado -->
                            <div class="col-md-8">
                                <h5 class="mb-2">
                                    <strong class="text-primary">#<?php echo htmlspecialchars($chamado['protocolo']); ?></strong> -
                                    <?php echo htmlspecialchars($chamado['titulo']); ?>
                                </h5>

                                <div class="mb-3">
                                    <span class="nn-badge <?php echo $prioridadeClass; ?>">
                                        <?php echo ucfirst($chamado['prioridade']); ?>
                                    </span>
                                    <span class="nn-badge <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($chamado['status']); ?>
                                    </span>
                                </div>

                                <p class="text-muted mb-3"><?php echo nl2br(htmlspecialchars($chamado['descricao'])); ?></p>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <i class="fas fa-user text-primary"></i>
                                            <strong>Cliente:</strong><br>
                                            <small><?php echo htmlspecialchars($chamado['cliente_nome']); ?></small>
                                        </div>
                                        <?php if ($chamado['cliente_telefone']): ?>
                                            <div class="mb-2">
                                                <i class="fas fa-phone text-success"></i>
                                                <strong>Telefone:</strong><br>
                                                <small>
                                                    <a href="tel:<?php echo htmlspecialchars($chamado['cliente_telefone']); ?>">
                                                        <?php echo htmlspecialchars($chamado['cliente_telefone']); ?>
                                                    </a>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <i class="fas fa-calendar text-primary"></i>
                                            <strong>Aberto:</strong><br>
                                            <small>
                                                <?php echo date('d/m/Y H:i', strtotime($chamado['data_abertura'])); ?>
                                                (há <?php echo $chamado['horas_abertas']; ?>h)
                                            </small>
                                        </div>

                                        <?php if ($chamado['data_inicio_atendimento']): ?>
                                            <div class="mb-2">
                                                <i class="fas fa-play text-success"></i>
                                                <strong>Iniciado:</strong><br>
                                                <small>
                                                    <?php echo date('d/m/Y H:i', strtotime($chamado['data_inicio_atendimento'])); ?>
                                                    <?php if ($chamado['minutos_em_atendimento']): ?>
                                                        (<?php echo $chamado['minutos_em_atendimento']; ?>min)
                                                    <?php endif; ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Ações -->
                            <div class="col-md-4 text-end d-flex flex-column gap-2">
                                <?php if ($chamado['status'] === 'aberto'): ?>
                                    <form action="processar_chamado.php" method="POST">
                                        <input type="hidden" name="chamado_id" value="<?php echo $chamado['id']; ?>">
                                        <input type="hidden" name="acao" value="iniciar">
                                        <button type="submit" class="nn-btn nn-btn-primary nn-btn-lg w-100">
                                            <i class="fas fa-play"></i>
                                            Iniciar Atendimento
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($chamado['status'] === 'em andamento'): ?>
                                    <a href="resolver_chamado.php?id=<?php echo $chamado['id']; ?>" class="nn-btn nn-btn-success nn-btn-lg w-100">
                                        <i class="fas fa-check"></i>
                                        Resolver Chamado
                                    </a>

                                    <!-- BOTÃO CORRIGIDO - Usando data-attributes do Bootstrap -->
                                    <button type="button" 
                                            class="nn-btn nn-btn-warning w-100"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalAtualizar"
                                            data-chamado-id="<?php echo $chamado['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                        Adicionar Atualização
                                    </button>

                                    <form action="processar_chamado.php" method="POST">
                                        <input type="hidden" name="chamado_id" value="<?php echo $chamado['id']; ?>">
                                        <input type="hidden" name="acao" value="pausar">
                                        <button type="submit" class="nn-btn nn-btn-secondary w-100">
                                            <i class="fas fa-pause"></i>
                                            Marcar como Pendente
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($chamado['status'] === 'pendente'): ?>
                                    <form action="processar_chamado.php" method="POST">
                                        <input type="hidden" name="chamado_id" value="<?php echo $chamado['id']; ?>">
                                        <input type="hidden" name="acao" value="retomar">
                                        <button type="submit" class="nn-btn nn-btn-info nn-btn-lg w-100">
                                            <i class="fas fa-play"></i>
                                            Retomar Atendimento
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($chamado['status'] === 'resolvido'): ?>
                                    <div class="nn-badge nn-badge-success p-3">
                                        <i class="fas fa-check-circle"></i>
                                        Chamado Resolvido
                                    </div>
                                <?php endif; ?>

                                <a href="detalhes_chamado.php?id=<?php echo $chamado['id']; ?>" class="nn-btn nn-btn-secondary w-100">
                                    <i class="fas fa-eye"></i>
                                    Ver Detalhes
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
</div>

<!-- Modal Atualizar Chamado - VERSÃO CORRIGIDA -->
<div class="modal fade" id="modalAtualizar" tabindex="-1" aria-labelledby="modalAtualizarLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--gradient-primary); color: white;">
                <h5 class="modal-title" id="modalAtualizarLabel">
                    <i class="fas fa-edit"></i>
                    Adicionar Atualização
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="processar_chamado.php" method="POST">
                <input type="hidden" name="chamado_id" id="atualizar_chamado_id">
                <input type="hidden" name="acao" value="atualizar">

                <div class="modal-body">
                    <div class="nn-form-group mb-3">
                        <label class="nn-form-label">Tipo de Atualização</label>
                        <select name="tipo_atualizacao" class="nn-form-control" required>
                            <option value="comentario">Comentário</option>
                            <option value="necessita_peca">Necessita Peça</option>
                            <option value="aguardando_cliente">Aguardando Cliente</option>
                        </select>
                    </div>

                    <div class="nn-form-group">
                        <label class="nn-form-label">Descrição</label>
                        <textarea name="descricao" class="nn-form-control" rows="4" required placeholder="Descreva a atualização do chamado..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="nn-btn nn-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="nn-btn nn-btn-primary">
                        <i class="fas fa-save"></i>
                        Salvar Atualização
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Captura o ID do chamado quando o modal abre - SOLUÇÃO NATIVA DO BOOTSTRAP
document.getElementById('modalAtualizar').addEventListener('show.bs.modal', function (event) {
    // Botão que acionou o modal
    const button = event.relatedTarget;
    
    // Extrai o ID do chamado do atributo data-chamado-id
    const chamadoId = button.getAttribute('data-chamado-id');
    
    // Atualiza o campo hidden no modal
    document.getElementById('atualizar_chamado_id').value = chamadoId;
    
    console.log('Modal aberto para chamado ID:', chamadoId); // Debug
});
</script>
<!-- Bootstrap JS - FORÇAR CARREGAMENTO -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

<script>
// Aguarda o Bootstrap carregar
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== INICIANDO DEBUG DO MODAL ===');
    
    // 1. Verifica se Bootstrap está carregado
    if (typeof bootstrap !== 'undefined') {
        console.log('✓ Bootstrap carregado:', bootstrap.Modal.VERSION);
    } else {
        console.error('✗ Bootstrap NÃO está carregado!');
        return; // Para aqui se Bootstrap não estiver carregado
    }
    
    // 2. Verifica se o modal existe no DOM
    const modalElement = document.getElementById('modalAtualizar');
    if (modalElement) {
        console.log('✓ Modal encontrado no DOM');
    } else {
        console.error('✗ Modal NÃO encontrado no DOM');
        return;
    }
    
    // 3. Verifica se o campo hidden existe
    const hiddenField = document.getElementById('atualizar_chamado_id');
    if (hiddenField) {
        console.log('✓ Campo hidden encontrado');
    } else {
        console.error('✗ Campo hidden NÃO encontrado');
    }
    
    // 4. Event listener do modal
    modalElement.addEventListener('show.bs.modal', function (event) {
        console.log('=== EVENTO show.bs.modal DISPARADO ===');
        
        const button = event.relatedTarget;
        
        if (button) {
            const chamadoId = button.getAttribute('data-chamado-id');
            console.log('✓ ID do chamado capturado:', chamadoId);
            
            const hiddenInput = document.getElementById('atualizar_chamado_id');
            if (hiddenInput) {
                hiddenInput.value = chamadoId;
                console.log('✓ Valor definido no campo hidden:', hiddenInput.value);
            }
        } else {
            console.error('✗ relatedTarget está vazio');
        }
    });
    
    modalElement.addEventListener('shown.bs.modal', function() {
        console.log('=== MODAL ABERTO COM SUCESSO ===');
        const hiddenValue = document.getElementById('atualizar_chamado_id').value;
        console.log('Valor no campo hidden:', hiddenValue);
    });
    
    console.log('✓ Event listeners registrados com sucesso');
    console.log('=== FIM DO DEBUG ===');
});
</script>

<?php
// Incluir footer
$stmt->close();
$stmt_stats->close();
$conn->close();
require_once '../includes/footer.php';
?>