<?php
session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

// PROTEÃ‡ÃƒO: Apenas administradores podem acessar
requireAdmin();

// Verificar autenticação de admin
if (!isset($_SESSION['id']) || $_SESSION['tipo'] !== 'admin') {
    header('Location: ../publics/login.php?erro=acesso_negado');
    exit();
}

$conn = getConnection();

// Processar atualização de configurações
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'atualizar') {
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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - NetoNerd Admin</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .main-container {
            padding: 30px 0;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            margin-bottom: 20px;
        }
        .card-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .config-group {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .config-group h4 {
            color: #667eea;
            margin-bottom: 20px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php if(file_exists('../routes/header_admin.php')) include '../routes/header_admin.php'; ?>

    <div class="container main-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h2 class="mb-0">
                            <i class="fas fa-cog"></i> Configurações do Sistema
                        </h2>
                    </div>
                    <div class="card-body">
                        <?php if (isset($sucesso)): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="fas fa-check-circle"></i> Configurações atualizadas com sucesso!
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($erro)): ?>
                            <div class="alert alert-danger alert-dismissible fade show">
                                <i class="fas fa-exclamation-triangle"></i> Erro ao atualizar configurações. Tente novamente.
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        <?php endif; ?>

                        <form method="POST">
                            <input type="hidden" name="acao" value="atualizar">

                            <!-- Configurações Gerais -->
                            <div class="config-group">
                                <h4><i class="fas fa-info-circle"></i> Geral</h4>
                                <?php foreach ($configs['geral'] ?? [] as $config): ?>
                                    <div class="form-group">
                                        <label for="<?php echo $config['chave']; ?>">
                                            <strong><?php echo ucfirst(str_replace('_', ' ', str_replace('sistema_', '', $config['chave']))); ?></strong>
                                        </label>
                                        <?php if ($config['descricao']): ?>
                                            <small class="form-text text-muted"><?php echo htmlspecialchars($config['descricao']); ?></small>
                                        <?php endif; ?>
                                        <input type="text"
                                               id="<?php echo $config['chave']; ?>"
                                               name="config[<?php echo $config['chave']; ?>]"
                                               class="form-control"
                                               value="<?php echo htmlspecialchars($config['valor']); ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Configurações de Email -->
                            <div class="config-group">
                                <h4><i class="fas fa-envelope"></i> Email</h4>
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Para configurar as credenciais SMTP, edite o arquivo <code>.env</code> na raiz do projeto.
                                </div>
                                <?php foreach ($configs['email'] ?? [] as $config): ?>
                                    <div class="form-group">
                                        <label for="<?php echo $config['chave']; ?>">
                                            <strong><?php echo ucfirst(str_replace('_', ' ', $config['chave'])); ?></strong>
                                        </label>
                                        <?php if ($config['descricao']): ?>
                                            <small class="form-text text-muted"><?php echo htmlspecialchars($config['descricao']); ?></small>
                                        <?php endif; ?>
                                        <?php if ($config['tipo'] === 'boolean'): ?>
                                            <div class="custom-control custom-switch">
                                                <input type="checkbox"
                                                       class="custom-control-input"
                                                       id="<?php echo $config['chave']; ?>"
                                                       name="config[<?php echo $config['chave']; ?>]"
                                                       value="1"
                                                       <?php echo $config['valor'] == '1' ? 'checked' : ''; ?>>
                                                <label class="custom-control-label" for="<?php echo $config['chave']; ?>">
                                                    Ativado
                                                </label>
                                            </div>
                                        <?php else: ?>
                                            <input type="text"
                                                   id="<?php echo $config['chave']; ?>"
                                                   name="config[<?php echo $config['chave']; ?>]"
                                                   class="form-control"
                                                   value="<?php echo htmlspecialchars($config['valor']); ?>">
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Configurações de Chamados -->
                            <div class="config-group">
                                <h4><i class="fas fa-ticket-alt"></i> Chamados</h4>
                                <?php foreach ($configs['chamados'] ?? [] as $config): ?>
                                    <div class="form-group">
                                        <label for="<?php echo $config['chave']; ?>">
                                            <strong><?php echo ucfirst(str_replace('_', ' ', $config['chave'])); ?></strong>
                                        </label>
                                        <?php if ($config['descricao']): ?>
                                            <small class="form-text text-muted"><?php echo htmlspecialchars($config['descricao']); ?></small>
                                        <?php endif; ?>
                                        <input type="text"
                                               id="<?php echo $config['chave']; ?>"
                                               name="config[<?php echo $config['chave']; ?>]"
                                               class="form-control"
                                               value="<?php echo htmlspecialchars($config['valor']); ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Configurações de Segurança -->
                            <div class="config-group">
                                <h4><i class="fas fa-shield-alt"></i> Segurança</h4>
                                <?php foreach ($configs['seguranca'] ?? [] as $config): ?>
                                    <div class="form-group">
                                        <label for="<?php echo $config['chave']; ?>">
                                            <strong><?php echo ucfirst(str_replace('_', ' ', $config['chave'])); ?></strong>
                                        </label>
                                        <?php if ($config['descricao']): ?>
                                            <small class="form-text text-muted"><?php echo htmlspecialchars($config['descricao']); ?></small>
                                        <?php endif; ?>
                                        <input type="number"
                                               id="<?php echo $config['chave']; ?>"
                                               name="config[<?php echo $config['chave']; ?>]"
                                               class="form-control"
                                               value="<?php echo htmlspecialchars($config['valor']); ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Configurações de Uploads -->
                            <div class="config-group">
                                <h4><i class="fas fa-upload"></i> Uploads</h4>
                                <?php foreach ($configs['uploads'] ?? [] as $config): ?>
                                    <div class="form-group">
                                        <label for="<?php echo $config['chave']; ?>">
                                            <strong><?php echo ucfirst(str_replace('_', ' ', $config['chave'])); ?></strong>
                                        </label>
                                        <?php if ($config['descricao']): ?>
                                            <small class="form-text text-muted"><?php echo htmlspecialchars($config['descricao']); ?></small>
                                        <?php endif; ?>
                                        <?php if ($config['tipo'] === 'json'): ?>
                                            <textarea id="<?php echo $config['chave']; ?>"
                                                      name="config[<?php echo $config['chave']; ?>]"
                                                      class="form-control"
                                                      rows="3"><?php echo htmlspecialchars($config['valor']); ?></textarea>
                                        <?php else: ?>
                                            <input type="number"
                                                   id="<?php echo $config['chave']; ?>"
                                                   name="config[<?php echo $config['chave']; ?>]"
                                                   class="form-control"
                                                   value="<?php echo htmlspecialchars($config['valor']); ?>">
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Informações do Sistema -->
                            <div class="config-group">
                                <h4><i class="fas fa-server"></i> Informações do Sistema</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <p><strong>Versão PHP:</strong> <?php echo PHP_VERSION; ?></p>
                                        <p><strong>Servidor Web:</strong> <?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Desconhecido'; ?></p>
                                    </div>
                                    <div class="col-md-6">
                                        <p><strong>Banco de Dados:</strong> MySQL <?php echo $conn->server_info; ?></p>
                                        <p><strong>Ambiente:</strong> <?php echo getenv('APP_ENV') ?: 'development'; ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save"></i> Salvar Configurações
                                </button>
                                <a href="dashboard.php" class="btn btn-secondary btn-lg ml-2">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>
