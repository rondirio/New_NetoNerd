<?php
/**
 * NetoNerd - Dashboard do Cliente
 * Painel de controle para clientes gerenciarem seus chamados
 * 
 * @package NetoNerd
 * @author NetoNerd Team
 * @version 2.0
 */

// Inicia sessão
// session_start();

// Verifica se usuário está autenticado
// if (!isset($_SESSION['id'])) {
//     header('Location: index.php?login=erro2');
//     exit;
// }

// Configurações da página
$pageTitle = 'Dashboard - NetoNerd';
$pageDescription = 'Painel de controle do cliente';
$id = 1;
$_SESSION = [$id];
// Inclui conexão com banco
require_once('../Database/conexao.php');
print_r($_SESSION);
// Obtém informações do usuário
$conn = getConnection();
$usuario_id = $_SESSION[0];

// Busca dados do cliente
$stmt = $conn->prepare("SELECT nome, email, genero, telefone, plano FROM clientes WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$cliente = $result->fetch_assoc();
$stmt->close();

// Define saudação baseada no gênero
$saudacao = 'Bem-vindo(a)';
if (isset($cliente['genero'])) {
    $saudacao = ($cliente['genero'] === 'Masculino') ? 'Bem-vindo' : 
                (($cliente['genero'] === 'Feminino') ? 'Bem-vinda' : 'Bem-vindo(a)');
}

// Estatísticas dos chamados
$stats = [
    'total' => 0,
    'abertos' => 0,
    'em_andamento' => 0,
    'resolvidos' => 0
];

$stmt = $conn->prepare("SELECT status, COUNT(*) as total FROM chamados WHERE cliente_id = ? GROUP BY status");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $stats['total'] += $row['total'];
    
    switch ($row['status']) {
        case 'aberto':
            $stats['abertos'] = $row['total'];
            break;
        case 'em andamento':
            $stats['em_andamento'] = $row['total'];
            break;
        case 'resolvido':
            $stats['resolvidos'] = $row['total'];
            break;
    }
}
$stmt->close();

// Busca chamados pendentes
$stmt = $conn->prepare("
    SELECT id, titulo, descricao, prioridade, status, protocolo, nome_usuario, data_criacao
    FROM chamados 
    WHERE cliente_id = ? AND status NOT IN ('resolvido', 'cancelado')
    ORDER BY 
        FIELD(prioridade, 'critica', 'alta', 'media', 'baixa'),
        data_criacao DESC
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$chamados_pendentes = $stmt->get_result();
$stmt->close();

// Busca chamados resolvidos/cancelados
$stmt = $conn->prepare("
    SELECT id, titulo, descricao, prioridade, status, protocolo, nome_usuario, data_criacao, data_finalizacao
    FROM chamados 
    WHERE cliente_id = ? AND status IN ('resolvido', 'cancelado')
    ORDER BY data_finalizacao DESC
    LIMIT 10
");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$chamados_finalizados = $stmt->get_result();
$stmt->close();

$conn->close();

// Funções auxiliares
function getBadgeClass($prioridade) {
    $classes = [
        'critica' => 'danger',
        'alta' => 'danger',
        'media' => 'warning',
        'baixa' => 'info'
    ];
    return $classes[$prioridade] ?? 'secondary';
}

function getStatusClass($status) {
    $classes = [
        'aberto' => 'primary',
        'em andamento' => 'warning',
        'pendente' => 'secondary',
        'resolvido' => 'success',
        'cancelado' => 'danger'
    ];
    return $classes[$status] ?? 'light';
}

function getStatusIcon($status) {
    $icons = [
        'aberto' => 'fas fa-folder-open',
        'em andamento' => 'fas fa-sync-alt',
        'pendente' => 'fas fa-clock',
        'resolvido' => 'fas fa-check-circle',
        'cancelado' => 'fas fa-times-circle'
    ];
    return $icons[$status] ?? 'fas fa-circle';
}

// Inclui o header
include_once 'layouts/header.php';
?>

<!-- Dashboard Header -->
<section class="dashboard-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="text-white mb-2">
                    <?= $saudacao ?>, <strong><?= htmlspecialchars($cliente['nome']) ?></strong>!
                </h1>
                <p class="text-white-50 mb-0">
                    <i class="fas fa-envelope mr-2"></i><?= htmlspecialchars($cliente['email']) ?>
                    <?php if (!empty($cliente['telefone'])): ?>
                    <span class="ml-3"><i class="fas fa-phone mr-2"></i><?= htmlspecialchars($cliente['telefone']) ?></span>
                    <?php endif; ?>
                </p>
            </div>
            <div class="col-lg-4 text-lg-right mt-3 mt-lg-0">
                <a href="chamados/criar.php" class="btn btn-light btn-lg">
                    <i class="fas fa-plus-circle"></i> Abrir Chamado
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Estatísticas -->
<section class="dashboard-stats">
    <div class="container">
        <div class="row">
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card stat-total">
                    <div class="stat-icon">
                        <i class="fas fa-ticket-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $stats['total'] ?></h3>
                        <p>Total de Chamados</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card stat-open">
                    <div class="stat-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $stats['abertos'] ?></h3>
                        <p>Abertos</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card stat-progress">
                    <div class="stat-icon">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $stats['em_andamento'] ?></h3>
                        <p>Em Andamento</p>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3 col-md-6 mb-4">
                <div class="stat-card stat-resolved">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?= $stats['resolvidos'] ?></h3>
                        <p>Resolvidos</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Chamados Pendentes -->
<section class="chamados-section">
    <div class="container">
        <div class="section-header">
            <h2><i class="fas fa-list-ul"></i> Chamados Ativos</h2>
            <p class="text-muted">Acompanhe o status dos seus chamados em aberto</p>
        </div>
        
        <div class="card">
            <div class="card-body p-0">
                <?php if ($chamados_pendentes->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="80">ID</th>
                                <th>Título</th>
                                <th width="150">Prioridade</th>
                                <th width="150">Status</th>
                                <th width="120">Data</th>
                                <th width="200" class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($chamado = $chamados_pendentes->fetch_assoc()): ?>
                            <tr>
                                <td class="font-weight-bold">#<?= $chamado['id'] ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($chamado['titulo']) ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        Protocolo: <?= htmlspecialchars($chamado['protocolo'] ?? 'N/A') ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge badge-<?= getBadgeClass($chamado['prioridade']) ?>">
                                        <?= ucfirst($chamado['prioridade']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= getStatusClass($chamado['status']) ?>">
                                        <i class="<?= getStatusIcon($chamado['status']) ?> mr-1"></i>
                                        <?= ucfirst($chamado['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <small><?= date('d/m/Y', strtotime($chamado['data_criacao'])) ?></small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="app/Views/chamados/visualizar.php?id=<?= $chamado['id'] ?>" 
                                           class="btn btn-primary" 
                                           title="Visualizar">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="app/Views/chamados/editar.php?id=<?= $chamado['id'] ?>" 
                                           class="btn btn-warning" 
                                           title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="app/Controllers/ChamadoController.php?action=excluir&id=<?= $chamado['id'] ?>" 
                                           class="btn btn-danger" 
                                           title="Excluir"
                                           data-confirm="Tem certeza que deseja excluir este chamado?">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h4>Nenhum chamado ativo</h4>
                    <p>Você não possui chamados em aberto no momento.</p>
                    <a href="chamados/criar.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Abrir Novo Chamado
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- Chamados Finalizados -->
<section class="chamados-section">
    <div class="container">
        <div class="section-header">
            <h2><i class="fas fa-check-double"></i> Histórico de Chamados</h2>
            <p class="text-muted">Chamados resolvidos e cancelados</p>
        </div>
        
        <div class="card">
            <div class="card-body p-0">
                <?php if ($chamados_finalizados->num_rows > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th width="80">ID</th>
                                <th>Título</th>
                                <th width="150">Prioridade</th>
                                <th width="150">Status</th>
                                <th width="120">Finalizado em</th>
                                <th width="100" class="text-center">Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($chamado = $chamados_finalizados->fetch_assoc()): ?>
                            <tr class="chamado-finalizado">
                                <td class="font-weight-bold">#<?= $chamado['id'] ?></td>
                                <td>
                                    <?= htmlspecialchars($chamado['titulo']) ?>
                                    <br>
                                    <small class="text-muted">
                                        Protocolo: <?= htmlspecialchars($chamado['protocolo'] ?? 'N/A') ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge badge-<?= getBadgeClass($chamado['prioridade']) ?>">
                                        <?= ucfirst($chamado['prioridade']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?= getStatusClass($chamado['status']) ?>">
                                        <i class="<?= getStatusIcon($chamado['status']) ?> mr-1"></i>
                                        <?= ucfirst($chamado['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <small><?= date('d/m/Y', strtotime($chamado['data_finalizacao'])) ?></small>
                                </td>
                                <td class="text-center">
                                    <a href="app/Views/chamados/visualizar.php?id=<?= $chamado['id'] ?>" 
                                       class="btn btn-sm btn-info" 
                                       title="Visualizar">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-history"></i>
                    <h4>Nenhum histórico</h4>
                    <p>Você ainda não possui chamados finalizados.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<?php
// Inclui o footer
include_once 'layouts/footer.php';
?>

<style>
/* ============================================================================
   ESTILOS - DASHBOARD CLIENTE
   ============================================================================ */

/* Dashboard Header */
.dashboard-header {
    background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    padding: 40px 0;
    margin-top: -76px;
    padding-top: 116px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

/* Estatísticas */
.dashboard-stats {
    padding: 40px 0;
    margin-top: -30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    display: flex;
    align-items: center;
    transition: all 0.3s ease;
    border-left: 4px solid;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.stat-total { border-left-color: #6c757d; }
.stat-open { border-left-color: #007bff; }
.stat-progress { border-left-color: #ffc107; }
.stat-resolved { border-left-color: #28a745; }

.stat-icon {
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    font-size: 1.8rem;
    margin-right: 20px;
}

.stat-total .stat-icon {
    background: rgba(108, 117, 125, 0.1);
    color: #6c757d;
}

.stat-open .stat-icon {
    background: rgba(0, 123, 255, 0.1);
    color: #007bff;
}

.stat-progress .stat-icon {
    background: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.stat-resolved .stat-icon {
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.stat-content h3 {
    font-size: 2rem;
    font-weight: 700;
    margin-bottom: 5px;
    color: #212529;
}

.stat-content p {
    font-size: 0.9rem;
    color: #6c757d;
    margin-bottom: 0;
}

/* Seção de Chamados */
.chamados-section {
    padding: 40px 0;
}

.section-header {
    margin-bottom: 30px;
}

.section-header h2 {
    font-size: 1.8rem;
    font-weight: 700;
    color: #212529;
    margin-bottom: 10px;
}

.section-header h2 i {
    color: #007bff;
    margin-right: 10px;
}

/* Tabela */
.table {
    margin-bottom: 0;
}

.table thead th {
    background: #f8f9fa;
    color: #495057;
    font-weight: 600;
    border-bottom: 2px solid #dee2e6;
    padding: 15px;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
}

.table tbody tr {
    transition: all 0.2s ease;
}

.table tbody tr:hover {
    background: #f8f9fa;
}

.table tbody td {
    padding: 15px;
    vertical-align: middle;
}

.chamado-finalizado {
    opacity: 0.7;
}

.chamado-finalizado:hover {
    opacity: 1;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
}

.empty-state i {
    font-size: 5rem;
    color: #dee2e6;
    margin-bottom: 20px;
}

.empty-state h4 {
    color: #6c757d;
    margin-bottom: 10px;
}

.empty-state p {
    color: #adb5bd;
    margin-bottom: 25px;
}

/* Badges */
.badge {
    font-weight: 600;
    padding: 6px 12px;
    font-size: 0.85rem;
}

/* Botões */
.btn-group-sm .btn {
    padding: 5px 10px;
}

/* Animações */
@keyframes fadeInScale {
    from {
        opacity: 0;
        transform: scale(0.9);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}

.stat-card {
    animation: fadeInScale 0.4s ease-out;
}

.stat-card:nth-child(1) { animation-delay: 0.1s; }
.stat-card:nth-child(2) { animation-delay: 0.2s; }
.stat-card:nth-child(3) { animation-delay: 0.3s; }
.stat-card:nth-child(4) { animation-delay: 0.4s; }

/* Responsive */
@media (max-width: 768px) {
    .dashboard-header {
        padding: 30px 0;
        padding-top: 106px;
    }
    
    .dashboard-header h1 {
        font-size: 1.5rem;
    }
    
    .stat-card {
        margin-bottom: 15px;
    }
    
    .stat-icon {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
        margin-right: 15px;
    }
    
    .stat-content h3 {
        font-size: 1.5rem;
    }
    
    .section-header h2 {
        font-size: 1.4rem;
    }
    
    .table {
        font-size: 0.9rem;
    }
    
    .btn-group {
        display: flex;
        flex-direction: column;
    }
    
    .btn-group .btn {
        margin-bottom: 5px;
        border-radius: 5px !important;
    }
}

/* Modo Escuro (opcional) */
@media (prefers-color-scheme: dark) {
    .stat-card {
        background: #2d3748;
        color: #e2e8f0;
    }
    
    .stat-content h3 {
        color: #e2e8f0;
    }
    
    .stat-content p {
        color: #a0aec0;
    }
}
</style>

<script>
// Confirmação antes de excluir
document.querySelectorAll('[data-confirm]').forEach(element => {
    element.addEventListener('click', function(e) {
        const message = this.getAttribute('data-confirm');
        if (!confirm(message)) {
            e.preventDefault();
            return false;
        }
    });
});

// Atualização automática das estatísticas (opcional)
function atualizarEstatisticas() {
    // Implementar via AJAX se necessário
}

// Destacar linha ao passar o mouse
document.querySelectorAll('.table tbody tr').forEach(row => {
    row.addEventListener('mouseenter', function() {
        this.style.backgroundColor = '#f1f3f5';
    });
    
    row.addEventListener('mouseleave', function() {
        this.style.backgroundColor = '';
    });
});

// Tooltip para botões
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
});

// Animação de contagem para números
function animateValue(element, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        element.innerHTML = Math.floor(progress * (end - start) + start);
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

// Anima os números das estatísticas ao carregar
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.stat-content h3').forEach(element => {
        const finalValue = parseInt(element.textContent);
        element.textContent = '0';
        setTimeout(() => {
            animateValue(element, 0, finalValue, 1000);
        }, 300);
    });
});

// Filtro de chamados (implementação básica)
function filtrarChamados(status) {
    const rows = document.querySelectorAll('.table tbody tr');
    rows.forEach(row => {
        if (status === 'todos') {
            row.style.display = '';
        } else {
            const statusCell = row.querySelector('.badge').textContent.toLowerCase();
            row.style.display = statusCell.includes(status) ? '' : 'none';
        }
    });
}

// Auto-refresh da página a cada 5 minutos (opcional)
// setInterval(() => location.reload(), 300000);
</script>