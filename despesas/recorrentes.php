<?php
session_start();
require_once 'classes/Despesa.php';

$despesa = new Despesa();

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['acao'])) {
            switch ($_POST['acao']) {
                case 'gerar_proximo_mes':
                    $criadas = $despesa->gerarRecorrentes();
                    if ($criadas > 0) {
                        $_SESSION['mensagem'] = "✓ {$criadas} despesa(s) recorrente(s) gerada(s) para o próximo mês!";
                        $_SESSION['tipo_mensagem'] = 'success';
                    } else {
                        $_SESSION['mensagem'] = 'ℹ️ Nenhuma despesa foi gerada. Elas já podem existir para o próximo mês.';
                        $_SESSION['tipo_mensagem'] = 'info';
                    }
                    break;
                    
                case 'remover_recorrencia':
                    $despesa->removerRecorrencia($_POST['id']);
                    $_SESSION['mensagem'] = 'Recorrência removida com sucesso!';
                    $_SESSION['tipo_mensagem'] = 'success';
                    break;
            }
        }
        header('Location: recorrentes.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['mensagem'] = 'Erro: ' . $e->getMessage();
        $_SESSION['tipo_mensagem'] = 'error';
    }
}

// Buscar despesas recorrentes
$recorrentes = $despesa->listarRecorrentes();

$titulo = 'Despesas Recorrentes';
require_once 'includes/header.php';
?>

<div class="container fade-in">
    <header>
        <h1>🔁 Despesas Recorrentes</h1>
        <div class="btn-group">
            <a href="despesas.php" class="btn btn-primary">← Voltar</a>
            <form method="POST" style="display: inline;">
                <input type="hidden" name="acao" value="gerar_proximo_mes">
                <button type="submit" class="btn btn-success">
                    ⚡ Gerar Próximo Mês
                </button>
            </form>
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
    
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 25px; border-radius: 10px; margin-bottom: 30px;">
        <h2 style="color: white; margin-bottom: 15px;">ℹ️ Como Funciona</h2>
        <p style="margin: 0;">
            As despesas marcadas como <strong>recorrentes</strong> são automaticamente criadas todo mês no dia de vencimento especificado. 
            Você pode gerá-las manualmente clicando em "Gerar Próximo Mês" ou deixar o sistema fazer isso automaticamente no dia 1º de cada mês.
        </p>
        <p style="margin: 10px 0 0 0;">
            <strong>Total de despesas recorrentes:</strong> <?php echo count($recorrentes); ?>
        </p>
    </div>
    
    <?php if (count($recorrentes) > 0): ?>
    
    <!-- Resumo por Categoria -->
    <?php
    $totalMensal = 0;
    $porCategoria = [];
    
    foreach ($recorrentes as $r) {
        $totalMensal += $r['valor'];
        $cat = $r['categoria'] ?? 'Sem categoria';
        if (!isset($porCategoria[$cat])) {
            $porCategoria[$cat] = 0;
        }
        $porCategoria[$cat] += $r['valor'];
    }
    ?>
    
    <div class="stats-grid">
        <div class="stat-card">
            <h3>💰 Custo Mensal Total</h3>
            <div class="value">R$ <?php echo number_format($totalMensal, 2, ',', '.'); ?></div>
            <div class="subtitle"><?php echo count($recorrentes); ?> despesas fixas</div>
        </div>
        
        <?php 
        arsort($porCategoria);
        $topCategorias = array_slice($porCategoria, 0, 3, true);
        $count = 0;
        foreach ($topCategorias as $cat => $valor): 
            $count++;
            $color = $count == 1 ? 'success' : ($count == 2 ? 'warning' : '');
        ?>
        <div class="stat-card <?php echo $color; ?>">
            <h3>📁 <?php echo htmlspecialchars($cat); ?></h3>
            <div class="value">R$ <?php echo number_format($valor, 2, ',', '.'); ?></div>
            <div class="subtitle">
                <?php 
                $percentual = ($valor / $totalMensal) * 100;
                echo number_format($percentual, 1) . '% do total'; 
                ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Tabela de Recorrentes -->
    <h2 style="color: var(--secondary-color); margin: 30px 0 20px;">📋 Suas Despesas Recorrentes</h2>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Conta</th>
                    <th>Dia Venc.</th>
                    <th>Valor</th>
                    <th>Pagamento</th>
                    <th>Categoria</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recorrentes as $r): ?>
                <tr>
                    <td>
                        <strong><?php echo htmlspecialchars($r['nome_conta']); ?></strong>
                        <?php if ($r['descricao']): ?>
                            <br><small style="color: #7f8c8d;"><?php echo htmlspecialchars($r['descricao']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span style="background: #3498db; color: white; padding: 5px 10px; border-radius: 5px; font-weight: bold;">
                            Dia <?php echo $r['dia_vencimento_recorrente']; ?>
                        </span>
                    </td>
                    <td><strong>R$ <?php echo number_format($r['valor'], 2, ',', '.'); ?></strong></td>
                    <td>
                        <?php echo htmlspecialchars($r['modo_pagamento']); ?>
                        <?php if ($r['debito_automatico']): ?>
                            <br><small style="color: #27ae60;">🔄 Déb. Auto</small>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($r['categoria'] ?? '-'); ?></td>
                    <td>
                        <div class="table-actions">
                            <a href="editar.php?id=<?php echo $r['id']; ?>" 
                               class="btn btn-primary btn-small"
                               title="Editar">
                                ✏️
                            </a>
                            
                            <button class="btn btn-warning btn-small" 
                                    onclick="removerRecorrencia(<?php echo $r['id']; ?>)"
                                    title="Remover recorrência (mantém despesa atual)">
                                🔕
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr style="background: var(--light-gray); font-weight: bold;">
                    <td colspan="2">TOTAL MENSAL</td>
                    <td>R$ <?php echo number_format($totalMensal, 2, ',', '.'); ?></td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
    </div>
    
    <!-- Dicas -->
    <div style="background: #fff3cd; padding: 20px; border-radius: 10px; margin-top: 30px; border-left: 4px solid #f39c12;">
        <h3 style="color: #856404; margin-bottom: 10px;">💡 Dicas Úteis</h3>
        <ul style="color: #856404; list-style: none; padding: 0;">
            <li>✓ Para adicionar nova recorrente, crie uma despesa e marque "Despesa Recorrente"</li>
            <li>✓ O botão 🔕 remove apenas a recorrência, mantendo a despesa atual</li>
            <li>✓ Para deletar completamente, use o botão 🗑️ na tela principal</li>
            <li>✓ O sistema gera automaticamente no dia 1º de cada mês (se configurado)</li>
        </ul>
    </div>
    
    <?php else: ?>
    
    <div class="alert alert-info">
        <strong>ℹ️ Nenhuma despesa recorrente cadastrada</strong>
        <p style="margin-top: 10px;">
            Para criar despesas recorrentes, adicione uma nova despesa e marque a opção 
            <strong>"🔁 Despesa Recorrente"</strong>.
        </p>
        <a href="adicionar.php" class="btn btn-primary" style="margin-top: 15px;">
            + Adicionar Despesa Recorrente
        </a>
    </div>
    
    <?php endif; ?>
</div>

<script>
function removerRecorrencia(id) {
    if (confirm('Deseja remover a recorrência desta despesa?\n\nA despesa atual será mantida, mas não será mais gerada automaticamente nos próximos meses.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'recorrentes.php';
        
        const inputAcao = document.createElement('input');
        inputAcao.type = 'hidden';
        inputAcao.name = 'acao';
        inputAcao.value = 'remover_recorrencia';
        
        const inputId = document.createElement('input');
        inputId.type = 'hidden';
        inputId.name = 'id';
        inputId.value = id;
        
        form.appendChild(inputAcao);
        form.appendChild(inputId);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
