<?php
/**
 * Configurações - NetoNerd ITSM v2.1
 */

require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireAdmin();

$conn = getConnection();

// Processar atualização de configurações
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'atualizar') {
    requireCsrfToken();
    try {
        foreach ($_POST['config'] as $chave => $valor) {
            $chave_safe = htmlspecialchars($chave);
            $valor_safe = htmlspecialchars($valor);

            $stmt = $conn->prepare("
                INSERT INTO configuracoes_sistema (chave, valor, tipo)
                VALUES (?, ?, 'string')
                ON DUPLICATE KEY UPDATE valor = ?
            ");
            $stmt->bind_param("sss", $chave_safe, $valor_safe, $valor_safe);
            $stmt->execute();
            $stmt->close();
        }

        $sucesso = true;
    } catch (Exception $e) {
        error_log("Erro ao atualizar configurações: " . $e->getMessage());
        $erro = true;
    }
}

// Buscar configurações atuais
$configs = [];
$result = $conn->query("SELECT * FROM configuracoes_sistema ORDER BY grupo, chave");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $configs[$row['grupo']][] = $row;
    }
}

$page_title = "Configurações - NetoNerd ITSM";
require_once '../includes/header.php';
?>

<div class="nn-main-wrapper">
    <div class="nn-content nn-content-full">

        <div class="nn-card nn-animate-fade">
            <div class="nn-card-header">
                <h1 class="nn-card-title">
                    <i class="fas fa-cog"></i>
                    Configurações do Sistema
                </h1>
            </div>
        </div>

        <?php if (isset($sucesso)): ?>
            <div class="nn-alert nn-alert-success nn-animate-fade">
                <i class="fas fa-check-circle"></i>
                Configurações atualizadas com sucesso!
            </div>
        <?php endif; ?>

        <?php if (isset($erro)): ?>
            <div class="nn-alert nn-alert-danger nn-animate-fade">
                <i class="fas fa-exclamation-triangle"></i>
                Erro ao atualizar configurações. Tente novamente.
            </div>
        <?php endif; ?>

        <form method="POST">
            <?php echo csrfField(); ?>
            <input type="hidden" name="acao" value="atualizar">

            <!-- Configurações Gerais -->
            <div class="nn-card">
                <div class="nn-card-header">
                    <h2 class="nn-card-title">
                        <i class="fas fa-info-circle"></i>
                        Geral
                    </h2>
                </div>
                <div class="nn-card-body">
                    <?php foreach ($configs['geral'] ?? [] as $config): ?>
                        <div class="nn-form-group">
                            <label class="nn-form-label" for="<?php echo htmlspecialchars($config['chave']); ?>">
                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', str_replace('sistema_', '', $config['chave'])))); ?>
                            </label>
                            <?php if ($config['descricao']): ?>
                                <small class="nn-text-light" style="display: block; margin-bottom: 5px;"><?php echo htmlspecialchars($config['descricao']); ?></small>
                            <?php endif; ?>
                            <input type="text"
                                   id="<?php echo htmlspecialchars($config['chave']); ?>"
                                   name="config[<?php echo htmlspecialchars($config['chave']); ?>]"
                                   class="nn-form-control"
                                   value="<?php echo htmlspecialchars($config['valor']); ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Configurações de Email -->
            <div class="nn-card">
                <div class="nn-card-header">
                    <h2 class="nn-card-title">
                        <i class="fas fa-envelope"></i>
                        Email
                    </h2>
                </div>
                <div class="nn-card-body">
                    <div class="nn-alert nn-alert-info">
                        <i class="fas fa-info-circle"></i>
                        Para configurar as credenciais SMTP, edite o arquivo <code>.env</code> na raiz do projeto.
                    </div>
                    <?php foreach ($configs['email'] ?? [] as $config): ?>
                        <div class="nn-form-group">
                            <label class="nn-form-label" for="<?php echo htmlspecialchars($config['chave']); ?>">
                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $config['chave']))); ?>
                            </label>
                            <?php if ($config['descricao']): ?>
                                <small class="nn-text-light" style="display: block; margin-bottom: 5px;"><?php echo htmlspecialchars($config['descricao']); ?></small>
                            <?php endif; ?>
                            <?php if ($config['tipo'] === 'boolean'): ?>
                                <div class="nn-d-flex nn-align-center nn-gap-1">
                                    <input type="checkbox"
                                           id="<?php echo htmlspecialchars($config['chave']); ?>"
                                           name="config[<?php echo htmlspecialchars($config['chave']); ?>]"
                                           value="1"
                                           <?php echo $config['valor'] == '1' ? 'checked' : ''; ?>>
                                    <label for="<?php echo htmlspecialchars($config['chave']); ?>">Ativado</label>
                                </div>
                            <?php else: ?>
                                <input type="text"
                                       id="<?php echo htmlspecialchars($config['chave']); ?>"
                                       name="config[<?php echo htmlspecialchars($config['chave']); ?>]"
                                       class="nn-form-control"
                                       value="<?php echo htmlspecialchars($config['valor']); ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Configurações de Chamados -->
            <div class="nn-card">
                <div class="nn-card-header">
                    <h2 class="nn-card-title">
                        <i class="fas fa-ticket-alt"></i>
                        Chamados
                    </h2>
                </div>
                <div class="nn-card-body">
                    <?php foreach ($configs['chamados'] ?? [] as $config): ?>
                        <div class="nn-form-group">
                            <label class="nn-form-label" for="<?php echo htmlspecialchars($config['chave']); ?>">
                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $config['chave']))); ?>
                            </label>
                            <?php if ($config['descricao']): ?>
                                <small class="nn-text-light" style="display: block; margin-bottom: 5px;"><?php echo htmlspecialchars($config['descricao']); ?></small>
                            <?php endif; ?>
                            <input type="text"
                                   id="<?php echo htmlspecialchars($config['chave']); ?>"
                                   name="config[<?php echo htmlspecialchars($config['chave']); ?>]"
                                   class="nn-form-control"
                                   value="<?php echo htmlspecialchars($config['valor']); ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Configurações de Segurança -->
            <div class="nn-card">
                <div class="nn-card-header">
                    <h2 class="nn-card-title">
                        <i class="fas fa-shield-alt"></i>
                        Segurança
                    </h2>
                </div>
                <div class="nn-card-body">
                    <?php foreach ($configs['seguranca'] ?? [] as $config): ?>
                        <div class="nn-form-group">
                            <label class="nn-form-label" for="<?php echo htmlspecialchars($config['chave']); ?>">
                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $config['chave']))); ?>
                            </label>
                            <?php if ($config['descricao']): ?>
                                <small class="nn-text-light" style="display: block; margin-bottom: 5px;"><?php echo htmlspecialchars($config['descricao']); ?></small>
                            <?php endif; ?>
                            <input type="number"
                                   id="<?php echo htmlspecialchars($config['chave']); ?>"
                                   name="config[<?php echo htmlspecialchars($config['chave']); ?>]"
                                   class="nn-form-control"
                                   value="<?php echo htmlspecialchars($config['valor']); ?>">
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Configurações de Uploads -->
            <div class="nn-card">
                <div class="nn-card-header">
                    <h2 class="nn-card-title">
                        <i class="fas fa-upload"></i>
                        Uploads
                    </h2>
                </div>
                <div class="nn-card-body">
                    <?php foreach ($configs['uploads'] ?? [] as $config): ?>
                        <div class="nn-form-group">
                            <label class="nn-form-label" for="<?php echo htmlspecialchars($config['chave']); ?>">
                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $config['chave']))); ?>
                            </label>
                            <?php if ($config['descricao']): ?>
                                <small class="nn-text-light" style="display: block; margin-bottom: 5px;"><?php echo htmlspecialchars($config['descricao']); ?></small>
                            <?php endif; ?>
                            <?php if ($config['tipo'] === 'json'): ?>
                                <textarea id="<?php echo htmlspecialchars($config['chave']); ?>"
                                          name="config[<?php echo htmlspecialchars($config['chave']); ?>]"
                                          class="nn-form-control"
                                          rows="3"><?php echo htmlspecialchars($config['valor']); ?></textarea>
                            <?php else: ?>
                                <input type="number"
                                       id="<?php echo htmlspecialchars($config['chave']); ?>"
                                       name="config[<?php echo htmlspecialchars($config['chave']); ?>]"
                                       class="nn-form-control"
                                       value="<?php echo htmlspecialchars($config['valor']); ?>">
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Informações do Sistema -->
            <div class="nn-card">
                <div class="nn-card-header">
                    <h2 class="nn-card-title">
                        <i class="fas fa-server"></i>
                        Informações do Sistema
                    </h2>
                </div>
                <div class="nn-card-body">
                    <div class="nn-stats-grid">
                        <div class="nn-stat-card">
                            <div class="nn-stat-label">Versão PHP</div>
                            <div class="nn-stat-value" style="font-size: 1.2rem;"><?php echo PHP_VERSION; ?></div>
                        </div>
                        <div class="nn-stat-card">
                            <div class="nn-stat-label">Servidor Web</div>
                            <div class="nn-stat-value" style="font-size: 1.2rem;"><?php echo htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Desconhecido'); ?></div>
                        </div>
                        <div class="nn-stat-card">
                            <div class="nn-stat-label">Banco de Dados</div>
                            <div class="nn-stat-value" style="font-size: 1.2rem;">MySQL <?php echo htmlspecialchars($conn->server_info); ?></div>
                        </div>
                        <div class="nn-stat-card">
                            <div class="nn-stat-label">Ambiente</div>
                            <div class="nn-stat-value" style="font-size: 1.2rem;"><?php echo htmlspecialchars(getenv('APP_ENV') ?: 'development'); ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="nn-text-center nn-mt-3">
                <button type="submit" class="nn-btn nn-btn-primary nn-btn-lg">
                    <i class="fas fa-save"></i>
                    Salvar Configurações
                </button>
                <a href="dashboard.php" class="nn-btn nn-btn-secondary nn-btn-lg">
                    <i class="fas fa-times"></i>
                    Cancelar
                </a>
            </div>
        </form>

    </div>
</div>

<?php
require_once '../includes/footer.php';
$conn->close();
?>
