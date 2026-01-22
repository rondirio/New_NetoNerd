<?php
/**
 * Categorias - NetoNerd ITSM v2.0
 * Gerenciamento de categorias de chamados
 */

session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireAdmin();

$conn = getConnection();

// Buscar categorias com estatísticas
$sql = "
    SELECT
        cat.*,
        COUNT(c.id) as total_chamados,
        COUNT(CASE WHEN c.status IN ('aberto', 'em andamento') THEN 1 END) as chamados_abertos
    FROM categorias_chamado cat
    LEFT JOIN chamados c ON cat.id = c.categoria_id
    GROUP BY cat.id
    ORDER BY cat.nome ASC
";
$result = $conn->query($sql);
$categorias = $result->fetch_all(MYSQLI_ASSOC);

$page_title = "Categorias - NetoNerd ITSM";
require_once '../includes/header.php';
?>

<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h1 class="nn-card-title">
                    <i class="fas fa-tags"></i>
                    Gerenciar Categorias
                </h1>
                <div>
                    <button class="nn-btn nn-btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoriaModal">
                        <i class="fas fa-plus"></i>
                        Nova Categoria
                    </button>
                </div>
            </div>
        </div>

        <?php if (count($categorias) > 0): ?>
            <div class="row g-3">
                <?php foreach ($categorias as $cat): ?>
                    <div class="col-md-4">
                        <div class="nn-card nn-animate-slide">
                            <div class="nn-card-body">
                                <div class="d-flex align-items-center mb-3">
                                    <div style="width: 50px; height: 50px; border-radius: 10px; background: <?php echo htmlspecialchars($cat['cor'] ?? '#007bff'); ?>; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; margin-right: 15px;">
                                        <i class="fas <?php echo htmlspecialchars($cat['icone'] ?? 'fa-tag'); ?>"></i>
                                    </div>
                                    <div>
                                        <h5 class="mb-0"><?php echo htmlspecialchars($cat['nome']); ?></h5>
                                        <small class="text-muted"><?php echo htmlspecialchars($cat['descricao'] ?? 'Sem descrição'); ?></small>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <div class="row">
                                        <div class="col-6 text-center">
                                            <div class="nn-stat-value" style="font-size: 1.5rem;"><?php echo $cat['total_chamados']; ?></div>
                                            <small class="text-muted">Total</small>
                                        </div>
                                        <div class="col-6 text-center">
                                            <div class="nn-stat-value" style="font-size: 1.5rem;"><?php echo $cat['chamados_abertos']; ?></div>
                                            <small class="text-muted">Abertos</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="nn-badge <?php echo ($cat['ativo'] ?? 1) ? 'nn-badge-success' : 'nn-badge-secondary'; ?>">
                                        <?php echo ($cat['ativo'] ?? 1) ? 'Ativa' : 'Inativa'; ?>
                                    </span>
                                    <div>
                                        <a href="processar_categoria.php?id=<?php echo $cat['id']; ?>&acao=editar" class="nn-btn nn-btn-primary nn-btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="processar_categoria.php" method="POST" style="display:inline;" onsubmit="return confirm('Deseja realmente excluir esta categoria?');">
                                            <input type="hidden" name="id" value="<?php echo $cat['id']; ?>">
                                            <input type="hidden" name="acao" value="excluir">
                                            <button type="submit" class="nn-btn nn-btn-danger nn-btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="nn-alert nn-alert-info nn-animate-fade">
                <i class="fas fa-info-circle"></i>
                Nenhuma categoria cadastrada. Clique em "Nova Categoria" para adicionar.
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- Modal Nova Categoria -->
<div class="modal fade" id="addCategoriaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--gradient-primary); color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i>
                    Nova Categoria
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="processar_categoria.php" method="POST">
                <input type="hidden" name="acao" value="criar">
                <div class="modal-body">
                    <div class="nn-form-group">
                        <label class="nn-form-label">Nome da Categoria *</label>
                        <input type="text" name="nome" class="nn-form-control" required>
                    </div>
                    <div class="nn-form-group">
                        <label class="nn-form-label">Descrição</label>
                        <textarea name="descricao" class="nn-form-control" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="nn-form-group">
                                <label class="nn-form-label">Cor *</label>
                                <input type="color" name="cor" class="nn-form-control" value="#007bff" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="nn-form-group">
                                <label class="nn-form-label">Ícone (FontAwesome) *</label>
                                <input type="text" name="icone" class="nn-form-control" placeholder="fa-tag" value="fa-tag" required>
                                <small class="text-muted">Ex: fa-laptop, fa-network-wired, fa-bug</small>
                            </div>
                        </div>
                    </div>
                    <div class="nn-form-group">
                        <label class="nn-form-label">
                            <input type="checkbox" name="ativo" value="1" checked>
                            Categoria Ativa
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="nn-btn nn-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="nn-btn nn-btn-primary">
                        <i class="fas fa-save"></i>
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
$conn->close();
require_once '../includes/footer.php';
?>
