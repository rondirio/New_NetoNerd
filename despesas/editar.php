<?php
session_start();
require_once 'classes/Auth.php';
require_once 'classes/Despesa.php';

// Proteger página
$auth = new Auth();
$auth->protegerPagina();
$usuarioId = $auth->getUsuarioId();

$despesaObj = new Despesa();

// Buscar despesa
if (!isset($_GET['id'])) {
    header('Location: despesas.php');
    exit;
}

$d = $despesaObj->buscarPorId($_GET['id'], $usuarioId);

if (!$d) {
    $_SESSION['mensagem'] = 'Despesa não encontrada ou você não tem permissão para editá-la.';
    $_SESSION['tipo_mensagem'] = 'error';
    header('Location: despesas.php');
    exit;
}

// Processar atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Extrair dia do vencimento
        $dataVencimento = $_POST['data_vencimento'];
        $diaVencimento = (int) date('d', strtotime($dataVencimento));
        
        $dados = [
            'nome_conta' => $_POST['nome_conta'],
            'descricao' => $_POST['descricao'],
            'valor' => str_replace(['.', ','], ['', '.'], $_POST['valor']),
            'data_vencimento' => $dataVencimento,
            'modo_pagamento' => $_POST['modo_pagamento'],
            'debito_automatico' => isset($_POST['debito_automatico']) ? 1 : 0,
            'recorrente' => isset($_POST['recorrente']) ? 1 : 0,
            'dia_vencimento_recorrente' => isset($_POST['recorrente']) ? $diaVencimento : null,
            'categoria' => $_POST['categoria'],
            'observacoes' => $_POST['observacoes'],
            'status' => $_POST['status']
        ];
        
        $despesaObj->atualizar($_GET['id'], $dados);
        
        $_SESSION['mensagem'] = 'Despesa atualizada com sucesso!';
        $_SESSION['tipo_mensagem'] = 'success';
        
        header('Location: despesas.php');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['mensagem'] = 'Erro ao atualizar despesa: ' . $e->getMessage();
        $_SESSION['tipo_mensagem'] = 'error';
    }
}

$titulo = 'Editar Despesa';
require_once 'includes/header.php';
?>

<div class="container fade-in">
    <header>
        <h1>✏️ Editar Despesa</h1>
        <a href="despesas.php" class="btn btn-primary">← Voltar</a>
    </header>
    
    <?php if (isset($_SESSION['mensagem'])): ?>
        <div class="alert alert-<?php echo $_SESSION['tipo_mensagem']; ?>">
            <?php 
            echo $_SESSION['mensagem']; 
            unset($_SESSION['mensagem']);
            unset($_SESSION['tipo_mensagem']);
            ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" id="formDespesa">
        <div class="form-row">
            <div class="form-group">
                <label for="nome_conta">Nome da Conta *</label>
                <input type="text" 
                       id="nome_conta" 
                       name="nome_conta" 
                       required 
                       value="<?php echo htmlspecialchars($d['nome_conta']); ?>">
            </div>
            
            <div class="form-group">
                <label for="categoria">Categoria</label>
                <input type="text" 
                       id="categoria" 
                       name="categoria" 
                       list="categorias"
                       value="<?php echo htmlspecialchars($d['categoria'] ?? ''); ?>">
                <datalist id="categorias">
                    <option value="Utilidades">
                    <option value="Alimentação">
                    <option value="Transporte">
                    <option value="Saúde">
                    <option value="Educação">
                    <option value="Lazer">
                    <option value="Moradia">
                    <option value="Outros">
                </datalist>
            </div>
        </div>
        
        <div class="form-group">
            <label for="descricao">Descrição</label>
            <textarea id="descricao" 
                      name="descricao" 
                      rows="3"><?php echo htmlspecialchars($d['descricao'] ?? ''); ?></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="valor">Valor *</label>
                <input type="text" 
                       id="valor" 
                       name="valor" 
                       required 
                       data-tipo="moeda"
                       value="<?php echo number_format($d['valor'], 2, ',', '.'); ?>">
            </div>
            
            <div class="form-group">
                <label for="data_vencimento">Data de Vencimento *</label>
                <input type="date" 
                       id="data_vencimento" 
                       name="data_vencimento" 
                       required
                       value="<?php echo $d['data_vencimento']; ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="modo_pagamento">Modo de Pagamento *</label>
                <select id="modo_pagamento" name="modo_pagamento" required>
                    <option value="Dinheiro" <?php echo $d['modo_pagamento'] == 'Dinheiro' ? 'selected' : ''; ?>>Dinheiro</option>
                    <option value="Cartão Crédito" <?php echo $d['modo_pagamento'] == 'Cartão Crédito' ? 'selected' : ''; ?>>Cartão de Crédito</option>
                    <option value="Cartão Débito" <?php echo $d['modo_pagamento'] == 'Cartão Débito' ? 'selected' : ''; ?>>Cartão de Débito</option>
                    <option value="PIX" <?php echo $d['modo_pagamento'] == 'PIX' ? 'selected' : ''; ?>>PIX</option>
                    <option value="Boleto" <?php echo $d['modo_pagamento'] == 'Boleto' ? 'selected' : ''; ?>>Boleto</option>
                    <option value="Transferência" <?php echo $d['modo_pagamento'] == 'Transferência' ? 'selected' : ''; ?>>Transferência</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="status">Status *</label>
                <select id="status" name="status" required>
                    <option value="Pendente" <?php echo $d['status'] == 'Pendente' ? 'selected' : ''; ?>>Pendente</option>
                    <option value="Pago" <?php echo $d['status'] == 'Pago' ? 'selected' : ''; ?>>Pago</option>
                    <option value="Vencido" <?php echo $d['status'] == 'Vencido' ? 'selected' : ''; ?>>Vencido</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <div class="checkbox-group">
                <input type="checkbox" 
                       id="debito_automatico" 
                       name="debito_automatico"
                       <?php echo $d['debito_automatico'] ? 'checked' : ''; ?>>
                <label for="debito_automatico" style="font-weight: normal;">
                    🔄 Débito Automático
                </label>
            </div>
            <div class="checkbox-group" style="margin-top: 10px;">
                <input type="checkbox" 
                       id="recorrente" 
                       name="recorrente"
                       <?php echo $d['recorrente'] ? 'checked' : ''; ?>>
                <label for="recorrente" style="font-weight: normal;">
                    🔁 Despesa Recorrente (repetir todo mês)
                </label>
            </div>
        </div>
        
        <div class="form-group">
            <label for="observacoes">Observações</label>
            <textarea id="observacoes" 
                      name="observacoes" 
                      rows="2"><?php echo htmlspecialchars($d['observacoes'] ?? ''); ?></textarea>
        </div>
        
        <div class="btn-group" style="margin-top: 30px;">
            <button type="submit" class="btn btn-success">
                ✓ Atualizar Despesa
            </button>
            <a href="despesas.php" class="btn btn-danger">
                ✕ Cancelar
            </a>
        </div>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
