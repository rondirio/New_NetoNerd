<?php
/**
 * Licenças - NetoNerd ITSM v2.0
 * Gerenciamento de licenças do sistema
 */

session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireAdmin();

$conn = getConnection();

// Buscar licenças (verificar se tabela existe)
$licencas = [];
$produtos = [];

$result = $conn->query("SHOW TABLES LIKE 'licencas'");
if ($result->num_rows > 0) {
    $result = $conn->query("SELECT l.*, p.nome as produto_nome FROM licencas l LEFT JOIN produtos_licenciaveis p ON l.produto_id = p.id ORDER BY l.data_criacao DESC");
    if ($result) {
        $licencas = $result->fetch_all(MYSQLI_ASSOC);
    }
}

$result = $conn->query("SHOW TABLES LIKE 'produtos_licenciaveis'");
if ($result->num_rows > 0) {
    $result = $conn->query("SELECT * FROM produtos_licenciaveis WHERE ativo = 1");
    if ($result) {
        $produtos = $result->fetch_all(MYSQLI_ASSOC);
    }
}

$page_title = "Licenças - NetoNerd ITSM";
require_once '../includes/header.php';
?>

<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h1 class="nn-card-title">
                    <i class="fas fa-key"></i>
                    Gerenciar Licenças
                </h1>
                <div>
                    <button class="nn-btn nn-btn-primary" data-bs-toggle="modal" data-bs-target="#addLicencaModal">
                        <i class="fas fa-plus"></i>
                        Nova Licença
                    </button>
                </div>
            </div>
        </div>

        <?php if (count($licencas) > 0): ?>
            <div class="nn-card nn-animate-slide">
                <div class="nn-card-body">
                    <div class="nn-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Produto</th>
                                    <th>Cliente</th>
                                    <th>Chave</th>
                                    <th>Status</th>
                                    <th>Validade</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($licencas as $lic): ?>
                                    <tr>
                                        <td><?php echo $lic['id']; ?></td>
                                        <td><?php echo htmlspecialchars($lic['produto_nome'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($lic['cliente_nome'] ?? 'N/A'); ?></td>
                                        <td><code><?php echo htmlspecialchars(substr($lic['chave_licenca'] ?? $lic['chave'] ?? '', 0, 20)) . '...'; ?></code></td>
                                        <td>
                                            <span class="nn-badge <?php echo ($lic['status'] ?? $lic['ativo'] ?? 1) == 'ativa' || ($lic['status'] ?? $lic['ativo'] ?? 1) == 1 ? 'nn-badge-success' : 'nn-badge-secondary'; ?>">
                                                <?php echo ucfirst($lic['status'] ?? (($lic['ativo'] ?? 1) ? 'Ativa' : 'Inativa')); ?>
                                            </span>
                                        </td>
                                        <td><?php echo isset($lic['data_validade']) ? date('d/m/Y', strtotime($lic['data_validade'])) : 'Sem validade'; ?></td>
                                        <td>
                                            <form action="processar_licenca.php" method="POST" style="display:inline;" onsubmit="return confirm('Deseja realmente excluir esta licença?');">
                                                <input type="hidden" name="id" value="<?php echo $lic['id']; ?>">
                                                <input type="hidden" name="acao" value="excluir">
                                                <button type="submit" class="nn-btn nn-btn-danger nn-btn-sm">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="nn-alert nn-alert-info nn-animate-fade">
                <i class="fas fa-info-circle"></i>
                Nenhuma licença cadastrada. Clique em "Nova Licença" para adicionar.
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- Modal Nova Licença -->
<div class="modal fade" id="addLicencaModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--gradient-primary); color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i>
                    Nova Licença
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="processar_licenca.php" method="POST">
                <input type="hidden" name="acao" value="criar">
                <div class="modal-body">
                    <?php if (count($produtos) > 0): ?>
                        <div class="nn-form-group">
                            <label class="nn-form-label">Produto *</label>
                            <select name="produto_id" class="nn-form-control" required>
                                <option value="">Selecione um produto</option>
                                <?php foreach ($produtos as $prod): ?>
                                    <option value="<?php echo $prod['id']; ?>">
                                        <?php echo htmlspecialchars($prod['nome']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php else: ?>
                        <div class="nn-alert nn-alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            Nenhum produto licenciável cadastrado. Cadastre produtos primeiro.
                        </div>
                    <?php endif; ?>

                    <div class="nn-form-group">
                        <label class="nn-form-label">Nome do Cliente *</label>
                        <input type="text" name="cliente_nome" class="nn-form-control" required>
                    </div>

                    <div class="nn-form-group">
                        <label class="nn-form-label">Chave de Licença</label>
                        <div class="input-group">
                            <input type="text" name="chave_licenca" id="chave_licenca" class="nn-form-control" placeholder="Será gerada automaticamente se deixar em branco">
                            <button type="button" class="nn-btn nn-btn-secondary" onclick="gerarChave()">
                                <i class="fas fa-random"></i> Gerar
                            </button>
                        </div>
                    </div>

                    <div class="nn-form-group">
                        <label class="nn-form-label">Status *</label>
                        <select name="status" class="nn-form-control" required>
                            <option value="ativa">Ativa</option>
                            <option value="inativa">Inativa</option>
                            <option value="expirada">Expirada</option>
                            <option value="suspensa">Suspensa</option>
                        </select>
                    </div>

                    <div class="nn-form-group">
                        <label class="nn-form-label">Data de Validade</label>
                        <input type="date" name="data_validade" class="nn-form-control">
                        <small class="text-muted">Deixe em branco para licença sem validade</small>
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

<script>
function gerarChave() {
    const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let chave = '';
    for (let i = 0; i < 5; i++) {
        if (i > 0) chave += '-';
        for (let j = 0; j < 4; j++) {
            chave += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
        }
    }
    document.getElementById('chave_licenca').value = chave;
}
</script>

<?php
$conn->close();
require_once '../includes/footer.php';
?>
