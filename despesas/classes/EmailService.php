<?php
require_once __DIR__ . '/../config/email.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mail;
    
    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->configurar();
    }
    
    /**
     * Configura o PHPMailer
     */
    private function configurar() {
        try {
            // Configurações do servidor
            $this->mail->isSMTP();
            $this->mail->Host = SMTP_HOST;
            $this->mail->SMTPAuth = true;
            $this->mail->Username = SMTP_USERNAME;
            $this->mail->Password = SMTP_PASSWORD;
            $this->mail->SMTPSecure = SMTP_SECURE;
            $this->mail->Port = SMTP_PORT;
            
            // Configurações do remetente
            $this->mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
            
            // Codificação
            $this->mail->CharSet = 'UTF-8';
            $this->mail->Encoding = 'base64';
            
        } catch (Exception $e) {
            throw new Exception("Erro na configuração do email: " . $e->getMessage());
        }
    }
    
    /**
     * Envia relatório de despesas
     */
    public function enviarRelatorio($destinatario, $nomeDestinatario, $relatorio) {
        try {
            $this->mail->clearAddresses();
            $this->mail->addAddress($destinatario, $nomeDestinatario);
            
            $this->mail->isHTML(true);
            $this->mail->Subject = 'Relatório de Despesas - ' . date('m/Y');
            $this->mail->Body = $this->montarHTMLRelatorio($relatorio);
            $this->mail->AltBody = $this->montarTextoRelatorio($relatorio);
            
            $this->mail->send();
            return true;
            
        } catch (Exception $e) {
            throw new Exception("Erro ao enviar email: " . $this->mail->ErrorInfo);
        }
    }
    
    /**
     * Monta HTML do relatório
     */
    private function montarHTMLRelatorio($relatorio) {
        $html = '
        <!DOCTYPE html>
        <html lang="pt-BR">
        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    background-color: #f4f4f4;
                    padding: 20px;
                }
                .container {
                    max-width: 800px;
                    margin: 0 auto;
                    background: white;
                    padding: 30px;
                    border-radius: 10px;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                }
                h1 {
                    color: #2c3e50;
                    border-bottom: 3px solid #3498db;
                    padding-bottom: 10px;
                }
                h2 {
                    color: #34495e;
                    margin-top: 30px;
                }
                .resumo {
                    background: #ecf0f1;
                    padding: 20px;
                    border-radius: 8px;
                    margin: 20px 0;
                }
                .resumo-item {
                    display: flex;
                    justify-content: space-between;
                    padding: 10px 0;
                    border-bottom: 1px solid #bdc3c7;
                }
                .resumo-item:last-child {
                    border-bottom: none;
                    font-weight: bold;
                    font-size: 1.2em;
                    color: #2c3e50;
                }
                .valor-pago {
                    color: #27ae60;
                    font-weight: bold;
                }
                .valor-pendente {
                    color: #e74c3c;
                    font-weight: bold;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin: 20px 0;
                }
                th {
                    background-color: #3498db;
                    color: white;
                    padding: 12px;
                    text-align: left;
                }
                td {
                    padding: 10px;
                    border-bottom: 1px solid #ddd;
                }
                tr:hover {
                    background-color: #f5f5f5;
                }
                .status {
                    padding: 5px 10px;
                    border-radius: 5px;
                    font-size: 0.9em;
                    font-weight: bold;
                }
                .status-pago {
                    background-color: #d4edda;
                    color: #155724;
                }
                .status-pendente {
                    background-color: #fff3cd;
                    color: #856404;
                }
                .status-vencido {
                    background-color: #f8d7da;
                    color: #721c24;
                }
                .footer {
                    margin-top: 30px;
                    padding-top: 20px;
                    border-top: 2px solid #ecf0f1;
                    text-align: center;
                    color: #7f8c8d;
                    font-size: 0.9em;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <h1>📊 Relatório de Despesas</h1>
                <p><strong>Período:</strong> ' . $relatorio['periodo'] . '</p>
                <p><strong>Gerado em:</strong> ' . date('d/m/Y H:i') . '</p>
                
                <div class="resumo">
                    <h2>Resumo Financeiro</h2>
                    <div class="resumo-item">
                        <span>Total de Contas:</span>
                        <span>' . $relatorio['estatisticas']['total_contas'] . '</span>
                    </div>
                    <div class="resumo-item">
                        <span>Contas Pagas:</span>
                        <span>' . $relatorio['estatisticas']['pagas'] . '</span>
                    </div>
                    <div class="resumo-item">
                        <span>Contas Pendentes:</span>
                        <span>' . $relatorio['estatisticas']['pendentes'] . '</span>
                    </div>
                    <div class="resumo-item">
                        <span>Contas Vencidas:</span>
                        <span>' . $relatorio['estatisticas']['vencidas'] . '</span>
                    </div>
                    <div class="resumo-item">
                        <span>Valor Pago:</span>
                        <span class="valor-pago">R$ ' . number_format($relatorio['estatisticas']['valor_pago'], 2, ',', '.') . '</span>
                    </div>
                    <div class="resumo-item">
                        <span>Valor Pendente:</span>
                        <span class="valor-pendente">R$ ' . number_format($relatorio['estatisticas']['valor_pendente'], 2, ',', '.') . '</span>
                    </div>
                    <div class="resumo-item">
                        <span>Valor Total:</span>
                        <span>R$ ' . number_format($relatorio['estatisticas']['valor_total'], 2, ',', '.') . '</span>
                    </div>
                </div>
                
                <h2>Detalhamento de Despesas</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Conta</th>
                            <th>Vencimento</th>
                            <th>Valor</th>
                            <th>Pagamento</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        foreach ($relatorio['despesas'] as $despesa) {
            $statusClass = 'status-' . strtolower($despesa['status']);
            $debito = $despesa['debito_automatico'] ? ' (Déb. Auto)' : '';
            
            $html .= '
                        <tr>
                            <td>' . htmlspecialchars($despesa['nome_conta']) . '</td>
                            <td>' . date('d/m/Y', strtotime($despesa['data_vencimento'])) . '</td>
                            <td>R$ ' . number_format($despesa['valor'], 2, ',', '.') . '</td>
                            <td>' . htmlspecialchars($despesa['modo_pagamento']) . $debito . '</td>
                            <td><span class="status ' . $statusClass . '">' . $despesa['status'] . '</span></td>
                        </tr>';
        }
        
        $html .= '
                    </tbody>
                </table>
                
                <div class="footer">
                    <p>Este é um email automático do Sistema de Gerenciamento de Despesas.</p>
                    <p>Gerado em ' . date('d/m/Y \à\s H:i') . '</p>
                </div>
            </div>
        </body>
        </html>';
        
        return $html;
    }
    
    /**
     * Monta texto simples do relatório
     */
    private function montarTextoRelatorio($relatorio) {
        $texto = "RELATÓRIO DE DESPESAS\n";
        $texto .= "Período: " . $relatorio['periodo'] . "\n";
        $texto .= "Gerado em: " . date('d/m/Y H:i') . "\n\n";
        
        $texto .= "RESUMO FINANCEIRO\n";
        $texto .= "================\n";
        $texto .= "Total de Contas: " . $relatorio['estatisticas']['total_contas'] . "\n";
        $texto .= "Contas Pagas: " . $relatorio['estatisticas']['pagas'] . "\n";
        $texto .= "Contas Pendentes: " . $relatorio['estatisticas']['pendentes'] . "\n";
        $texto .= "Contas Vencidas: " . $relatorio['estatisticas']['vencidas'] . "\n";
        $texto .= "Valor Pago: R$ " . number_format($relatorio['estatisticas']['valor_pago'], 2, ',', '.') . "\n";
        $texto .= "Valor Pendente: R$ " . number_format($relatorio['estatisticas']['valor_pendente'], 2, ',', '.') . "\n";
        $texto .= "Valor Total: R$ " . number_format($relatorio['estatisticas']['valor_total'], 2, ',', '.') . "\n\n";
        
        $texto .= "DETALHAMENTO\n";
        $texto .= "============\n\n";
        
        foreach ($relatorio['despesas'] as $despesa) {
            $texto .= "Conta: " . $despesa['nome_conta'] . "\n";
            $texto .= "Vencimento: " . date('d/m/Y', strtotime($despesa['data_vencimento'])) . "\n";
            $texto .= "Valor: R$ " . number_format($despesa['valor'], 2, ',', '.') . "\n";
            $texto .= "Pagamento: " . $despesa['modo_pagamento'];
            if ($despesa['debito_automatico']) {
                $texto .= " (Débito Automático)";
            }
            $texto .= "\n";
            $texto .= "Status: " . $despesa['status'] . "\n";
            $texto .= "---\n\n";
        }
        
        return $texto;
    }
}
