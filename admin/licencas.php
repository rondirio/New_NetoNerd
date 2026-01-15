<?php
session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';
require_once '../config/LicenseManager.php';

// PROTEÇÃO: Apenas administradores podem acessar
requireAdmin();

$conn = getConnection();
$licenseManager = new LicenseManager();

// Buscar produtos
$produtos = $conn->query("SELECT * FROM produtos_licenciaveis WHERE ativo = 1 ORDER BY nome");

// Buscar clientes
$clientes = $conn->query("SELECT id, nome, email FROM clientes ORDER BY nome");

// Filtros
$filtro_status = $_GET['status'] ?? '';
$filtro_produto = $_GET['produto'] ?? '';

$filtros = [];
if ($filtro_status) $filtros['status'] = $filtro_status;
if ($filtro_produto) $filtros['produto_id'] = $filtro_produto;

// Listar licenças
$licencas = $licenseManager->listarLicencas($filtros);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Licenças - NetoNerd Admin</title>
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
        .licenca-row {
            border-left: 5px solid #667eea;
            padding: 15px;
            margin: 10px 0;
            background: #f8f9fa;
            border-radius: 8px;
        }
        .api-key-box {
            background: #2c3e50;
            color: #2ecc71;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 12px;
            cursor: pointer;
            position: relative;
        }
        .api-key-box:hover {
            background: #34495e;
        }
        .status-trial { border-left-color: #17a2b8; }
        .status-ativa { border-left-color: #28a745; }
        .status-suspensa { border-left-color: #dc3545; }
        .status-expirada { border-left-color: #6c757d; }
    </style>
</head>
<body>
    <?php if(file_exists('../routes/header_admin.php')) include '../routes/header_admin.php'; ?>

    <div class="container-fluid main-container">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="mb-0">
                            <i class="fas fa-key"></i> Gerenciamento de Licenças
                        </h2>
                        <button class="btn btn-light" data-toggle="modal" data-target="#modalNovaLicenca">
                            <i class="fas fa-plus"></i> Gerar Nova Licença
                        </button>
                    </div>
                    <div class="card-body">
                        <?php if (isset($_GET['sucesso'])): ?>
                            <div class="alert alert-success alert-dismissible fade show">
                                <i class="fas fa-check-circle"></i>
                                <?php
                                switch ($_GET['sucesso']) {
                                    case 'gerada':
                                        echo 'Licença gerada com sucesso!';
                                        break;
                                    case 'suspensa':
                                        echo 'Licença suspensa com sucesso!';
                                        break;
                                    case 'reativada':
                                        echo 'Licença reativada com sucesso!';
                                        break;
                                }
                                ?>
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        <?php endif; ?>

                        <!-- Filtros -->
                        <div class="mb-4">
                            <form method="GET" class="form-inline">
                                <select name="status" class="form-control mr-2">
                                    <option value="">Todos os Status</option>
                                    <option value="trial" <?php echo $filtro_status === 'trial' ? 'selected' : ''; ?>>Trial</option>
                                    <option value="ativa" <?php echo $filtro_status === 'ativa' ? 'selected' : ''; ?>>Ativa</option>
                                    <option value="suspensa" <?php echo $filtro_status === 'suspensa' ? 'selected' : ''; ?>>Suspensa</option>
                                    <option value="expirada" <?php echo $filtro_status === 'expirada' ? 'selected' : ''; ?>>Expirada</option>
                                </select>
                                <select name="produto" class="form-control mr-2">
                                    <option value="">Todos os Produtos</option>
                                    <?php $produtos->data_seek(0); while ($prod = $produtos->fetch_assoc()): ?>
                                        <option value="<?php echo $prod['id']; ?>" <?php echo $filtro_produto == $prod['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($prod['nome']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Filtrar
                                </button>
                            </form>
                        </div>

                        <!-- Lista de Licenças -->
                        <div>
                            <h5>Total: <?php echo count($licencas); ?> licenças</h5>
                            <?php foreach ($licencas as $lic): ?>
                                <div class="licenca-row status-<?php echo strtolower($lic['status']); ?>">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5>
                                                <i class="fas fa-cube"></i> <?php echo htmlspecialchars($lic['produto_nome']); ?>
                                            </h5>
                                            <p class="mb-2">
                                                <strong>Cliente:</strong> <?php echo htmlspecialchars($lic['cliente_nome']); ?><br>
                                                <small class="text-muted">
                                                    <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($lic['cliente_email']); ?><br>
                                                    <i class="fas fa-phone"></i> <?php echo htmlspecialchars($lic['cliente_telefone'] ?? 'N/A'); ?>
                                                </small>
                                            </p>
                                            <div class="api-key-box" onclick="copiarApiKey(this)" title="Clique para copiar">
                                                <i class="fas fa-key"></i> API Key: <?php echo htmlspecialchars($lic['api_key']); ?>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <p>
                                                <strong>Tipo:</strong> <?php echo ucfirst($lic['tipo_licenca']); ?><br>
                                                <strong>Status:</strong>
                                                <span class="badge badge-<?php
                                                    echo match($lic['status']) {
                                                        'trial' => 'info',
                                                        'ativa' => 'success',
                                                        'suspensa' => 'danger',
                                                        'expirada' => 'secondary',
                                                        default => 'warning'
                                                    };
                                                ?>">
                                                    <?php echo ucfirst($lic['status']); ?>
                                                </span><br>
                                                <strong>Valor:</strong> R$ <?php echo number_format($lic['valor_licenca'], 2, ',', '.'); ?><br>
                                                <?php if ($lic['data_proxima_cobranca']): ?>
                                                    <strong>Próx. Cobrança:</strong> <?php echo date('d/m/Y', strtotime($lic['data_proxima_cobranca'])); ?><br>
                                                    <strong>Dias restantes:</strong>
                                                    <?php
                                                    $dias = $lic['dias_para_vencimento'];
                                                    if ($dias < 0) {
                                                        echo '<span class="text-danger">' . abs($dias) . ' dias atraso</span>';
                                                    } else {
                                                        echo $dias . ' dias';
                                                    }
                                                    ?>
                                                <?php endif; ?>
                                            </p>
                                        </div>
                                        <div class="col-md-3 text-right">
                                            <p>
                                                <strong>Instalações:</strong> <?php echo $lic['total_instalacoes']; ?> / <?php echo $lic['max_instalacoes']; ?><br>
                                                <?php if ($lic['vendedor_nome']): ?>
                                                    <small class="text-muted">
                                                        <i class="fas fa-user"></i> Vendedor: <?php echo htmlspecialchars($lic['vendedor_nome']); ?>
                                                    </small>
                                                <?php endif; ?>
                                            </p>
                                            <div class="btn-group-vertical btn-block">
                                                <a href="visualizar_licenca.php?id=<?php echo $lic['id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> Ver Detalhes
                                                </a>
                                                <?php if ($lic['status'] === 'ativa' || $lic['status'] === 'trial'): ?>
                                                    <a href="processar_licenca.php?acao=suspender&id=<?php echo $lic['id']; ?>"
                                                       class="btn btn-sm btn-warning"
                                                       onclick="return confirm('Suspender esta licença?')">
                                                        <i class="fas fa-ban"></i> Suspender
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($lic['status'] === 'suspensa'): ?>
                                                    <a href="processar_licenca.php?acao=reativar&id=<?php echo $lic['id']; ?>"
                                                       class="btn btn-sm btn-success">
                                                        <i class="fas fa-check"></i> Reativar
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>

                            <?php if (empty($licencas)): ?>
                                <div class="alert alert-info text-center">
                                    <i class="fas fa-info-circle"></i> Nenhuma licença encontrada.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nova Licença -->
    <div class="modal fade" id="modalNovaLicenca" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Gerar Nova Licença</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <form action="processar_licenca.php" method="POST">
                    <input type="hidden" name="acao" value="gerar">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Cliente *</label>
                            <select name="cliente_id" class="form-control" required>
                                <option value="">Selecione o cliente</option>
                                <?php $clientes->data_seek(0); while ($cli = $clientes->fetch_assoc()): ?>
                                    <option value="<?php echo $cli['id']; ?>">
                                        <?php echo htmlspecialchars($cli['nome']); ?> (<?php echo htmlspecialchars($cli['email']); ?>)
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Produto *</label>
                            <select name="produto_id" class="form-control" required id="select_produto" onchange="atualizarPreco()">
                                <option value="">Selecione o produto</option>
                                <?php $produtos->data_seek(0); while ($prod = $produtos->fetch_assoc()): ?>
                                    <option value="<?php echo $prod['id']; ?>"
                                            data-mensal="<?php echo $prod['preco_mensal']; ?>"
                                            data-anual="<?php echo $prod['preco_anual']; ?>"
                                            data-vitalicia="<?php echo $prod['preco_vitalicio']; ?>">
                                        <?php echo htmlspecialchars($prod['nome']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Tipo de Licença *</label>
                            <select name="tipo_licenca" class="form-control" required id="select_tipo" onchange="atualizarPreco()">
                                <option value="mensal">Mensal</option>
                                <option value="anual">Anual</option>
                                <option value="vitalicia">Vitalícia</option>
                            </select>
                        </div>
                        <div class="alert alert-info">
                            <strong>Valor da Licença:</strong> R$ <span id="valor_licenca">0,00</span>
                        </div>
                        <div class="form-group">
                            <label>Observações</label>
                            <textarea name="observacoes" class="form-control" rows="3" placeholder="Informações adicionais sobre a venda..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key"></i> Gerar Licença
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function copiarApiKey(elemento) {
            const apiKey = elemento.textContent.replace('API Key: ', '').trim();
            navigator.clipboard.writeText(apiKey).then(() => {
                const original = elemento.innerHTML;
                elemento.innerHTML = '<i class="fas fa-check"></i> API Key copiada!';
                elemento.style.background = '#27ae60';
                setTimeout(() => {
                    elemento.innerHTML = original;
                    elemento.style.background = '#2c3e50';
                }, 2000);
            });
        }

        function atualizarPreco() {
            const produtoSelect = document.getElementById('select_produto');
            const tipoSelect = document.getElementById('select_tipo');
            const valorSpan = document.getElementById('valor_licenca');

            if (!produtoSelect.value) {
                valorSpan.textContent = '0,00';
                return;
            }

            const option = produtoSelect.options[produtoSelect.selectedIndex];
            const tipo = tipoSelect.value;

            let valor = 0;
            if (tipo === 'mensal') valor = parseFloat(option.dataset.mensal);
            else if (tipo === 'anual') valor = parseFloat(option.dataset.anual);
            else if (tipo === 'vitalicia') valor = parseFloat(option.dataset.vitalicia);

            valorSpan.textContent = valor.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
