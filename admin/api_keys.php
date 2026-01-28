<?php
/**
 * Chaves API - NetoNerd ITSM
 * Gerenciamento de chaves de API para aplicativo
 */

session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireAdmin();

$conn = getConnection();

// Verificar/criar tabela se não existir
$conn->query("CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(64) NOT NULL UNIQUE,
    descricao VARCHAR(255) DEFAULT NULL,
    cliente_nome VARCHAR(255) DEFAULT NULL,
    status ENUM('ativa', 'inativa', 'revogada') DEFAULT 'ativa',
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_expiracao DATETIME DEFAULT NULL,
    ultimo_uso DATETIME DEFAULT NULL,
    total_requisicoes INT DEFAULT 0,
    ip_permitido VARCHAR(255) DEFAULT NULL,
    criado_por INT DEFAULT NULL,
    INDEX idx_chave (chave),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Buscar chaves API
$api_keys = [];
$result = $conn->query("SELECT * FROM api_keys ORDER BY data_criacao DESC");
if ($result) {
    $api_keys = $result->fetch_all(MYSQLI_ASSOC);
}

// Mensagens de feedback
$mensagem = '';
$tipo_mensagem = '';

if (isset($_GET['msg'])) {
    switch ($_GET['msg']) {
        case 'criada':
            $mensagem = 'Chave API criada com sucesso!';
            $tipo_mensagem = 'success';
            break;
        case 'excluida':
            $mensagem = 'Chave API excluída com sucesso!';
            $tipo_mensagem = 'success';
            break;
        case 'atualizada':
            $mensagem = 'Chave API atualizada com sucesso!';
            $tipo_mensagem = 'success';
            break;
        case 'erro':
            $mensagem = 'Ocorreu um erro ao processar a solicitação.';
            $tipo_mensagem = 'danger';
            break;
    }
}

$page_title = "Chaves API - NetoNerd ITSM";
require_once '../includes/header.php';
?>

<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <?php if ($mensagem): ?>
            <div class="nn-alert nn-alert-<?= $tipo_mensagem ?> nn-animate-fade">
                <i class="fas fa-<?= $tipo_mensagem === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= htmlspecialchars($mensagem) ?>
            </div>
        <?php endif; ?>

        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h1 class="nn-card-title">
                    <i class="fas fa-code"></i>
                    Chaves API - Aplicativo
                </h1>
                <div>
                    <button class="nn-btn nn-btn-primary" data-bs-toggle="modal" data-bs-target="#addApiKeyModal">
                        <i class="fas fa-plus"></i>
                        Nova Chave API
                    </button>
                </div>
            </div>
        </div>

        <div class="nn-card nn-animate-fade" style="margin-top: 1rem;">
            <div class="nn-card-body">
                <div class="nn-alert nn-alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Endpoint de Validação:</strong>
                    <code><?= (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] ?>/api/validar_chave.php</code>
                </div>
            </div>
        </div>

        <?php if (count($api_keys) > 0): ?>
            <div class="nn-card nn-animate-slide">
                <div class="nn-card-body">
                    <div class="nn-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Chave</th>
                                    <th>Descrição</th>
                                    <th>Cliente</th>
                                    <th>Status</th>
                                    <th>Último Uso</th>
                                    <th>Requisições</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($api_keys as $key): ?>
                                    <tr>
                                        <td data-label="ID"><?= $key['id'] ?></td>
                                        <td data-label="Chave">
                                            <div class="d-flex align-items-center gap-2">
                                                <code id="key-<?= $key['id'] ?>" class="text-truncate" style="max-width: 150px;">
                                                    <?= htmlspecialchars($key['chave']) ?>
                                                </code>
                                                <button type="button" class="nn-btn nn-btn-sm nn-btn-secondary" onclick="copiarChave('<?= htmlspecialchars($key['chave']) ?>')" title="Copiar">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td data-label="Descrição"><?= htmlspecialchars($key['descricao'] ?? '-') ?></td>
                                        <td data-label="Cliente"><?= htmlspecialchars($key['cliente_nome'] ?? '-') ?></td>
                                        <td data-label="Status">
                                            <span class="nn-badge <?php
                                                echo match($key['status']) {
                                                    'ativa' => 'nn-badge-success',
                                                    'inativa' => 'nn-badge-secondary',
                                                    'revogada' => 'nn-badge-danger',
                                                    default => 'nn-badge-secondary'
                                                };
                                            ?>">
                                                <?= ucfirst($key['status']) ?>
                                            </span>
                                        </td>
                                        <td data-label="Último Uso">
                                            <?= $key['ultimo_uso'] ? date('d/m/Y H:i', strtotime($key['ultimo_uso'])) : 'Nunca' ?>
                                        </td>
                                        <td data-label="Requisições"><?= number_format($key['total_requisicoes']) ?></td>
                                        <td data-label="Ações">
                                            <div class="d-flex gap-1">
                                                <?php if ($key['status'] === 'ativa'): ?>
                                                    <form action="processar_api_key.php" method="POST" style="display:inline;">
                                                        <input type="hidden" name="id" value="<?= $key['id'] ?>">
                                                        <input type="hidden" name="acao" value="desativar">
                                                        <button type="submit" class="nn-btn nn-btn-warning nn-btn-sm" title="Desativar">
                                                            <i class="fas fa-pause"></i>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form action="processar_api_key.php" method="POST" style="display:inline;">
                                                        <input type="hidden" name="id" value="<?= $key['id'] ?>">
                                                        <input type="hidden" name="acao" value="ativar">
                                                        <button type="submit" class="nn-btn nn-btn-success nn-btn-sm" title="Ativar">
                                                            <i class="fas fa-play"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                <form action="processar_api_key.php" method="POST" style="display:inline;" onsubmit="return confirm('Deseja realmente excluir esta chave API?');">
                                                    <input type="hidden" name="id" value="<?= $key['id'] ?>">
                                                    <input type="hidden" name="acao" value="excluir">
                                                    <button type="submit" class="nn-btn nn-btn-danger nn-btn-sm" title="Excluir">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
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
                Nenhuma chave API cadastrada. Clique em "Nova Chave API" para criar uma.
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- Modal Nova Chave API -->
<div class="modal fade" id="addApiKeyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: var(--gradient-primary); color: white;">
                <h5 class="modal-title">
                    <i class="fas fa-plus"></i>
                    Nova Chave API
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="processar_api_key.php" method="POST">
                <input type="hidden" name="acao" value="criar">
                <div class="modal-body">
                    <div class="nn-form-group">
                        <label class="nn-form-label">Nome do Cliente</label>
                        <input type="text" name="cliente_nome" class="nn-form-control" placeholder="Nome do cliente ou empresa">
                    </div>

                    <div class="nn-form-group">
                        <label class="nn-form-label">Descrição</label>
                        <input type="text" name="descricao" class="nn-form-control" placeholder="Ex: App Android, App iOS...">
                    </div>

                    <div class="nn-form-group">
                        <label class="nn-form-label">Chave API</label>
                        <div class="input-group">
                            <input type="text" name="chave" id="chave_api" class="nn-form-control" placeholder="Será gerada automaticamente" readonly>
                            <button type="button" class="nn-btn nn-btn-secondary" onclick="gerarChaveAPI()">
                                <i class="fas fa-random"></i> Gerar
                            </button>
                        </div>
                        <small class="text-muted">A chave será gerada automaticamente ao salvar se deixar em branco</small>
                    </div>

                    <div class="nn-form-group">
                        <label class="nn-form-label">Status</label>
                        <select name="status" class="nn-form-control">
                            <option value="ativa">Ativa</option>
                            <option value="inativa">Inativa</option>
                        </select>
                    </div>

                    <div class="nn-form-group">
                        <label class="nn-form-label">Data de Expiração</label>
                        <input type="date" name="data_expiracao" class="nn-form-control">
                        <small class="text-muted">Deixe em branco para chave sem expiração</small>
                    </div>

                    <div class="nn-form-group">
                        <label class="nn-form-label">IPs Permitidos</label>
                        <input type="text" name="ip_permitido" class="nn-form-control" placeholder="Ex: 192.168.1.1, 10.0.0.1">
                        <small class="text-muted">Deixe em branco para permitir todos os IPs</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="nn-btn nn-btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="nn-btn nn-btn-primary">
                        <i class="fas fa-save"></i>
                        Criar Chave
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function gerarChaveAPI() {
    const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let chave = 'NN_';
    for (let i = 0; i < 32; i++) {
        chave += caracteres.charAt(Math.floor(Math.random() * caracteres.length));
    }
    document.getElementById('chave_api').value = chave;
}

function copiarChave(chave) {
    navigator.clipboard.writeText(chave).then(() => {
        alert('Chave copiada para a área de transferência!');
    }).catch(err => {
        // Fallback para navegadores mais antigos
        const textarea = document.createElement('textarea');
        textarea.value = chave;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('Chave copiada para a área de transferência!');
    });
}

// Gerar chave automaticamente ao abrir o modal
document.getElementById('addApiKeyModal').addEventListener('show.bs.modal', function () {
    gerarChaveAPI();
});
</script>

<?php
$conn->close();
require_once '../includes/footer.php';
?>
