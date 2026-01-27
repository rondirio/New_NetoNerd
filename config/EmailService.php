<?php
/**
 * Serviço de Email - NetoNerd ITSM
 * Classe para gerenciar o envio de emails usando PHPMailer
 */

require_once __DIR__ . '/../libs/PHPMailer-php-8.4/src/Exception.php';
require_once __DIR__ . '/../libs/PHPMailer-php-8.4/src/PHPMailer.php';
require_once __DIR__ . '/../libs/PHPMailer-php-8.4/src/SMTP.php';
require_once __DIR__ . '/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mailer;
    private $config;

    public function __construct() {
        $this->config = Config::email();
        $this->mailer = new PHPMailer(true);
        $this->configurarSMTP();
    }

    /**
     * Configura as definições do SMTP
     */
    private function configurarSMTP() {
        try {
            // Configurações do servidor
            $this->mailer->isSMTP();
            $this->mailer->Host       = $this->config['host'];
            $this->mailer->SMTPAuth   = true;
            $this->mailer->Username   = $this->config['username'];
            $this->mailer->Password   = $this->config['password'];
            $this->mailer->SMTPSecure = $this->config['encryption'];
            $this->mailer->Port       = $this->config['port'];
            $this->mailer->CharSet    = 'UTF-8';

            // Remetente padrão
            $this->mailer->setFrom($this->config['from_email'], $this->config['from_name']);

        } catch (Exception $e) {
            error_log("Erro ao configurar SMTP: " . $e->getMessage());
        }
    }

    /**
     * Envia um email genérico
     *
     * @param string $destinatario Email do destinatário
     * @param string $nome Nome do destinatário
     * @param string $assunto Assunto do email
     * @param string $corpo Corpo do email (HTML)
     * @param string $corpoTexto Versão texto do email (opcional)
     * @return bool
     */
    public function enviarEmail($destinatario, $nome, $assunto, $corpo, $corpoTexto = '') {
        try {
            // Limpar destinatários anteriores
            $this->mailer->clearAddresses();
            $this->mailer->clearAttachments();

            // Destinatário
            $this->mailer->addAddress($destinatario, $nome);

            // Conteúdo
            $this->mailer->isHTML(true);
            $this->mailer->Subject = $assunto;
            $this->mailer->Body    = $corpo;
            $this->mailer->AltBody = $corpoTexto ?: strip_tags($corpo);

            $this->mailer->send();
            return true;

        } catch (Exception $e) {
            error_log("Erro ao enviar email: " . $this->mailer->ErrorInfo);
            return false;
        }
    }

    /**
     * Notifica o técnico sobre um novo chamado
     *
     * @param array $tecnico Dados do técnico
     * @param array $chamado Dados do chamado
     * @param array $cliente Dados do cliente
     * @return bool
     */
    public function notificarNovoChamado($tecnico, $chamado, $cliente) {
        $assunto = "[NetoNerd] Novo Chamado #{$chamado['protocolo']} - {$chamado['titulo']}";

        $corpo = $this->gerarTemplateChamado([
            'titulo' => 'Novo Chamado Atribuído',
            'nome_tecnico' => $tecnico['nome'],
            'protocolo' => $chamado['protocolo'],
            'titulo_chamado' => $chamado['titulo'],
            'descricao' => $chamado['descricao'],
            'prioridade' => $chamado['prioridade'],
            'categoria' => $chamado['categoria'] ?? 'Não especificada',
            'cliente_nome' => $cliente['nome'],
            'cliente_email' => $cliente['email'],
            'cliente_telefone' => $cliente['telefone'] ?? 'Não informado',
            'data_abertura' => date('d/m/Y H:i', strtotime($chamado['data_abertura'])),
            'link' => Config::get('APP_URL', 'http://localhost') . '/tecnico/visualizar_chamado.php?id=' . $chamado['id']
        ]);

        return $this->enviarEmail($tecnico['email'], $tecnico['nome'], $assunto, $corpo);
    }

    /**
     * Notifica o técnico sobre uma nova resposta do cliente
     *
     * @param array $tecnico Dados do técnico
     * @param array $chamado Dados do chamado
     * @param array $cliente Dados do cliente
     * @param string $resposta Texto da resposta
     * @return bool
     */
    public function notificarNovaResposta($tecnico, $chamado, $cliente, $resposta) {
        $assunto = "[NetoNerd] Nova Resposta no Chamado #{$chamado['protocolo']}";

        $corpo = $this->gerarTemplateResposta([
            'titulo' => 'Nova Resposta do Cliente',
            'nome_tecnico' => $tecnico['nome'],
            'protocolo' => $chamado['protocolo'],
            'titulo_chamado' => $chamado['titulo'],
            'cliente_nome' => $cliente['nome'],
            'resposta' => $resposta,
            'data_resposta' => date('d/m/Y H:i'),
            'link' => Config::get('APP_URL', 'http://localhost') . '/tecnico/visualizar_chamado.php?id=' . $chamado['id']
        ]);

        return $this->enviarEmail($tecnico['email'], $tecnico['nome'], $assunto, $corpo);
    }

    /**
     * Notifica o cliente sobre uma atualização no chamado
     *
     * @param array $cliente Dados do cliente
     * @param array $chamado Dados do chamado
     * @param string $mensagem Mensagem da atualização
     * @return bool
     */
    public function notificarClienteAtualizacao($cliente, $chamado, $mensagem) {
        $assunto = "[NetoNerd] Atualização no Chamado #{$chamado['protocolo']}";

        $corpo = $this->gerarTemplateAtualizacao([
            'titulo' => 'Atualização no seu Chamado',
            'nome_cliente' => $cliente['nome'],
            'protocolo' => $chamado['protocolo'],
            'titulo_chamado' => $chamado['titulo'],
            'mensagem' => $mensagem,
            'status' => $chamado['status'],
            'data_atualizacao' => date('d/m/Y H:i'),
            'link' => Config::get('APP_URL', 'http://localhost') . '/cliente/visualizar_chamado.php?id=' . $chamado['id']
        ]);

        return $this->enviarEmail($cliente['email'], $cliente['nome'], $assunto, $corpo);
    }

    /**
     * Notifica o cliente sobre fechamento do chamado
     *
     * @param array $cliente Dados do cliente
     * @param array $chamado Dados do chamado
     * @return bool
     */
    public function notificarChamadoFechado($cliente, $chamado) {
        $assunto = "[NetoNerd] Chamado #{$chamado['protocolo']} Fechado";

        $corpo = $this->gerarTemplateAtualizacao([
            'titulo' => 'Chamado Fechado',
            'nome_cliente' => $cliente['nome'],
            'protocolo' => $chamado['protocolo'],
            'titulo_chamado' => $chamado['titulo'],
            'mensagem' => 'Seu chamado foi fechado com sucesso. Caso o problema persista, você pode abrir um novo chamado.',
            'status' => 'Fechado',
            'data_atualizacao' => date('d/m/Y H:i'),
            'link' => Config::get('APP_URL', 'http://localhost') . '/cliente/meus_chamados.php'
        ]);

        return $this->enviarEmail($cliente['email'], $cliente['nome'], $assunto, $corpo);
    }

    /**
     * Gera template HTML para novo chamado
     */
    private function gerarTemplateChamado($dados) {
        $prioridadeCor = $this->getCorPrioridade($dados['prioridade']);

        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .chamado-info { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                .info-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
                .info-label { font-weight: bold; color: #666; }
                .prioridade { display: inline-block; padding: 5px 15px; border-radius: 20px; color: white; font-weight: bold; background: {$prioridadeCor}; }
                .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; color: #666; font-size: 12px; padding: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🎫 {$dados['titulo']}</h1>
                </div>
                <div class='content'>
                    <p>Olá <strong>{$dados['nome_tecnico']}</strong>,</p>
                    <p>Um novo chamado foi atribuído a você:</p>

                    <div class='chamado-info'>
                        <div class='info-row'>
                            <span class='info-label'>Protocolo:</span>
                            <span>#{$dados['protocolo']}</span>
                        </div>
                        <div class='info-row'>
                            <span class='info-label'>Título:</span>
                            <span>{$dados['titulo_chamado']}</span>
                        </div>
                        <div class='info-row'>
                            <span class='info-label'>Prioridade:</span>
                            <span class='prioridade'>{$dados['prioridade']}</span>
                        </div>
                        <div class='info-row'>
                            <span class='info-label'>Categoria:</span>
                            <span>{$dados['categoria']}</span>
                        </div>
                        <div class='info-row'>
                            <span class='info-label'>Data de Abertura:</span>
                            <span>{$dados['data_abertura']}</span>
                        </div>
                    </div>

                    <h3>Descrição do Problema:</h3>
                    <p style='background: white; padding: 15px; border-left: 4px solid #667eea; border-radius: 4px;'>{$dados['descricao']}</p>

                    <h3>Dados do Cliente:</h3>
                    <div class='chamado-info'>
                        <div class='info-row'>
                            <span class='info-label'>Nome:</span>
                            <span>{$dados['cliente_nome']}</span>
                        </div>
                        <div class='info-row'>
                            <span class='info-label'>Email:</span>
                            <span>{$dados['cliente_email']}</span>
                        </div>
                        <div class='info-row'>
                            <span class='info-label'>Telefone:</span>
                            <span>{$dados['cliente_telefone']}</span>
                        </div>
                    </div>

                    <center>
                        <a href='{$dados['link']}' class='button'>Visualizar Chamado</a>
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
    }

    /**
     * Gera template HTML para nova resposta
     */
    private function gerarTemplateResposta($dados) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .resposta-box { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); border-left: 4px solid #28a745; }
                .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; color: #666; font-size: 12px; padding: 20px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>💬 Nova Resposta</h1>
                </div>
                <div class='content'>
                    <p>Olá <strong>{$dados['nome_tecnico']}</strong>,</p>
                    <p>O cliente <strong>{$dados['cliente_nome']}</strong> adicionou uma nova resposta no chamado <strong>#{$dados['protocolo']}</strong>:</p>

                    <h3>{$dados['titulo_chamado']}</h3>

                    <div class='resposta-box'>
                        <p><strong>Resposta do Cliente:</strong></p>
                        <p>{$dados['resposta']}</p>
                        <p style='color: #666; font-size: 12px; margin-top: 15px;'>Em {$dados['data_resposta']}</p>
                    </div>

                    <center>
                        <a href='{$dados['link']}' class='button'>Responder Chamado</a>
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
    }

    /**
     * Gera template HTML para atualização
     */
    private function gerarTemplateAtualizacao($dados) {
        return "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='UTF-8'>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
                .update-box { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
                .button { display: inline-block; padding: 12px 30px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; color: #666; font-size: 12px; padding: 20px; }
                .status-badge { display: inline-block; padding: 5px 15px; border-radius: 20px; background: #28a745; color: white; font-weight: bold; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔔 {$dados['titulo']}</h1>
                </div>
                <div class='content'>
                    <p>Olá <strong>{$dados['nome_cliente']}</strong>,</p>
                    <p>Há uma atualização no seu chamado <strong>#{$dados['protocolo']}</strong>:</p>

                    <div class='update-box'>
                        <h3>{$dados['titulo_chamado']}</h3>
                        <p><strong>Status:</strong> <span class='status-badge'>{$dados['status']}</span></p>
                        <p><strong>Atualização:</strong></p>
                        <p>{$dados['mensagem']}</p>
                        <p style='color: #666; font-size: 12px; margin-top: 15px;'>Em {$dados['data_atualizacao']}</p>
                    </div>

                    <center>
                        <a href='{$dados['link']}' class='button'>Ver Detalhes</a>
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
    }

    /**
     * Retorna a cor baseada na prioridade
     */
    private function getCorPrioridade($prioridade) {
        $cores = [
            'baixa' => '#28a745',
            'media' => '#ffc107',
            'alta' => '#fd7e14',
            'critica' => '#dc3545',
            'urgente' => '#dc3545'
        ];

        return $cores[strtolower($prioridade)] ?? '#6c757d';
    }
}
