<?php
/**
 * Processar Cadastro de Técnico
 * NetoNerd ITSM v2.0
 */

// Inclui o arquivo de conexão com o banco de dados
require_once '../config/bandoDeDados/conexao.php';

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Obtém os dados enviados pelo formulário
    $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $status = isset($_POST['status_technician']) ? trim($_POST['status_technician']) : '';
    $matricula = isset($_POST['registration']) ? trim($_POST['registration']) : '';
    $veiculo = isset($_POST['vehicle_of_the_day']) ? trim($_POST['vehicle_of_the_day']) : '';
    $senha = isset($_POST['password']) ? trim($_POST['password']) : '';
    
    // Valida se os campos obrigatórios foram preenchidos
    if (empty($nome) || empty($email) || empty($status) || empty($matricula) || empty($veiculo) || empty($senha)) {
        header('Location: ../admin/cadastrar_tecnico.php?erro=campos_obrigatorios');
        exit();
    }
    
    // Valida formato do email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ../admin/cadastrar_tecnico.php?erro=email_invalido');
        exit();
    }
    
    // Verifica se o email já existe
    $sql_check = "SELECT id FROM tecnicos WHERE email = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param('s', $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    
    if ($result_check->num_rows > 0) {
        $stmt_check->close();
        $conn->close();
        header('Location: ../admin/cadastrar_tecnico.php?erro=email_existente');
        exit();
    }
    $stmt_check->close();
    
    // Verifica se a matrícula já existe
    $sql_check_mat = "SELECT id FROM tecnicos WHERE matricula = ?";
    $stmt_check_mat = $conn->prepare($sql_check_mat);
    $stmt_check_mat->bind_param('s', $matricula);
    $stmt_check_mat->execute();
    $result_check_mat = $stmt_check_mat->get_result();
    
    if ($result_check_mat->num_rows > 0) {
        $stmt_check_mat->close();
        $conn->close();
        header('Location: ../admin/cadastrar_tecnico.php?erro=matricula_existente');
        exit();
    }
    $stmt_check_mat->close();
    
    // Hash da senha
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    
    // Prepara a consulta SQL para inserir os dados (SEM o campo 'id' - auto_increment)
    $sql = "INSERT INTO tecnicos (nome, email, status_tecnico, Ativo, matricula, carro_do_dia, senha_hash) 
            VALUES (?, ?, ?, 1, ?, ?, ?)";
    
    // Prepara a declaração
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        // Vincula os parâmetros
        $stmt->bind_param('ssssss', $nome, $email, $status, $matricula, $veiculo, $senha_hash);
        
        // Executa a consulta
        if ($stmt->execute()) {
            // Sucesso - Redireciona para o dashboard do ADMIN
            $stmt->close();
            $conn->close();
            header('Location: ../admin/dashboard.php?sucesso=tecnico_cadastrado');
            exit();
        } else {
            // Erro ao executar
            $erro_msg = urlencode($stmt->error);
            $stmt->close();
            $conn->close();
            header('Location: ../admin/cadastrar_tecnico.php?erro=erro_banco&msg=' . $erro_msg);
            exit();
        }
    } else {
        // Erro na preparação
        $erro_msg = urlencode($conn->error);
        $conn->close();
        header('Location: ../admin/cadastrar_tecnico.php?erro=erro_preparacao&msg=' . $erro_msg);
        exit();
    }
    
} else {
    // Método de requisição inválido
    header('Location: ../admin/cadastrar_tecnico.php?erro=metodo_invalido');
    exit();
}
?>