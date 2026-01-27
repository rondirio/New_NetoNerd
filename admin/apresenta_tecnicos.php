<?php
/**
 * Gerenciar Técnicos - NetoNerd ITSM v2.0
 * Lista completa de técnicos com estatísticas
 */

session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

// PROTEÇÃO: Apenas administradores
requireAdmin();

$conn = getConnection();

// Buscar todos os técnicos com estatísticas
$sql = "
    SELECT
        t.*,
        COUNT(DISTINCT c.id) as total_chamados,
        COUNT(DISTINCT CASE WHEN c.status IN ('aberto', 'em andamento') THEN c.id END) as chamados_abertos,
        COUNT(DISTINCT CASE WHEN c.status = 'resolvido' THEN c.id END) as chamados_resolvidos,
        MAX(c.data_abertura) as ultima_atividade
    FROM tecnicos t
    LEFT JOIN chamados c ON t.id = c.tecnico_id
    GROUP BY t.id
    ORDER BY t.nome ASC
";
$result = $conn->query($sql);

// Armazena resultados em array
$tecnicos = [];
while ($row = $result->fetch_assoc()) {
    $tecnicos[] = $row;
}

// Stats gerais
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM tecnicos WHERE status_tecnico = 'Active'");
$stmt->execute();
$total_ativos = $stmt->get_result()->fetch_assoc()['total'];

$page_title = "Gerenciar Técnicos - NetoNerd ITSM";
require_once '../includes/header.php';
?>

<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h1 class="nn-card-title">
                    <i class="fas fa-users-cog"></i>
                    Gerenciar Técnicos
                </h1>
                <div>
                    <span class="nn-badge nn-badge-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo $total_ativos; ?> Ativos
                    </span>
                    <a href="dashboard.php" class="nn-btn nn-btn-secondary nn-btn-sm ms-2">
                        <i class="fas fa-arrow-left"></i>
                        Voltar
                    </a>
                </div>
            </div>
        </div>

        <div class="nn-card nn-animate-slide">
            <div class="nn-card-body">
                <div class="nn-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Técnico</th>
                                <th>Matrícula</th>
                                <th>Email</th>
                                <th>Veículo</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Abertos</th>
                                <th class="text-center">Resolvidos</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($tecnicos as $tec): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="nn-avatar">
                                                <?php echo strtoupper(substr($tec['nome'], 0, 1)); ?>
                                            </div>
                                            <strong><?php echo htmlspecialchars($tec['nome']); ?></strong>
                                        </div>
                                    </td>
                                    <td><span class="nn-badge nn-badge-info"><?php echo htmlspecialchars($tec['matricula']); ?></span></td>
                                    <td><?php echo htmlspecialchars($tec['email']); ?></td>
                                    <td><?php echo htmlspecialchars($tec['carro_do_dia'] ?? 'N/A'); ?></td>
                                    <td class="text-center"><strong><?php echo $tec['total_chamados']; ?></strong></td>
                                    <td class="text-center">
                                        <?php if ($tec['chamados_abertos'] > 0): ?>
                                            <span class="nn-badge nn-badge-warning"><?php echo $tec['chamados_abertos']; ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="nn-badge nn-badge-success"><?php echo $tec['chamados_resolvidos']; ?></span>
                                    </td>
                                    <td>
                                        <span class="nn-badge <?php echo $tec['status_tecnico'] === 'Active' ? 'nn-badge-success' : 'nn-badge-secondary'; ?>">
                                            <?php echo $tec['status_tecnico']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="editar_tecnico.php?id=<?php echo $tec['id']; ?>" class="nn-btn nn-btn-primary nn-btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
.nn-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--gradient-primary);
    color: white;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    margin-right: 10px;
}
</style>

<?php
$conn->close();
require_once '../includes/footer.php';
?>