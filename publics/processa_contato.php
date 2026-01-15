<?php
/**
 * Processador de Formulário de Contato - NetoNerd
 * Versão Segura com PHPMailer (sem Composer)
 */

require_once "../config/bandoDeDados/conexao.php";
require_once "../config/EmailService.php";

// Inclui os arquivos do PHPMailer (coloque a pasta PHPMailer/src no mesmo nível ou ajuste o caminho)
require_once __DIR__ . '/../libs/PHPMailer-php-8.4/src/Exception.php';
// require_once __DIR__ . '../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer-php-8.4/src/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer-php-8.4/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Verifica se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: contato.php');
    exit();
}

try {
    $conn = getConnection();
    
    // Sanitização e validação dos dados
    $nome      = trim($_POST['nome'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $telefone  = preg_replace('/\D/', '', $_POST['telefone'] ?? '');
    $assunto   = trim($_POST['assunto'] ?? '');
    $mensagem  = trim($_POST['mensagem'] ?? '');
    
    // Validações
    if (empty($nome) || strlen($nome) < 3) {
        throw new Exception("Nome inválido");
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Email inválido");
    }
    
    if (strlen($telefone) < 10) {
        throw new Exception("Telefone inválido");
    }
    
    if (empty($assunto)) {
        throw new Exception("Assunto não selecionado");
    }
    
    if (strlen($mensagem) < 10) {
        throw new Exception("Mensagem muito curta");
    }
    
    // Proteção anti-spam simples
    session_start();
    $tempo_ultimo_envio = $_SESSION['ultimo_contato'] ?? 0;
    $tempo_atual = time();
    
    if (($tempo_atual - $tempo_ultimo_envio) < 60) {
        throw new Exception("Aguarde um minuto antes de enviar outra mensagem");
    }
    
    // Cria tabela de contatos se não existir
    $sql_create = "CREATE TABLE IF NOT EXISTS contatos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(200) NOT NULL,
        email VARCHAR(200) NOT NULL,
        telefone VARCHAR(20) NOT NULL,
        assunto VARCHAR(100) NOT NULL,
        mensagem TEXT NOT NULL,
        ip_address VARCHAR(45),
        respondido BOOLEAN DEFAULT FALSE,
        data_contato TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_data (data_contato),
        INDEX idx_respondido (respondido)
    )";
    $conn->query($sql_create);
    
    // Registra no banco de dados
    $ip_address = $_SERVER['REMOTE_ADDR'];
    
    $stmt = $conn->prepare(
        "INSERT INTO contatos (nome, email, telefone, assunto, mensagem, ip_address) 
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("ssssss", $nome, $email, $telefone, $assunto, $mensagem, $ip_address);
    $stmt->execute();
    $contato_id = $conn->insert_id;
    $stmt->close();
    
    // Enviar email para a equipe usando EmailService
    $emailConfig = Config::email();
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = $emailConfig['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $emailConfig['username'];
    $mail->Password   = $emailConfig['password'];
    $mail->SMTPSecure = $emailConfig['encryption'];
    $mail->Port       = $emailConfig['port'];
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom($emailConfig['from_email'], 'Contato Site NetoNerd');
    $mail->addReplyTo($email, $nome);
    $mail->addAddress($emailConfig['username']);

    $mensagem_email = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <h2>Novo Contato do Site</h2>
        <p><strong>Nome:</strong> {$nome}</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Telefone:</strong> " . formatarTelefone($telefone) . "</p>
        <p><strong>Assunto:</strong> {$assunto}</p>
        <p><strong>Mensagem:</strong></p>
        <p style='background: #f8f9fa; padding: 15px; border-radius: 5px;'>{$mensagem}</p>
        <p><strong>Protocolo:</strong> #{$contato_id}</p>
        <p><strong>Data:</strong> " . date('d/m/Y H:i') . "</p>
    </body>
    </html>";

    $mail->isHTML(true);
    $mail->Subject = "Novo Contato - {$assunto} (#{$contato_id})";
    $mail->Body    = $mensagem_email;
    $mail->send();

    // Email de confirmação para o cliente
    $mail = new PHPMailer(true);

    $mail->isSMTP();
    $mail->Host       = $emailConfig['host'];
    $mail->SMTPAuth   = true;
    $mail->Username   = $emailConfig['username'];
    $mail->Password   = $emailConfig['password'];
    $mail->SMTPSecure = $emailConfig['encryption'];
    $mail->Port       = $emailConfig['port'];
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom($emailConfig['from_email'], $emailConfig['from_name']);
    $mail->addAddress($email, $nome);

    $mail->isHTML(true);
    $mail->Subject = "Recebemos seu contato - NetoNerd";

    $mensagem_confirmacao = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #007bff, #0056b3); color: white; 
                        padding: 30px; border-radius: 10px; text-align: center;'>
                <h2 style='margin: 0;'>Obrigado pelo Contato!</h2>
            </div>
            <div style='padding: 30px; background: #f8f9fa; margin-top: 20px; border-radius: 10px;'>
                <p>Olá <strong>{$nome}</strong>,</p>
                <p>Recebemos sua mensagem sobre: <strong>{$assunto}</strong></p>
                <p>Nossa equipe analisará sua solicitação e retornará em breve.</p>
                <p style='background: white; padding: 15px; border-left: 4px solid #007bff; border-radius: 5px;'>
                    <strong>Protocolo:</strong> #{$contato_id}<br>
                    <strong>Data:</strong> " . date('d/m/Y H:i') . "
                </p>
                <p>Enquanto isso, você também pode:</p>
                <ul>
                    <li><a href='https://wa.me/5521977395867' style='color: #25d366;'>Falar diretamente via WhatsApp</a></li>
                    <li><a href='tel:+5521977395867' style='color: #007bff;'>Ligar para (21) 97739-5867</a></li>
                </ul>
                <p style='text-align: center; margin-top: 30px;'>
                    <a href='http://seusite.com' 
                       style='background: #007bff; color: white; padding: 12px 30px; 
                              text-decoration: none; border-radius: 5px; display: inline-block;'>
                        Acessar Site
                    </a>
                </p>
            </div>
            <div style='text-align: center; margin-top: 20px; color: #666; font-size: 12px;'>
                <p>NetoNerd Soluções Digitais<br>
                Teresópolis - RJ<br>
                (21) 97739-5867</p>
            </div>
        </div>
    </body>
    </html>";

    $mail->Body = $mensagem_confirmacao;
    $mail->send();

    // Atualiza timestamp do último envio
    $_SESSION['ultimo_contato'] = $tempo_atual;
    
    // Log
    error_log("Contato recebido: {$nome} ({$email}) - Assunto: {$assunto} - ID: {$contato_id}");
    
    $conn->close();
    
    // Redireciona com sucesso
    header('Location: contato.php?sucesso=1');
    exit();

} catch (Exception $e) {
    error_log("Erro ao processar contato: " . $e->getMessage());
    header('Location: contato.php?erro=' . urlencode($e->getMessage()));
    exit();
}

/**
 * Função auxiliar para formatar telefone
 */
function formatarTelefone($telefone) {
    if (strlen($telefone) === 11) {
        return "(" . substr($telefone, 0, 2) . ") " . 
               substr($telefone, 2, 5) . "-" . substr($telefone, 7);
    } else if (strlen($telefone) === 10) {
        return "(" . substr($telefone, 0, 2) . ") " . 
               substr($telefone, 2, 4) . "-" . substr($telefone, 6);
    }
    return $telefone;
}
?>