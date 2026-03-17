<?php
session_start();
require_once 'classes/Auth.php';
require_once 'classes/Despesa.php';

// Proteger página
$auth = new Auth();
$auth->protegerPagina();
$usuarioId = $auth->getUsuarioId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $despesa = new Despesa();
        
        // Extrair dia do vencimento
        $dataVencimento = $_POST['data_vencimento'];
        $diaVencimento = (int) date('d', strtotime($dataVencimento));
        
        // Verificar se é parcelado
        $isParcelado = isset($_POST['parcelado']) && $_POST['parcelado'] == '1';
        $totalParcelas = isset($_POST['total_parcelas']) ? (int)$_POST['total_parcelas'] : 1;
        
        if ($isParcelado && $totalParcelas > 1) {
            // Criar despesas parceladas
            $dados = [
                'usuario_id' => $usuarioId,
                'nome_conta' => $_POST['nome_conta'],
                'descricao' => $_POST['descricao'],
                'valor_total' => str_replace(['.', ','], ['', '.'], $_POST['valor']),
                'data_vencimento' => $dataVencimento,
                'modo_pagamento' => $_POST['modo_pagamento'],
                'debito_automatico' => isset($_POST['debito_automatico']) ? 1 : 0,
                'categoria' => $_POST['categoria'],
                'observacoes' => $_POST['observacoes']
            ];
            
            $resultado = $despesa->criarParceladas($dados, $totalParcelas);
            
            $_SESSION['mensagem'] = "{$totalParcelas} parcelas criadas com sucesso! Valor por parcela: R$ " . number_format($resultado['valor_parcela'], 2, ',', '.');
            $_SESSION['tipo_mensagem'] = 'success';
        } else {
            // Despesa normal (não parcelada)
            $dados = [
                'usuario_id' => $usuarioId,
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
                'status' => 'Pendente'
            ];
            
            $despesa->adicionar($dados);
            
            $_SESSION['mensagem'] = 'Despesa adicionada com sucesso!';
            $_SESSION['tipo_mensagem'] = 'success';
        }
        
        header('Location: despesas.php');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['mensagem'] = 'Erro ao adicionar despesa: ' . $e->getMessage();
        $_SESSION['tipo_mensagem'] = 'error';
    }
}

$titulo = 'Adicionar Despesa';
require_once 'includes/header.php';
?>

<div class="container fade-in">
    <header>
        <h1>➕ Adicionar Nova Despesa</h1>
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
                       placeholder="Ex: Conta de Luz">
            </div>
            
            <div class="form-group">
                <label for="categoria">Categoria</label>
                <input type="text" 
                       id="categoria" 
                       name="categoria" 
                       list="categorias"
                       placeholder="Ex: Utilidades, Alimentação">
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
                      rows="3" 
                      placeholder="Detalhes sobre a despesa (opcional)"></textarea>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="valor">Valor *</label>
                <input type="text" 
                       id="valor" 
                       name="valor" 
                       required 
                       data-tipo="moeda"
                       placeholder="0,00">
            </div>
            
            <div class="form-group">
                <label for="data_vencimento">Data de Vencimento *</label>
                <input type="date" 
                       id="data_vencimento" 
                       name="data_vencimento" 
                       required
                       value="<?php echo date('Y-m-d'); ?>">
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label for="modo_pagamento">Modo de Pagamento *</label>
                <select id="modo_pagamento" name="modo_pagamento" required>
                    <option value="">Selecione...</option>
                    <option value="Dinheiro">Dinheiro</option>
                    <option value="Cartão Crédito">Cartão de Crédito</option>
                    <option value="Cartão Débito">Cartão de Débito</option>
                    <option value="PIX">PIX</option>
                    <option value="Boleto">Boleto</option>
                    <option value="Transferência">Transferência</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>&nbsp;</label>
                <div class="checkbox-group">
                    <input type="checkbox" 
                           id="debito_automatico" 
                           name="debito_automatico">
                    <label for="debito_automatico" style="font-weight: normal;">
                        🔄 Débito Automático
                    </label>
                </div>
                <div class="checkbox-group" style="margin-top: 10px;">
                    <input type="checkbox" 
                           id="recorrente" 
                           name="recorrente"
                           onchange="toggleRecorrenteParcelado(this)">
                    <label for="recorrente" style="font-weight: normal;">
                        🔁 Despesa Recorrente (repetir todo mês)
                    </label>
                </div>
                <div class="checkbox-group" style="margin-top: 10px;">
                    <input type="checkbox" 
                           id="parcelado" 
                           name="parcelado"
                           value="1"
                           onchange="toggleRecorrenteParcelado(this)">
                    <label for="parcelado" style="font-weight: normal;">
                        💳 Despesa Parcelada (número fixo de parcelas)
                    </label>
                </div>
            </div>
        </div>
        
        <!-- Campo de número de parcelas (oculto inicialmente) -->
        <div id="campo_parcelas" style="display: none;">
            <div class="form-row">
                <div class="form-group">
                    <label for="total_parcelas">Número de Parcelas *</label>
                    <input type="number" 
                           id="total_parcelas" 
                           name="total_parcelas" 
                           min="2" 
                           max="360" 
                           value="12"
                           placeholder="Ex: 12">
                    <small style="color: #7f8c8d;">
                        O valor será dividido igualmente entre as parcelas
                    </small>
                </div>
            </div>
        </div>
        
        <div class="form-group">
            <label for="observacoes">Observações</label>
            <textarea id="observacoes" 
                      name="observacoes" 
                      rows="2" 
                      placeholder="Observações adicionais (opcional)"></textarea>
        </div>
        
        <div class="btn-group" style="margin-top: 30px;">
            <button type="submit" class="btn btn-success">
                ✓ Salvar Despesa
            </button>
            <a href="despesas.php" class="btn btn-danger">
                ✕ Cancelar
            </a>
        </div>
    </form>
</div>

<script>
// Sugerir próximo vencimento baseado em contas recorrentes
document.getElementById('nome_conta').addEventListener('blur', function() {
    const conta = this.value.toLowerCase();
    const hoje = new Date();
    const dia = hoje.getDate();
    
    // Sugestões de vencimentos comuns
    const sugestoes = {
        'luz': 15,
        'energia': 15,
        'água': 10,
        'internet': 5,
        'telefone': 10,
        'aluguel': 5,
        'condomínio': 10
    };
    
    for (const [palavra, diaVencimento] of Object.entries(sugestoes)) {
        if (conta.includes(palavra)) {
            const proximaData = new Date(hoje.getFullYear(), hoje.getMonth() + 1, diaVencimento);
            document.getElementById('data_vencimento').value = proximaData.toISOString().split('T')[0];
            break;
        }
    }
});

// Controlar checkboxes de recorrente e parcelado (mutuamente exclusivos)
function toggleRecorrenteParcelado(checkbox) {
    const recorrente = document.getElementById('recorrente');
    const parcelado = document.getElementById('parcelado');
    const campoParcelas = document.getElementById('campo_parcelas');
    
    if (checkbox.id === 'recorrente' && recorrente.checked) {
        parcelado.checked = false;
        campoParcelas.style.display = 'none';
    } else if (checkbox.id === 'parcelado' && parcelado.checked) {
        recorrente.checked = false;
        campoParcelas.style.display = 'block';
        document.getElementById('total_parcelas').required = true;
    } else if (checkbox.id === 'parcelado' && !parcelado.checked) {
        campoParcelas.style.display = 'none';
        document.getElementById('total_parcelas').required = false;
    }
}

// Validação do formulário
document.getElementById('formDespesa').addEventListener('submit', function(e) {
    const parcelado = document.getElementById('parcelado');
    const totalParcelas = document.getElementById('total_parcelas');
    
    if (parcelado.checked) {
        const parcelas = parseInt(totalParcelas.value);
        if (isNaN(parcelas) || parcelas < 2 || parcelas > 360) {
            e.preventDefault();
            alert('Por favor, insira um número de parcelas entre 2 e 360');
            totalParcelas.focus();
            return false;
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>
