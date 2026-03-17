<?php
session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';
require_once '../config/LicenseManager.php';
require_once '../config/EmailService.php';

// PROTEÇÃO: Apenas administradores podem acessar
requireAdmin();

$conn = getConnection();
$licenseManager = new LicenseManager();
$acao = $_REQUEST['acao'] ?? '';

try {
    switch ($acao) {
        case 'gerar':
            // Gerar nova licença
            $produto_id = intval($_POST['produto_id']);
            $cliente_id = intval($_POST['cliente_id']);
            $tipo_licenca = $_POST['tipo_licenca'];
            $observacoes = trim($_POST['observacoes'] ?? '');
            $vendedor_id = $_SESSION['usuario_id'] ?? null;

            if (!$produto_id || !$cliente_id) {
                throw new Exception('Dados inválidos');
            }

            $licenca = $licenseManager->gerarLicenca($produto_id, $cliente_id, $tipo_licenca, $vendedor_id);

            if (!$licenca) {
                throw new Exception('Erro ao gerar licença');
            }

            // Atualizar observações se fornecidas
            if ($observacoes) {
                $stmt = $conn->prepare("UPDATE licencas SET observacoes = ? WHERE id = ?");
                $stmt->bind_param("si", $observacoes, $licenca['id']);
                $stmt->execute();
                $stmt->close();
            }

            // Enviar email para o cliente com a API Key
            try {
                $stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
                $stmt->bind_param("i", $cliente_id);
                $stmt->execute();
                $cliente = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                $stmt = $conn->prepare("SELECT * FROM produtos_licenciaveis WHERE id = ?");
                $stmt->bind_param("i", $produto_id);
                $stmt->execute();
                $produto = $stmt->get_result()->fetch_assoc();
                $stmt->close();

                $emailService = new EmailService();
                enviarEmailLicenca($emailService, $cliente, $produto, $licenca);
            } catch (Exception $e) {
                error_log("Erro ao enviar email de licença: " . $e->getMessage());
            }

            header('Location: licencas.php?sucesso=gerada');
            break;

        case 'suspender':
            $licenca_id = intval($_GET['id']);
            if (!$licenca_id) throw new Exception('ID inválido');

            $licenseManager->suspenderLicenca($licenca_id, 'Suspensão manual pelo administrador');

            header('Location: licencas.php?sucesso=suspensa');
            break;

        case 'reativar':
            $licenca_id = intval($_GET['id']);
            if (!$licenca_id) throw new Exception('ID inválido');

            // Calcular próxima cobrança (30 dias a partir de agora)
            $proxima_cobranca = date('Y-m-d H:i:s', strtotime('+30 days'));

            $licenseManager->reativarLicenca($licenca_id, $proxima_cobranca);

            header('Location: licencas.php?sucesso=reativada');
            break;

        default:
            throw new Exception('Ação inválida');
    }

} catch (Exception $e) {
    error_log("Erro ao processar licença: " . $e->getMessage());
    header('Location: licencas.php?erro=' . urlencode($e->getMessage()));
}

$conn->close();

/**
 * Envia email com informações da licença gerada
 */
function enviarEmailLicenca($emailService, $cliente, $produto, $licenca) {
    $assunto = "[NetoNerd] Sua Licença do {$produto['nome']} foi Gerada!";

    $corpo = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9fa; padding: 30px; border: 1px solid #ddd; }
            .api-key-box { background: #2c3e50; color: #2ecc71; padding: 20px; border-radius: 8px; font-family: monospace; font-size: 14px; margin: 20px 0; text-align: center; }
            .info-box { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
            .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; color: #666; font-size: 12px; padding: 20px; }
            .warning { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 Bem-vindo ao {$produto['nome']}!</h1>
            </div>
            <div class='content'>
                <p>Olá <strong>{$cliente['nome']}</strong>,</p>
                <p>Sua licença do <strong>{$produto['nome']}</strong> foi gerada com sucesso!</p>

                <div class='info-box'>
                    <h3>📋 Informações da Licença</h3>
                    <p><strong>Produto:</strong> {$produto['nome']}</p>
                    <p><strong>Tipo:</strong> " . ucfirst($licenca['tipo_licenca']) . "</p>
                    <p><strong>Status:</strong> Ativa (30 dias de trial)</p>
                    <p><strong>Valor:</strong> R$ " . number_format($licenca['valor_licenca'], 2, ',', '.') . "</p>
                </div>

                <div class='api-key-box'>
                    <strong>🔑 Sua API Key:</strong><br><br>
                    {$licenca['api_key']}
                </div>

                <div class='warning'>
                    <strong>⚠️ Importante:</strong><br>
                    • Guarde esta API Key em local seguro<br>
                    • Você terá 30 dias de trial grátis<br>
                    • Após o trial, a primeira cobrança será de R$ " . number_format($licenca['valor_licenca'], 2, ',', '.') . "<br>
                    • Você tem 7 dias para realizar o pagamento após o vencimento
                </div>

                <h3>🚀 Como Ativar seu Sistema</h3>
                <ol>
                    <li>Acesse a URL de instalação do seu sistema</li>
                    <li>Insira a API Key acima quando solicitado</li>
                    <li>O sistema criará automaticamente um usuário administrador</li>
                    <li>Pronto! Você já pode começar a usar</li>
                </ol>

                <div class='info-box'>
                    <h4>📞 Precisa de Ajuda?</h4>
                    <p>Entre em contato conosco:</p>
                    <p>
                        📧 Email: suporte@netonerd.com.br<br>
                        📱 WhatsApp: (21) 97739-5867<br>
                        🌐 Site: www.netonerd.com.br
                    </p>
                </div>

                <center>
                    <p>Obrigado por escolher a NetoNerd!</p>
                </center>
            </div>
            <div class='footer'>
                <p>NetoNerd Soluções Digitais LTDA<br>
                Este é um email automático, por favor não responda.</p>
            </div>
        </div>
    </body>
    </html>
    ";

    return $emailService->enviarEmail($cliente['email'], $cliente['nome'], $assunto, $corpo);
}
?>
