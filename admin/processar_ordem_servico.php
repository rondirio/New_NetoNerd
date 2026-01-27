<?php
/**
 * Processar Ordem de Serviço - NetoNerd ITSM v2.0 - DEBUG VERSION
 * Sistema inteligente: vincula ou cadastra cliente automaticamente
 */
 require('../controller/configurar_log.php');
session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';


session_start();
echo "<pre>";
print_r($_SESSION);
echo "</pre>";


requireAdmin();

// Ativar exibição de erros
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("ERRO: Método não é POST");
}

$conn = getConnection();

echo "<pre>";
echo "=== DEBUG: PROCESSANDO ORDEM DE SERVIÇO ===\n\n";

// Captura dados do formulário
$numero_os = trim($_POST['numero_os']);
$chamado_id = !empty($_POST['chamado_id']) ? intval($_POST['chamado_id']) : null;
$tecnico_id = intval($_POST['tecnico_id']);

echo "1. DADOS BÁSICOS:\n";
echo "   - numero_os: $numero_os\n";
echo "   - chamado_id: " . ($chamado_id ?? 'NULL') . "\n";
echo "   - tecnico_id: $tecnico_id\n\n";

// Dados do cliente
$cliente_id = !empty($_POST['cliente_id']) ? intval($_POST['cliente_id']) : null;
$cliente_nome = trim($_POST['cliente_nome']);
$cliente_telefone = trim($_POST['cliente_telefone']);
$cliente_email = trim($_POST['cliente_email'] ?? '');
$cliente_endereco = trim($_POST['cliente_endereco'] ?? '');
$cliente_cpf = trim($_POST['cliente_cpf'] ?? '');
$cadastrar_cliente = isset($_POST['cadastrar_cliente']) && $_POST['cadastrar_cliente'] == '1';

echo "2. DADOS DO CLIENTE:\n";
echo "   - cliente_id: " . ($cliente_id ?? 'NULL') . "\n";
echo "   - cliente_nome: $cliente_nome\n";
echo "   - cliente_telefone: $cliente_telefone\n";
echo "   - cliente_email: $cliente_email\n";
echo "   - cadastrar_cliente: " . ($cadastrar_cliente ? 'SIM' : 'NÃO') . "\n\n";

// Dados do equipamento
$equipamento_tipo = trim($_POST['equipamento_tipo'] ?? '');
$equipamento_marca = trim($_POST['equipamento_marca'] ?? '');
$equipamento_modelo = trim($_POST['equipamento_modelo'] ?? '');
$equipamento_serial = trim($_POST['equipamento_serial'] ?? '');

// Serviços
$problema_relatado = trim($_POST['problema_relatado']);
$servicos_executados = trim($_POST['servicos_executados'] ?? '');
$pecas_utilizadas = trim($_POST['pecas_utilizadas'] ?? '');
$observacoes = trim($_POST['observacoes'] ?? '');

// Valores
$valor_mao_obra = floatval($_POST['valor_mao_obra']);
$valor_pecas = floatval($_POST['valor_pecas']);
$valor_total = floatval($_POST['valor_total']);

echo "3. VALORES:\n";
echo "   - valor_mao_obra: $valor_mao_obra\n";
echo "   - valor_pecas: $valor_pecas\n";
echo "   - valor_total: $valor_total\n\n";

// Status e controle
$status = $_POST['status'];
$acao = $_POST['acao'];
$created_by = intval($_SESSION['id']);

echo "4. STATUS E CONTROLE:\n";
echo "   - status: $status\n";
echo "   - acao: $acao\n";
echo "   - created_by: $created_by\n\n";

// Validações
if (empty($numero_os) || empty($tecnico_id) || empty($cliente_nome) || empty($cliente_telefone) || empty($problema_relatado)) {
    die("ERRO: Campos obrigatórios não preenchidos!");
}

// Cadastrar cliente se necessário
if (!$cliente_id && $cadastrar_cliente) {
    echo "5. CADASTRANDO NOVO CLIENTE...\n";
    
    $sql_insert_cliente = "
        INSERT INTO clientes (nome, telefone, email, endereco, cpf, data_cadastro)
        VALUES (?, ?, ?, ?, ?, NOW())
    ";
    
    $stmt_cliente = $conn->prepare($sql_insert_cliente);
    
    if (!$stmt_cliente) {
        die("ERRO AO PREPARAR INSERÇÃO DE CLIENTE: " . $conn->error);
    }
    
    $stmt_cliente->bind_param(
        'sssss',
        $cliente_nome,
        $cliente_telefone,
        $cliente_email,
        $cliente_endereco,
        $cliente_cpf
    );
    
    if ($stmt_cliente->execute()) {
        $cliente_id = $stmt_cliente->insert_id;
        echo "   - Cliente cadastrado com ID: $cliente_id\n\n";
        $stmt_cliente->close();
    } else {
        die("ERRO AO CADASTRAR CLIENTE: " . $stmt_cliente->error);
    }
}

// Define datas baseado no status
$data_inicio = null;
$data_conclusao = null;

if ($status === 'em_andamento') {
    $data_inicio = date('Y-m-d H:i:s');
    echo "6. STATUS EM ANDAMENTO - data_inicio definida: $data_inicio\n\n";
} elseif ($status === 'concluida') {
    $data_inicio = date('Y-m-d H:i:s');
    $data_conclusao = date('Y-m-d H:i:s');
    echo "6. STATUS CONCLUÍDA - datas definidas\n";
    echo "   - data_inicio: $data_inicio\n";
    echo "   - data_conclusao: $data_conclusao\n\n";
}

// Converter strings vazias em NULL
$cliente_email = $cliente_email ?: null;
$cliente_endereco = $cliente_endereco ?: null;
$cliente_cpf = $cliente_cpf ?: null;
$equipamento_tipo = $equipamento_tipo ?: null;
$equipamento_marca = $equipamento_marca ?: null;
$equipamento_modelo = $equipamento_modelo ?: null;
$equipamento_serial = $equipamento_serial ?: null;
$servicos_executados = $servicos_executados ?: null;
$pecas_utilizadas = $pecas_utilizadas ?: null;
$observacoes = $observacoes ?: null;

echo "7. PREPARANDO INSERT...\n";

$sql = "
    INSERT INTO ordens_servico (
        numero_os, 
        chamado_id, 
        tecnico_id, 
        cliente_id,
        cliente_nome, 
        cliente_telefone, 
        cliente_email, 
        cliente_endereco, 
        cliente_cpf,
        equipamento_tipo, 
        equipamento_marca, 
        equipamento_modelo, 
        equipamento_serial,
        problema_relatado, 
        servicos_executados, 
        pecas_utilizadas, 
        observacoes,
        valor_mao_obra, 
        valor_pecas, 
        valor_total,
        data_inicio, 
        data_conclusao, 
        status, 
        created_by
    ) VALUES (
        ?, ?, ?, ?,
        ?, ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?, ?,
        ?, ?, ?,
        ?, ?, ?, ?
    )
";

echo "   SQL preparado.\n\n";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("ERRO AO PREPARAR SQL: " . $conn->error);
}

echo "8. BINDING PARAMETERS...\n";

$stmt->bind_param(
    'siiisssssssssssssdddsssi',
    $numero_os,             // 1 - s
    $chamado_id,            // 2 - i
    $tecnico_id,            // 3 - i
    $cliente_id,            // 4 - i
    $cliente_nome,          // 5 - s
    $cliente_telefone,      // 6 - s
    $cliente_email,         // 7 - s
    $cliente_endereco,      // 8 - s
    $cliente_cpf,           // 9 - s
    $equipamento_tipo,      // 10 - s
    $equipamento_marca,     // 11 - s
    $equipamento_modelo,    // 12 - s
    $equipamento_serial,    // 13 - s
    $problema_relatado,     // 14 - s
    $servicos_executados,   // 15 - s
    $pecas_utilizadas,      // 16 - s
    $observacoes,           // 17 - s
    $valor_mao_obra,        // 18 - d
    $valor_pecas,           // 19 - d
    $valor_total,           // 20 - d
    $data_inicio,           // 21 - s
    $data_conclusao,        // 22 - s
    $status,                // 23 - s
    $created_by             // 24 - i
);

echo "   Parâmetros vinculados.\n\n";

echo "9. EXECUTANDO INSERT...\n";

if ($stmt->execute()) {
    $os_id = $stmt->insert_id;
    echo "   ✓ SUCESSO! Ordem criada com ID: $os_id\n\n";
    
    // Verificar se realmente foi inserido
    $sql_check = "SELECT * FROM ordens_servico WHERE id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param('i', $os_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        echo "10. VERIFICAÇÃO: Registro encontrado no banco!\n";
        $os_verificada = $result_check->fetch_assoc();
        echo "    - ID: " . $os_verificada['id'] . "\n";
        echo "    - Número OS: " . $os_verificada['numero_os'] . "\n";
        echo "    - Cliente: " . $os_verificada['cliente_nome'] . "\n";
        echo "    - Status: " . $os_verificada['status'] . "\n";
    } else {
        echo "10. ERRO: Registro NÃO encontrado no banco após inserção!\n";
    }
    $stmt_check->close();
    
    // Se veio de um chamado, atualizar
    if ($chamado_id) {
        echo "\n11. Atualizando chamado vinculado...\n";
        $sql_update = "UPDATE chamados SET status = 'em andamento' WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param('i', $chamado_id);
        if ($stmt_update->execute()) {
            echo "    ✓ Chamado atualizado\n";
        }
        $stmt_update->close();
    }
    
    $stmt->close();
    $conn->close();
    
    echo "\n=== FIM DO DEBUG ===\n";
    echo "\nRedirecionando em 5 segundos...\n";
    echo "</pre>";
    
    // Redirecionar
    if ($acao === 'salvar_e_imprimir') {
        echo "<script>setTimeout(function(){ window.location.href = 'imprimir_ordem_servico.php?id=$os_id'; }, 5000);</script>";
    } else {
        $msg_extra = $cliente_id && $cadastrar_cliente ? '&cliente_cadastrado=1' : '';
        echo "<script>setTimeout(function(){ window.location.href = 'listar_ordens_servico.php?sucesso=os_criada&numero=" . urlencode($numero_os) . "$msg_extra'; }, 5000);</script>";
    }
    
} else {
    echo "   ✗ ERRO AO EXECUTAR: " . $stmt->error . "\n";
    echo "   Código do erro: " . $stmt->errno . "\n";
    $stmt->close();
    $conn->close();
}

echo "</pre>";
?>