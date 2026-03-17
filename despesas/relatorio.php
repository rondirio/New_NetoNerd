<?php
session_start();
require_once 'classes/Despesa.php';

$despesa = new Despesa();

// Filtros
$mesAtual = $_GET['mes'] ?? date('m');
$anoAtual = $_GET['ano'] ?? date('Y');

// Buscar dados
$despesas = $despesa->listar(['mes' => $mesAtual, 'ano' => $anoAtual]);
$estatisticas = $despesa->estatisticasMes($mesAtual, $anoAtual);

$meses = [
    '01' => 'Janeiro', '02' => 'Fevereiro', '03' => 'Março',
    '04' => 'Abril', '05' => 'Maio', '06' => 'Junho',
    '07' => 'Julho', '08' => 'Agosto', '09' => 'Setembro',
    '10' => 'Outubro', '11' => 'Novembro', '12' => 'Dezembro'
];

$periodo = $meses[$mesAtual] . '/' . $anoAtual;

$titulo = 'Relatório de Despesas';
require_once 'includes/header.php';
?>

<style>
    @media print {
        .no-print {
            display: none !important;
        }
        
        body {
            background: white;
            padding: 0;
        }
        
        .container {
            box-shadow: none;
            padding: 20px;
        }
        
        .btn {
            display: none;
        }
    }
</style>

<div class="container fade-in">
    <header class="no-print">
        <h1>📊 Relatório de Despesas</h1>
        <div class="btn-group">
            <a href="despesas.php" class="btn btn-primary">← Voltar</a>
            <button onclick="window.print()" class="btn btn-success">🖨️ Imprimir</button>
            <button onclick="document.getElementById('modalEmail').classList.add('active')" 
                    class="btn btn-warning">
                📧 Enviar por Email
            </button>
        </div>
    </header>
    
    <?php if (isset($_SESSION['mensagem'])): ?>
        <div class="alert alert-<?php echo $_SESSION['tipo_mensagem']; ?> no-print">
            <?php 
            echo $_SESSION['mensagem']; 
            unset($_SESSION['mensagem']);
            unset($_SESSION['tipo_mensagem']);
            ?>
        </div>
    <?php endif; ?>
    
    <!-- Filtro de Período -->
    <form method="GET" class="filters no-print">
        <div class="filter-group">
            <label>Mês</label>
            <select name="mes" onchange="this.form.submit()">
                <?php foreach ($meses as $num => $nome): ?>
                    <option value="<?php echo $num; ?>" <?php echo $mesAtual == $num ? 'selected' : ''; ?>>
                        <?php echo $nome; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Ano</label>
            <select name="ano" onchange="this.form.submit()">
                <?php for ($i = date('Y') - 2; $i <= date('Y') + 1; $i++): ?>
                    <option value="<?php echo $i; ?>" <?php echo $anoAtual == $i ? 'selected' : ''; ?>>
                        <?php echo $i; ?>
                    </option>
                <?php endfor; ?>
            </select>
        </div>
    </form>
    
    <!-- Cabeçalho do Relatório -->
    <div style="text-align: center; margin: 30px 0;">
        <h2 style="color: var(--secondary-color);">Relatório Financeiro</h2>
        <h3 style="color: var(--dark-gray);"><?php echo $periodo; ?></h3>
        <p style="color: var(--dark-gray);">Gerado em <?php echo date('d/m/Y \à\s H:i'); ?></p>
    </div>
    
    <!-- Resumo Executivo -->
    <div style="background: var(--light-gray); padding: 30px; border-radius: 10px; margin: 30px 0;">
        <h3 style="color: var(--secondary-color); margin-bottom: 20px;">📈 Resumo Executivo</h3>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div>
                <p style="color: var(--dark-gray); margin-bottom: 5px;">Total de Contas</p>
                <p style="font-size: 2em; font-weight: bold; color: var(--secondary-color);">
                    <?php echo $estatisticas['total_contas']; ?>
                </p>
            </div>
            
            <div>
                <p style="color: var(--dark-gray); margin-bottom: 5px;">Contas Pagas</p>
                <p style="font-size: 2em; font-weight: bold; color: var(--success-color);">
                    <?php echo $estatisticas['pagas']; ?>
                </p>
            </div>
            
            <div>
                <p style="color: var(--dark-gray); margin-bottom: 5px;">Contas Pendentes</p>
                <p style="font-size: 2em; font-weight: bold; color: var(--warning-color);">
                    <?php echo $estatisticas['pendentes']; ?>
                </p>
            </div>
            
            <div>
                <p style="color: var(--dark-gray); margin-bottom: 5px;">Contas Vencidas</p>
                <p style="font-size: 2em; font-weight: bold; color: var(--danger-color);">
                    <?php echo $estatisticas['vencidas']; ?>
                </p>
            </div>
        </div>
        
        <hr style="margin: 30px 0; border: none; border-top: 2px solid #ccc;">
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
            <div>
                <p style="color: var(--dark-gray); margin-bottom: 5px;">✓ Valor Pago</p>
                <p style="font-size: 1.8em; font-weight: bold; color: var(--success-color);">
                    R$ <?php echo number_format($estatisticas['valor_pago'], 2, ',', '.'); ?>
                </p>
                <p style="color: var(--dark-gray); font-size: 0.9em;">
                    <?php 
                    $percentualPago = $estatisticas['valor_total'] > 0 
                        ? ($estatisticas['valor_pago'] / $estatisticas['valor_total']) * 100 
                        : 0;
                    echo number_format($percentualPago, 1); 
                    ?>% do total
                </p>
            </div>
            
            <div>
                <p style="color: var(--dark-gray); margin-bottom: 5px;">⏳ Valor Pendente</p>
                <p style="font-size: 1.8em; font-weight: bold; color: var(--danger-color);">
                    R$ <?php echo number_format($estatisticas['valor_pendente'], 2, ',', '.'); ?>
                </p>
                <p style="color: var(--dark-gray); font-size: 0.9em;">
                    <?php 
                    $percentualPendente = $estatisticas['valor_total'] > 0 
                        ? ($estatisticas['valor_pendente'] / $estatisticas['valor_total']) * 100 
                        : 0;
                    echo number_format($percentualPendente, 1); 
                    ?>% do total
                </p>
            </div>
            
            <div>
                <p style="color: var(--dark-gray); margin-bottom: 5px;">💰 Valor Total</p>
                <p style="font-size: 1.8em; font-weight: bold; color: var(--secondary-color);">
                    R$ <?php echo number_format($estatisticas['valor_total'], 2, ',', '.'); ?>
                </p>
                <p style="color: var(--dark-gray); font-size: 0.9em;">
                    Despesas do período
                </p>
            </div>
        </div>
    </div>
    
    <!-- Detalhamento -->
    <h3 style="color: var(--secondary-color); margin: 30px 0 20px;">📋 Detalhamento de Despesas</h3>
    
    <?php if (count($despesas) > 0): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Conta</th>
                    <th>Categoria</th>
                    <th>Vencimento</th>
                    <th>Valor</th>
                    <th>Pagamento</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($despesas as $d): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($d['nome_conta']); ?></strong>
                        <?php if ($d['descricao']): ?>
                            <br><small style="color: #7f8c8d;"><?php echo htmlspecialchars($d['descricao']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($d['categoria'] ?? '-'); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($d['data_vencimento'])); ?></td>
                    <td><strong>R$ <?php echo number_format($d['valor'], 2, ',', '.'); ?></strong></td>
                    <td>
                        <?php echo htmlspecialchars($d['modo_pagamento']); ?>
                        <?php if ($d['debito_automatico']): ?>
                            <br><small>🔄 Déb. Auto</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        $badgeClass = 'badge-' . strtolower($d['status']);
                        echo "<span class='badge {$badgeClass}'>{$d['status']}</span>";
                        ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div class="alert alert-info">
        Nenhuma despesa registrada para este período.
    </div>
    <?php endif; ?>
    
    <!-- Rodapé do Relatório -->
    <div style="margin-top: 50px; padding-top: 20px; border-top: 2px solid var(--light-gray); text-align: center; color: var(--dark-gray);">
        <p>Relatório gerado automaticamente pelo Sistema de Gerenciamento de Despesas</p>
        <p><?php echo date('d/m/Y \à\s H:i'); ?></p>
    </div>
</div>

<!-- Modal de Envio por Email -->
<div id="modalEmail" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>📧 Enviar Relatório por Email</h2>
            <button class="modal-close" onclick="document.getElementById('modalEmail').classList.remove('active')">×</button>
        </div>
        
        <form method="POST" action="enviar_relatorio.php">
            <input type="hidden" name="mes" value="<?php echo $mesAtual; ?>">
            <input type="hidden" name="ano" value="<?php echo $anoAtual; ?>">
            
            <div class="form-group">
                <label for="email">Email do Destinatário *</label>
                <input type="email" 
                       id="email" 
                       name="email" 
                       required 
                       placeholder="seu@email.com">
            </div>
            
            <div class="form-group">
                <label for="nome">Nome do Destinatário</label>
                <input type="text" 
                       id="nome" 
                       name="nome" 
                       placeholder="Nome (opcional)">
            </div>
            
            <div class="alert alert-info">
                O relatório será enviado com o resumo financeiro e todas as despesas do período selecionado.
            </div>
            
            <div class="btn-group" style="margin-top: 20px;">
                <button type="submit" class="btn btn-success">
                    ✓ Enviar Email
                </button>
                <button type="button" 
                        class="btn btn-danger" 
                        onclick="document.getElementById('modalEmail').classList.remove('active')">
                    ✕ Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
