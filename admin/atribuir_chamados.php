<?php

/**
 * Atribuir Chamados a Técnicos
 * NetoNerd ITSM v2.0
 */
session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';
requireAdmin();

$page_title = "Atribuir Chamados - NetoNerd ITSM";
$conn = getConnection();

// ========================================
// FILTROS DA URL
// ========================================
$filtro_status = $_GET['status'] ?? 'nao_atribuido';
$filtro_prioridade = $_GET['prioridade'] ?? '';
$busca = $_GET['busca'] ?? '';

// ========================================
// CONSTRUÇÃO DA QUERY - CHAMADOS
// ========================================
$sql = "
    SELECT 
        c.*,
        IFNULL(cl.nome, c.nome_usuario) AS cliente_nome,
        cl.email AS cliente_email,
        cl.telefone AS cliente_telefone,
        t.nome AS tecnico_nome,
        t.matricula AS tecnico_matricula,
        TIMESTAMPDIFF(HOUR, c.data_abertura, NOW()) AS horas_aguardando
    FROM chamados c
    LEFT JOIN clientes cl ON c.cliente_id = cl.id
    LEFT JOIN tecnicos t ON c.tecnico_id = t.id
    WHERE c.status != 'cancelado'
";

$params = [];
$types = '';

// Filtro por atribuição
if ($filtro_status === 'nao_atribuido') {
    $sql .= " AND c.tecnico_id IS NULL";
} elseif ($filtro_status === 'atribuido') {
    $sql .= " AND c.tecnico_id IS NOT NULL";
} elseif ($filtro_status !== '' && $filtro_status !== 'todos') {
    $sql .= " AND c.status = ?";
    $params[] = $filtro_status;
    $types .= 's';
}

// Filtro prioridade
if ($filtro_prioridade !== '') {
    $sql .= " AND c.prioridade = ?";
    $params[] = $filtro_prioridade;
    $types .= 's';
}

// Busca
if ($busca !== '') {
    $sql .= " AND (
        c.titulo LIKE ? OR 
        c.descricao LIKE ? OR 
        c.protocolo LIKE ? OR 
        IFNULL(cl.nome, c.nome_usuario) LIKE ?
    )";
    $like = "%{$busca}%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
    $types .= 'ssss';
}

// Ordenação
$sql .= " 
    ORDER BY 
        CASE c.prioridade 
            WHEN 'critica' THEN 1 
            WHEN 'alta' THEN 2 
            WHEN 'media' THEN 3 
            WHEN 'baixa' THEN 4 
        END,
        c.data_abertura ASC
";

// Execução
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Armazena resultados em array
$chamados = [];
while ($row = $result->fetch_assoc()) {
    $chamados[] = $row;
}

// ========================================
// TÉCNICOS ATIVOS
// ========================================
$tecnicos = $conn->query("
    SELECT id, nome, matricula 
    FROM tecnicos 
    WHERE Ativo = 1 
    AND matricula LIKE '%F%' 
    ORDER BY nome
");

// ========================================
// ESTATÍSTICAS POR TÉCNICO
// ========================================
$stats_result = $conn->query("
    SELECT 
        t.id,
        COUNT(CASE WHEN c.status = 'aberto' THEN 1 END) AS abertos,
        COUNT(CASE WHEN c.status = 'em andamento' THEN 1 END) AS em_andamento,
        COUNT(CASE WHEN c.status = 'pendente' THEN 1 END) AS pendentes
    FROM tecnicos t
    LEFT JOIN chamados c ON t.id = c.tecnico_id 
        AND c.status IN ('aberto','em andamento','pendente')
    WHERE t.Ativo = 1 
    AND t.matricula LIKE '%F%'
    GROUP BY t.id
");

$tecnico_stats = [];
while ($row = $stats_result->fetch_assoc()) {
    $tecnico_stats[$row['id']] = $row;
}

// ========================================
// CONTAGEM NÃO ATRIBUÍDOS
// ========================================
$count_nao_atribuidos = $conn->query("
    SELECT COUNT(*) as total 
    FROM chamados 
    WHERE tecnico_id IS NULL 
    AND status != 'cancelado'
")->fetch_assoc()['total'];

require_once '../includes/header.php';
?>

<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <!-- Cabeçalho -->
        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h1 class="nn-card-title">
                    <i class="fas fa-user-plus"></i>
                    Atribuir Chamados a Técnicos
                </h1>
                <button type="button" class="nn-btn nn-btn-secondary" onclick="location.reload()">
                    <i class="fas fa-sync"></i>
                    Atualizar
                </button>
            </div>
        </div>

        <!-- Alertas -->
        <?php if (isset($_GET['sucesso'])): ?>
            <div class="nn-alert nn-alert-success nn-animate-fade">
                <i class="fas fa-check-circle"></i>
                Chamado atribuído com sucesso!
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['erro'])): ?>
            <div class="nn-alert nn-alert-danger nn-animate-fade">
                <i class="fas fa-exclamation-circle"></i>
                Erro: <?= htmlspecialchars($_GET['erro']) ?>
            </div>
        <?php endif; ?>

        <!-- Stats -->
        <?php if ($count_nao_atribuidos > 0): ?>
            <div class="nn-alert nn-alert-warning nn-animate-slide">
                <i class="fas fa-exclamation-triangle"></i>
                <strong><?= $count_nao_atribuidos ?> chamado(s)</strong> aguardando atribuição!
            </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="nn-card nn-animate-slide">
            <div class="nn-card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="nn-form-label">
                            <i class="fas fa-filter"></i> Status
                        </label>
                        <select name="status" class="nn-form-control" onchange="this.form.submit()">
                            <option value="nao_atribuido" <?= $filtro_status === 'nao_atribuido' ? 'selected' : '' ?>>Não Atribuídos</option>
                            <option value="atribuido" <?= $filtro_status === 'atribuido' ? 'selected' : '' ?>>Atribuídos</option>
                            <option value="todos" <?= $filtro_status === 'todos' ? 'selected' : '' ?>>Todos</option>
                            <option value="aberto" <?= $filtro_status === 'aberto' ? 'selected' : '' ?>>Abertos</option>
                            <option value="em andamento" <?= $filtro_status === 'em andamento' ? 'selected' : '' ?>>Em Andamento</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="nn-form-label">
                            <i class="fas fa-exclamation-triangle"></i> Prioridade
                        </label>
                        <select name="prioridade" class="nn-form-control" onchange="this.form.submit()">
                            <option value="">Todas</option>
                            <option value="critica" <?= $filtro_prioridade === 'critica' ? 'selected' : '' ?>>Crítica</option>
                            <option value="alta" <?= $filtro_prioridade === 'alta' ? 'selected' : '' ?>>Alta</option>
                            <option value="media" <?= $filtro_prioridade === 'media' ? 'selected' : '' ?>>Média</option>
                            <option value="baixa" <?= $filtro_prioridade === 'baixa' ? 'selected' : '' ?>>Baixa</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="nn-form-label">
                            <i class="fas fa-search"></i> Buscar
                        </label>
                        <input type="text" name="busca" class="nn-form-control" placeholder="Protocolo, título, cliente..." value="<?= htmlspecialchars($busca) ?>">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="nn-btn nn-btn-primary w-100">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de Chamados -->
        <?php if (count($chamados) === 0): ?>
            <div class="nn-alert nn-alert-info">
                <i class="fas fa-info-circle"></i>
                Nenhum chamado encontrado com os filtros aplicados.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($chamados as $chamado): ?>
                    <div class="col-md-6 mb-3">
                        <div class="nn-card" style="border-left: 4px solid
                            <?php
                            echo $chamado['prioridade'] === 'critica' ? 'var(--priority-critical)' :
                                 ($chamado['prioridade'] === 'alta' ? 'var(--priority-high)' :
                                 ($chamado['prioridade'] === 'media' ? 'var(--priority-medium)' : 'var(--priority-low)'));
                            ?>
                        ">
                            <div class="nn-card-body">
                                <div class="nn-d-flex nn-justify-between nn-align-center nn-mb-2">
                                    <div>
                                        <h5 class="mb-1">
                                            <strong>#<?= $chamado['protocolo'] ?></strong> - <?= htmlspecialchars($chamado['titulo']) ?>
                                        </h5>
                                        <span class="nn-badge nn-badge-<?= $chamado['prioridade'] === 'critica' ? 'critical' : ($chamado['prioridade'] === 'alta' ? 'high' : ($chamado['prioridade'] === 'media' ? 'medium' : 'low')) ?>">
                                            <?= ucfirst($chamado['prioridade']) ?>
                                        </span>
                                        <span class="nn-badge nn-badge-<?= $chamado['status'] === 'aberto' ? 'primary' : ($chamado['status'] === 'em andamento' ? 'info' : 'secondary') ?>">
                                            <?= ucfirst($chamado['status']) ?>
                                        </span>
                                    </div>
                                    <?php if ($chamado['horas_aguardando'] > 24): ?>
                                        <span class="nn-badge nn-badge-warning">
                                            <i class="fas fa-clock"></i> <?= $chamado['horas_aguardando'] ?>h aguardando
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <p class="text-muted mb-2">
                                    <?= htmlspecialchars(substr($chamado['descricao'], 0, 150)) ?>...
                                </p>

                                <div class="nn-mb-2">
                                    <strong><i class="fas fa-user"></i> Cliente:</strong>
                                    <?= htmlspecialchars($chamado['cliente_nome']) ?>
                                    <?php if ($chamado['cliente_telefone']): ?>
                                        <br><small><i class="fas fa-phone"></i> <?= htmlspecialchars($chamado['cliente_telefone']) ?></small>
                                    <?php endif; ?>
                                </div>

                                <div class="nn-mb-2">
                                    <strong><i class="fas fa-calendar"></i> Aberto:</strong>
                                    <?= date('d/m/Y H:i', strtotime($chamado['data_abertura'])) ?>
                                </div>

                                <?php if ($chamado['tecnico_id']): ?>
                                    <div class="nn-alert nn-alert-success nn-mb-2">
                                        <i class="fas fa-user-check"></i> Atribuído a:
                                        <strong><?= htmlspecialchars($chamado['tecnico_nome']) ?></strong>
                                        (<?= htmlspecialchars($chamado['tecnico_matricula']) ?>)
                                    </div>
                                    <button type="button" class="nn-btn nn-btn-warning nn-btn-sm" onclick="abrirModalAtribuir(<?= $chamado['id'] ?>, '<?= addslashes(htmlspecialchars($chamado['titulo'])) ?>')">
                                        <i class="fas fa-exchange-alt"></i> Reatribuir
                                    </button>
                                <?php else: ?>
                                    <button type="button" class="nn-btn nn-btn-primary" onclick="abrirModalAtribuir(<?= $chamado['id'] ?>, '<?= addslashes(htmlspecialchars($chamado['titulo'])) ?>')">
                                        <i class="fas fa-user-plus"></i> Atribuir Técnico
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- Modal Atribuir Técnico -->
<div class="modal fade" id="modalAtribuir" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--gradient-primary); color: white;">
                <h5 class="modal-title"><i class="fas fa-user-plus"></i> Atribuir Técnico ao Chamado</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="processar_atribuicao.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="chamado_id" id="chamado_id">
                    <input type="hidden" name="acao" value="atribuir">

                    <div class="nn-alert nn-alert-info">
                        <strong>Chamado:</strong> <span id="modal_chamado_titulo"></span>
                    </div>

                    <div class="nn-form-group">
                        <label class="nn-form-label"><strong>Selecione o Técnico:</strong></label>

                        <?php $tecnicos->data_seek(0); ?>
                        <?php while ($tec = $tecnicos->fetch_assoc()): ?>
                            <?php $stats = $tecnico_stats[$tec['id']] ?? ['abertos' => 0, 'em_andamento' => 0, 'pendentes' => 0]; ?>
                            <div style="border: 2px solid #e9ecef; border-radius: 10px; padding: 15px; margin-bottom: 10px; cursor: pointer; transition: all 0.3s;"
                                 onclick="selecionarTecnico(<?= $tec['id'] ?>)"
                                 id="card_tecnico_<?= $tec['id'] ?>">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="tecnico_id" value="<?= $tec['id'] ?>" id="tecnico_<?= $tec['id'] ?>" required>
                                    <label class="form-check-label w-100" for="tecnico_<?= $tec['id'] ?>">
                                        <div class="nn-d-flex nn-justify-between nn-align-center">
                                            <div>
                                                <strong><?= htmlspecialchars($tec['nome']) ?></strong>
                                                <br>
                                                <small class="text-muted">Matrícula: <?= htmlspecialchars($tec['matricula']) ?></small>
                                            </div>
                                            <div class="text-end">
                                                <small>
                                                    <span class="nn-badge nn-badge-info"><?= $stats['em_andamento'] ?> em andamento</span>
                                                    <span class="nn-badge nn-badge-secondary"><?= $stats['abertos'] ?> abertos</span>
                                                    <span class="nn-badge nn-badge-warning"><?= $stats['pendentes'] ?> pendentes</span>
                                                </small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <div class="nn-form-group">
                        <label class="nn-form-label">Comentário (opcional):</label>
                        <textarea name="comentario" class="nn-form-control" rows="3" placeholder="Ex: Cliente relata urgência..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="nn-btn nn-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="nn-btn nn-btn-primary">
                        <i class="fas fa-check"></i> Atribuir Chamado
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function abrirModalAtribuir(chamadoId, chamadoTitulo) {
    document.getElementById('chamado_id').value = chamadoId;
    document.getElementById('modal_chamado_titulo').textContent = '#' + chamadoId + ' - ' + chamadoTitulo;

    // Limpar seleção
    document.querySelectorAll('[id^="card_tecnico_"]').forEach(card => {
        card.style.borderColor = '#e9ecef';
        card.style.backgroundColor = '';
    });
    document.querySelectorAll('input[name="tecnico_id"]').forEach(input => input.checked = false);

    new bootstrap.Modal(document.getElementById('modalAtribuir')).show();
}

function selecionarTecnico(tecnicoId) {
    // Limpar todos
    document.querySelectorAll('[id^="card_tecnico_"]').forEach(card => {
        card.style.borderColor = '#e9ecef';
        card.style.backgroundColor = '';
    });

    // Selecionar
    const radio = document.getElementById('tecnico_' + tecnicoId);
    radio.checked = true;

    const card = document.getElementById('card_tecnico_' + tecnicoId);
    card.style.borderColor = 'var(--success)';
    card.style.backgroundColor = '#d4edda';
}
</script>

<?php require_once '../includes/footer.php'; ?>