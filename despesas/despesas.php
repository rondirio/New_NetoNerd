<?php
session_start();
require_once 'classes/Auth.php';
require_once 'classes/Despesa.php';

// Proteger página e obter usuário
$auth = new Auth();
$auth->protegerPagina();
$usuarioId = $auth->getUsuarioId();
$usuario = $auth->getUsuario();

$despesa = new Despesa();
$despesa->criarTabela();

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['acao'])) {
            switch ($_POST['acao']) {
                case 'marcar_pago':
                    $despesa->marcarComoPago($_POST['id'], $usuarioId);
                    $_SESSION['mensagem'] = 'Despesa marcada como paga com sucesso!';
                    $_SESSION['tipo_mensagem'] = 'success';
                    break;
                    
                case 'deletar':
                    $despesa->deletar($_POST['id'], $usuarioId);
                    $_SESSION['mensagem'] = 'Despesa deletada com sucesso!';
                    $_SESSION['tipo_mensagem'] = 'success';
                    break;
                    
                case 'gerar_recorrentes':
                    $criadas = $despesa->gerarRecorrentes();
                    if ($criadas > 0) {
                        $_SESSION['mensagem'] = "{$criadas} despesa(s) recorrente(s) gerada(s) para o próximo mês!";
                        $_SESSION['tipo_mensagem'] = 'success';
                    } else {
                        $_SESSION['mensagem'] = 'Nenhuma despesa recorrente foi gerada (podem já existir para o próximo mês).';
                        $_SESSION['tipo_mensagem'] = 'info';
                    }
                    break;
            }
        }
        header('Location: despesas.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['mensagem'] = 'Erro: ' . $e->getMessage();
        $_SESSION['tipo_mensagem'] = 'error';
    }
}

// Filtros
$filtros = ['usuario_id' => $usuarioId];
$mesAtual = date('m');
$anoAtual = date('Y');

if (isset($_GET['mes']) && $_GET['mes'] !== '') {
    $filtros['mes'] = $_GET['mes'];
    $mesAtual = $_GET['mes'];
}

if (isset($_GET['ano']) && $_GET['ano'] !== '') {
    $filtros['ano'] = $_GET['ano'];
    $anoAtual = $_GET['ano'];
}

if (isset($_GET['status']) && $_GET['status'] !== '') {
    $filtros['status'] = $_GET['status'];
}

if (isset($_GET['categoria']) && $_GET['categoria'] !== '') {
    $filtros['categoria'] = $_GET['categoria'];
}

// Se não houver filtros de mês/ano, usar mês atual
if (!isset($filtros['mes'])) {
    $filtros['mes'] = $mesAtual;
}
if (!isset($filtros['ano'])) {
    $filtros['ano'] = $anoAtual;
}

// Buscar dados
$despesas = $despesa->listar($filtros);
$estatisticas = $despesa->estatisticasMes($filtros['mes'], $filtros['ano'], $usuarioId);
$categorias = $despesa->obterCategorias();
$totalRecorrentes = $despesa->contarRecorrentes();

$titulo = 'Dashboard - Despesas';
require_once 'includes/header.php';
?>

<div class="container fade-in">
    <header>
        <h1>💰 Gerenciador de Despesas</h1>
        <div class="btn-group">
            <a href="adicionar.php" class="btn btn-primary">+ Nova Despesa</a>
            <a href="parcelamentos.php" class="btn btn-warning">
                💳 Parceladas
            </a>
            <a href="recorrentes.php" class="btn btn-warning">
                🔁 Recorrentes (<?php echo $totalRecorrentes; ?>)
            </a>
            <a href="relatorio.php" class="btn btn-success">📊 Relatório</a>
        </div>
    </header>
    
    <?php if (isset($_SESSION['mensagem'])): ?>
        <div class="alert alert-<?php echo $_SESSION['tipo_mensagem']; ?> fade-in">
            <?php 
            echo $_SESSION['mensagem']; 
            unset($_SESSION['mensagem']);
            unset($_SESSION['tipo_mensagem']);
            ?>
        </div>
    <?php endif; ?>
    
    <!-- Estatísticas -->
    <div class="stats-grid">
        <div class="stat-card">
            <h3>Total de Contas</h3>
            <div class="value"><?php echo $estatisticas['total_contas']; ?></div>
            <div class="subtitle">
                <?php echo $estatisticas['pagas']; ?> pagas | 
                <?php echo $estatisticas['pendentes']; ?> pendentes
            </div>
        </div>
        
        <div class="stat-card success">
            <h3>✓ Valor Pago</h3>
            <div class="value">R$ <?php echo number_format($estatisticas['valor_pago'], 2, ',', '.'); ?></div>
            <div class="subtitle">
                <?php 
                $percentualPago = $estatisticas['valor_total'] > 0 
                    ? ($estatisticas['valor_pago'] / $estatisticas['valor_total']) * 100 
                    : 0;
                echo number_format($percentualPago, 1); 
                ?>% do total
            </div>
        </div>
        
        <div class="stat-card danger">
            <h3>⏳ Valor Pendente</h3>
            <div class="value">R$ <?php echo number_format($estatisticas['valor_pendente'], 2, ',', '.'); ?></div>
            <div class="subtitle">
                <?php echo $estatisticas['pendentes'] + $estatisticas['vencidas']; ?> conta(s)
            </div>
        </div>
        
        <div class="stat-card warning">
            <h3>📅 Total do Mês</h3>
            <div class="value">R$ <?php echo number_format($estatisticas['valor_total'], 2, ',', '.'); ?></div>
            <div class="subtitle">
                <?php 
                $meses = [
                    '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
                    '04' => 'Abril', '05' => 'Maio', '06' => 'Junho',
                    '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro',
                    '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
                ];
                echo $meses[$filtros['mes']] . '/' . $filtros['ano'];
                ?>
            </div>
        </div>
    </div>
    
    <!-- Filtros -->
    <form method="GET" class="filters">
        <div class="filter-group">
            <label>Mês</label>
            <select name="mes" onchange="this.form.submit()">
                <?php foreach ($meses as $num => $nome): ?>
                    <option value="<?php echo $num; ?>" <?php echo $filtros['mes'] == $num ? 'selected' : ''; ?>>
                        <?php echo $nome; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Ano</label>
            <select name="ano" onchange="this.form.submit()">
                <?php for ($i = date('Y') - 2; $i <= date('Y') + 1; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php echo $filtros['ano'] == $i ? 'selected' : ''; ?>>
                        <?php echo $i; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Status</label>
            <select name="status" onchange="this.form.submit()">
                <option value="">Todos</option>
                <option value="Pendente" <?php echo (isset($filtros['status']) && $filtros['status'] == 'Pendente') ? 'selected' : ''; ?>>Pendente</option>
                <option value="Pago" <?php echo (isset($filtros['status']) && $filtros['status'] == 'Pago') ? 'selected' : ''; ?>>Pago</option>
                <option value="Vencido" <?php echo (isset($filtros['status']) && $filtros['status'] == 'Vencido') ? 'selected' : ''; ?>>Vencido</option>
            </select>
        </div>
        
        <?php if (count($categorias) > 0): ?>
        <div class="filter-group">
            <label>Categoria</label>
            <select name="categoria" onchange="this.form.submit()">
                <option value="">Todas</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat); ?>" 
                        <?php echo (isset($filtros['categoria']) && $filtros['categoria'] == $cat) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        
        <?php if (count($filtros) > 2): ?>
        <div class="filter-group">
            <label>&nbsp;</label>
            <a href="despesas.php" class="btn btn-warning">Limpar Filtros</a>
        </div>
        <?php endif; ?>
    </form>
    
    <!-- Tabela de Despesas -->
    <?php if (count($despesas) > 0): ?>
    <div class="table-container">
        <table id="tabelaDespesas">
            <thead>
                <tr>
                    <th>Conta</th>
                    <th>Vencimento</th>
                    <th>Valor</th>
                    <th>Pagamento</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($despesas as $d): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($d['nome_conta']); ?></strong>
                        <?php if ($d['recorrente']): ?>
                            <span style="background: #3498db; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.75em; margin-left: 5px;">
                                🔁 RECORRENTE
                            </span>
                        <?php endif; ?>
                        <?php if ($d['parcelado']): ?>
                            <span style="background: #9b59b6; color: white; padding: 2px 6px; border-radius: 4px; font-size: 0.75em; margin-left: 5px;">
                                💳 PARCELADA
                            </span>
                        <?php endif; ?>
                        <?php if ($d['categoria']): ?>
                            <br><small style="color: #7f8c8d;">📁 <?php echo htmlspecialchars($d['categoria']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($d['data_vencimento'])); ?></td>
                    <td><strong>R$ <?php echo number_format($d['valor'], 2, ',', '.'); ?></strong></td>
                    <td>
                        <?php echo htmlspecialchars($d['modo_pagamento']); ?>
                        <?php if ($d['debito_automatico']): ?>
                            <br><small style="color: #27ae60;">🔄 Déb. Automático</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $badgeClass = 'badge-' . strtolower($d['status']);
                        echo "<span class='badge {$badgeClass}'>{$d['status']}</span>";
                        ?>
                    </td>
                    <td>
                        <div class="table-actions">
                            <?php if ($d['status'] != 'Pago'): ?>
                            <button class="btn btn-success btn-small" 
                                    onclick="marcarPago(<?php echo $d['id']; ?>)">
                                ✓
                            </button>
                            <?php endif; ?>
                            
                            <a href="editar.php?id=<?php echo $d['id']; ?>" 
                               class="btn btn-primary btn-small">
                                ✏️
                            </a>
                            
                            <button class="btn btn-danger btn-small" 
                                    onclick="deletarDespesa(<?php echo $d['id']; ?>)">
                                🗑️
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="alert alert-info">
        ℹ️ Nenhuma despesa encontrada para este período.
    </div>
    <?php endif; ?>
    
    <!-- Seção de Boletos Pendentes -->
    <div style="margin-top: 40px;">
        <h2 style="color: var(--secondary-color); margin-bottom: 20px;">
            📄 Boletos Pendentes
        </h2>
        <div id="listaBoletos">
            <div class="loading">
                <div class="spinner"></div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
