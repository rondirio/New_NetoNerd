<?php
session_start();
require_once 'classes/Auth.php';
require_once 'classes/Despesa.php';

// Proteger página
$auth = new Auth();
$auth->protegerPagina();
$usuarioId = $auth->getUsuarioId();

$despesa = new Despesa();

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['acao'])) {
            switch ($_POST['acao']) {
                case 'deletar_parcelamento':
                    $despesa->deletarParcelamento($_POST['grupo'], $usuarioId);
                    $_SESSION['mensagem'] = 'Parcelamento deletado com sucesso!';
                    $_SESSION['tipo_mensagem'] = 'success';
                    break;
            }
        }
        header('Location: parcelamentos.php');
        exit;
    } catch (Exception $e) {
        $_SESSION['mensagem'] = 'Erro: ' . $e->getMessage();
        $_SESSION['tipo_mensagem'] = 'error';
    }
}

// Buscar parcelamentos
$grupos = $despesa->listarGruposParcelamento($usuarioId);

$titulo = 'Despesas Parceladas';
require_once 'includes/header.php';
?>

<div class="container fade-in">
    <header>
        <h1>💳 Despesas Parceladas</h1>
        <div class="btn-group">
            <a href="despesas.php" class="btn btn-primary">← Voltar</a>
            <a href="adicionar.php" class="btn btn-success">+ Nova Parcelada</a>
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
        <h2 style="color: white; margin-bottom: 15px;">ℹ️ Despesas Parceladas</h2>
        <p style="margin: 0;">
            Aqui você visualiza todas as compras parceladas. Cada parcelamento gera várias despesas mensais automaticamente até completar o número total de parcelas.
        </p>
        <p style="margin: 10px 0 0 0;">
            <strong>Exemplo:</strong> Celular em 12x de R$ 100 = 12 despesas de R$ 100 (uma por mês)
        </p>
    </div>
    
    <?php if (count($grupos) > 0): ?>
    
    <h2 style="color: var(--secondary-color); margin: 30px 0 20px;">📋 Seus Parcelamentos</h2>
    
    <div class="stats-grid">
        <?php foreach ($grupos as $grupo): 
            $resumo = $despesa->resumoParcelamento($grupo['grupo_parcelamento'], $usuarioId);
            $percentualPago = ($resumo['pagas'] / $resumo['total_parcelas']) * 100;
        ?>
        <div class="stat-card" style="background: white; color: #2c3e50; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
            <h3 style="color: #3498db; font-size: 1em;">
                <?php 
                // Remover " (X/Y)" do nome
                $nomeBase = preg_replace('/\s*\(\d+\/\d+\)$/', '', $grupo['nome_conta']);
                echo htmlspecialchars($nomeBase); 
                ?>
            </h3>
            <div class="value" style="color: #2c3e50; font-size: 1.5em;">
                <?php echo $resumo['pagas']; ?>/<?php echo $resumo['total_parcelas']; ?> pagas
            </div>
            <div class="subtitle" style="color: #7f8c8d;">
                R$ <?php echo number_format($resumo['valor_pago'], 2, ',', '.'); ?> de 
                R$ <?php echo number_format($resumo['valor_total'], 2, ',', '.'); ?>
            </div>
            
            <!-- Barra de progresso -->
            <div style="background: #ecf0f1; height: 8px; border-radius: 4px; margin: 15px 0; overflow: hidden;">
                <div style="background: #27ae60; height: 100%; width: <?php echo $percentualPago; ?>%;"></div>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 15px;">
                <a href="?ver=<?php echo $grupo['grupo_parcelamento']; ?>" 
                   class="btn btn-primary btn-small" style="flex: 1;">
                    Ver Parcelas
                </a>
                <button onclick="deletarParcelamento('<?php echo $grupo['grupo_parcelamento']; ?>')" 
                        class="btn btn-danger btn-small">
                    🗑️
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <!-- Detalhes do parcelamento selecionado -->
    <?php if (isset($_GET['ver'])): 
        $parcelas = $despesa->listarParcelamento($_GET['ver'], $usuarioId);
        if (count($parcelas) > 0):
    ?>
    <div style="margin-top: 40px;">
        <h2 style="color: var(--secondary-color); margin-bottom: 20px;">
            📝 Detalhes: <?php echo preg_replace('/\s*\(\d+\/\d+\)$/', '', htmlspecialchars($parcelas[0]['nome_conta'])); ?>
        </h2>
        
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Parcela</th>
                        <th>Vencimento</th>
                        <th>Valor</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($parcelas as $p): ?>
                    <tr>
                        <td>
                            <strong><?php echo $p['parcela_atual']; ?>/<?php echo $p['total_parcelas']; ?></strong>
                        </td>
                        <td><?php echo date('d/m/Y', strtotime($p['data_vencimento'])); ?></td>
                        <td><strong>R$ <?php echo number_format($p['valor'], 2, ',', '.'); ?></strong></td>
                        <td>
                            <?php
                            $badgeClass = 'badge-' . strtolower($p['status']);
                            echo "<span class='badge {$badgeClass}'>{$p['status']}</span>";
                            ?>
                        </td>
                        <td>
                            <div class="table-actions">
                                <?php if ($p['status'] != 'Pago'): ?>
                                <button class="btn btn-success btn-small" 
                                        onclick="marcarPago(<?php echo $p['id']; ?>)">
                                    ✓
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php 
        endif;
    endif; 
    ?>
    
    <?php else: ?>
    
    <div class="alert alert-info">
        <strong>ℹ️ Nenhum parcelamento encontrado</strong>
        <p style="margin-top: 10px;">
            Você ainda não tem despesas parceladas. Para criar uma, vá em "Adicionar Despesa" e marque a opção 
            <strong>"💳 Despesa Parcelada"</strong>.
        </p>
        <a href="adicionar.php" class="btn btn-primary" style="margin-top: 15px;">
            + Adicionar Despesa Parcelada
        </a>
    </div>
    
    <?php endif; ?>
</div>

<script>
function deletarParcelamento(grupo) {
    if (confirm('Tem certeza que deseja deletar TODAS as parcelas deste parcelamento?\n\nEsta ação não pode ser desfeita.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'parcelamentos.php';
        
        const inputAcao = document.createElement('input');
        inputAcao.type = 'hidden';
        inputAcao.name = 'acao';
        inputAcao.value = 'deletar_parcelamento';
        
        const inputGrupo = document.createElement('input');
        inputGrupo.type = 'hidden';
        inputGrupo.name = 'grupo';
        inputGrupo.value = grupo;
        
        form.appendChild(inputAcao);
        form.appendChild(inputGrupo);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
