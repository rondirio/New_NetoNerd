<?php
/**
 * Processar Ordem de Serviço - NetoNerd ITSM v2.0
 * Sistema inteligente: vincula ou cadastra cliente automaticamente
 */
require('../controller/configurar_log.php');
session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: listar_ordens_servico.php?erro=metodo_invalido');
    exit();
}

$conn = getConnection();

// Captura dados do formulário
$numero_os = trim($_POST['numero_os']);
$chamado_id = !empty($_POST['chamado_id']) ? intval($_POST['chamado_id']) : null;
$tecnico_id = intval($_POST['tecnico_id']);

// Dados do cliente
$cliente_id = !empty($_POST['cliente_id']) ? intval($_POST['cliente_id']) : null;
$cliente_nome = trim($_POST['cliente_nome']);
$cliente_telefone = trim($_POST['cliente_telefone']);
$cliente_email = trim($_POST['cliente_email'] ?? '');
$cliente_endereco = trim($_POST['cliente_endereco'] ?? '');
$cliente_cpf = trim($_POST['cliente_cpf'] ?? '');
$cadastrar_cliente = isset($_POST['cadastrar_cliente']) && $_POST['cadastrar_cliente'] == '1';

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

// Status e controle
$status = $_POST['status'];
$acao = $_POST['acao'];
$created_by = intval($_SESSION['id']);

// Validações
if (empty($numero_os) || empty($tecnico_id) || empty($cliente_nome) || empty($cliente_telefone) || empty($problema_relatado)) {
    header('Location: nova_ordem_servico.php?erro=campos_obrigatorios');
    exit();
}

// Cadastrar cliente se necessário
if (!$cliente_id && $cadastrar_cliente) {
    $sql_insert_cliente = "
        INSERT INTO clientes (nome, telefone, email, endereco, cpf, data_criacao)
        VALUES (?, ?, ?, ?, ?, NOW())
    ";

    $stmt_cliente = $conn->prepare($sql_insert_cliente);

    if (!$stmt_cliente) {
        error_log('processar_ordem_servico: falha ao preparar INSERT clientes: ' . $conn->error);
        header('Location: nova_ordem_servico.php?erro=erro_interno');
        exit();
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
        $stmt_cliente->close();
    } else {
        error_log('processar_ordem_servico: falha ao cadastrar cliente: ' . $stmt_cliente->error);
        header('Location: nova_ordem_servico.php?erro=erro_cadastro_cliente');
        exit();
    }
}

// Define datas baseado no status
$data_inicio = null;
$data_conclusao = null;

if ($status === 'em_andamento') {
    $data_inicio = date('Y-m-d H:i:s');
} elseif ($status === 'concluida') {
    $data_inicio = date('Y-m-d H:i:s');
    $data_conclusao = date('Y-m-d H:i:s');
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

$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log('processar_ordem_servico: falha ao preparar INSERT ordens_servico: ' . $conn->error);
    header('Location: nova_ordem_servico.php?erro=erro_interno');
    exit();
}

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

if ($stmt->execute()) {
    $os_id = $stmt->insert_id;

    // Se veio de um chamado, atualizar status
    if ($chamado_id) {
        $sql_update = "UPDATE chamados SET status = 'em andamento' WHERE id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param('i', $chamado_id);
        $stmt_update->execute();
        $stmt_update->close();
    }

    $stmt->close();
    $conn->close();

    if ($acao === 'salvar_e_imprimir') {
        header('Location: imprimir_ordem_servico.php?id=' . $os_id);
    } else {
        $msg_extra = ($cliente_id && $cadastrar_cliente) ? '&cliente_cadastrado=1' : '';
        header('Location: listar_ordens_servico.php?sucesso=os_criada&numero=' . urlencode($numero_os) . $msg_extra);
    }
    exit();

} else {
    error_log('processar_ordem_servico: falha ao inserir OS: ' . $stmt->error . ' (errno: ' . $stmt->errno . ')');
    $stmt->close();
    $conn->close();
    header('Location: nova_ordem_servico.php?erro=erro_ao_salvar');
    exit();
}
