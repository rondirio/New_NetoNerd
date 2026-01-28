<?php
/**
 * Chaves API - NetoNerd ITSM
 * Gerenciamento de chaves de API para aplicativo
 * Cada chave está vinculada ao banco de dados do cliente na Hostinger
 */

session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireAdmin();

$conn = getConnection();

// Verificar/criar tabela se não existir (com campos de conexão do BD)
$conn->query("CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(64) NOT NULL UNIQUE,
    descricao VARCHAR(255) DEFAULT NULL,
    cliente_nome VARCHAR(255) DEFAULT NULL,
    db_host VARCHAR(255) NOT NULL DEFAULT '',
    db_nome VARCHAR(255) NOT NULL DEFAULT '',
    db_usuario VARCHAR(255) NOT NULL DEFAULT '',
    db_senha VARCHAR(255) NOT NULL DEFAULT '',
    db_porta INT DEFAULT 3306,
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

// Verificar se colunas de BD existem, se não, adicionar
$result = $conn->query("SHOW COLUMNS FROM api_keys LIKE 'db_host'");
if ($result->num_rows === 0) {
    $conn->query("ALTER TABLE api_keys
        ADD COLUMN db_host VARCHAR(255) NOT NULL DEFAULT '' AFTER cliente_nome,
        ADD COLUMN db_nome VARCHAR(255) NOT NULL DEFAULT '' AFTER db_host,
        ADD COLUMN db_usuario VARCHAR(255) NOT NULL DEFAULT '' AFTER db_nome,
        ADD COLUMN db_senha VARCHAR(255) NOT NULL DEFAULT '' AFTER db_usuario,
        ADD COLUMN db_porta INT DEFAULT 3306 AFTER db_senha");
}

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
        case 'conexao_ok':
            $mensagem = 'Conexão com o banco de dados testada com sucesso!';
            $tipo_mensagem = 'success';
            break;
        case 'conexao_erro':
            $mensagem = 'Erro ao conectar ao banco de dados. Verifique as credenciais.';
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
                <p class="mt-2 mb-0">
                    <small class="text-muted">
                        <i class="fas fa-database"></i>
                        Cada chave API está vinculada ao banco de dados do cliente na Hostinger.
                        O app usa a chave para autenticar e receber as credenciais de conexão.
                    </small>
                </p>
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
                                    <th>Cliente</th>
                                    <th>Banco de Dados</th>
                                    <th>Status</th>
                                    <th>Último Uso</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($api_keys as $key): ?>
                                    <tr>
                                        <td data-label="ID"><?= $key['id'] ?></td>
                                        <td data-label="Chave">
                                            <div class="d-flex align-items-center gap-2">
                                                <code class="text-truncate" style="max-width: 120px;">
                                                    <?= htmlspecialchars(substr($key['chave'], 0, 15)) ?>...
                                                </code>
                                                <button type="button" class="nn-btn nn-btn-sm nn-btn-secondary" onclick="copiarChave('<?= htmlspecialchars($key['chave']) ?>')" title="Copiar Chave">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td data-label="Cliente"><?= htmlspecialchars($key['cliente_nome'] ?? '-') ?></td>
                                        <td data-label="Banco de Dados">
                                            <?php if (!empty($key['db_host'])): ?>
                                                <small>
                                                    <i class="fas fa-server text-muted"></i>
                                                    <?= htmlspecialchars($key['db_host']) ?><br>
                                                    <i class="fas fa-database text-muted"></i>
                                                    <?= htmlspecialchars($key['db_nome']) ?>
                                                </small>
                                            <?php else: ?>
                                                <span class="text-muted">Não configurado</span>
                                            <?php endif; ?>
                                        </td>
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
                                        <td data-label="Ações">
                                            <div class="d-flex gap-1 flex-wrap">
                                                <!-- Testar Conexão -->
                                                <?php if (!empty($key['db_host'])): ?>
                                                    <form action="processar_api_key.php" method="POST" style="display:inline;">
                                                        <input type="hidden" name="id" value="<?= $key['id'] ?>">
                                                        <input type="hidden" name="acao" value="testar_conexao">
                                                        <button type="submit" class="nn-btn nn-btn-info nn-btn-sm" title="Testar Conexão">
                                                            <i class="fas fa-plug"></i>
                                                        </button>
                                                    </form>
                                                <?php endif; ?>

                                                <!-- Ativar/Desativar -->
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

                                                <!-- Excluir -->
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
    <div class="modal-dialog modal-lg">
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
                    <div class="row">
                        <!-- Informações Gerais -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-info-circle"></i> Informações Gerais
                            </h6>

                            <div class="nn-form-group">
                                <label class="nn-form-label">Nome do Cliente *</label>
                                <input type="text" name="cliente_nome" class="nn-form-control" placeholder="Nome do cliente ou empresa" required>
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
                                        <i class="fas fa-random"></i>
                                    </button>
                                </div>
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
                        </div>

                        <!-- Dados do Banco de Dados -->
                        <div class="col-md-6">
                            <h6 class="text-primary mb-3">
                                <i class="fas fa-database"></i> Banco de Dados (Hostinger)
                            </h6>

                            <div class="nn-form-group">
                                <label class="nn-form-label">Host do Banco *</label>
                                <input type="text" name="db_host" class="nn-form-control" placeholder="Ex: sql123.main-hosting.eu" required>
                                <small class="text-muted">Encontre no painel da Hostinger</small>
                            </div>

                            <div class="nn-form-group">
                                <label class="nn-form-label">Nome do Banco *</label>
                                <input type="text" name="db_nome" class="nn-form-control" placeholder="Ex: u123456789_sistema" required>
                            </div>

                            <div class="nn-form-group">
                                <label class="nn-form-label">Usuário do Banco *</label>
                                <input type="text" name="db_usuario" class="nn-form-control" placeholder="Ex: u123456789_admin" required>
                            </div>

                            <div class="nn-form-group">
                                <label class="nn-form-label">Senha do Banco *</label>
                                <div class="input-group">
                                    <input type="password" name="db_senha" id="db_senha" class="nn-form-control" placeholder="Senha do banco de dados" required>
                                    <button type="button" class="nn-btn nn-btn-secondary" onclick="toggleSenha()">
                                        <i class="fas fa-eye" id="toggleIcon"></i>
                                    </button>
                                </div>
                            </div>

                            <div class="nn-form-group">
                                <label class="nn-form-label">Porta</label>
                                <input type="number" name="db_porta" class="nn-form-control" value="3306" placeholder="3306">
                            </div>
                        </div>
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
        const textarea = document.createElement('textarea');
        textarea.value = chave;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand('copy');
        document.body.removeChild(textarea);
        alert('Chave copiada para a área de transferência!');
    });
}

function toggleSenha() {
    const input = document.getElementById('db_senha');
    const icon = document.getElementById('toggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
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
