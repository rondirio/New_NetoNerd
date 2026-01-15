<?php
/**
 * Job Automático - Verificação de Licenças e Pagamentos
 * NetoNerd ITSM
 *
 * Este script deve ser executado diariamente via CRON
 * Exemplo de CRON (diariamente às 9h):
 * 0 9 * * * /usr/bin/php /caminho/para/New_NetoNerd/cron/verificar_licencas.php
 */

require_once __DIR__ . '/../config/bandoDeDados/conexao.php';
require_once __DIR__ . '/../config/LicenseManager.php';
require_once __DIR__ . '/../config/EmailService.php';

echo "[" . date('Y-m-d H:i:s') . "] Iniciando verificação de licenças...\n";

$conn = getConnection();
$licenseManager = new LicenseManager();
$emailService = new EmailService();

try {
    // ============================================================
    // 1. VERIFICAR TRIALS EXPIRADOS
    // ============================================================
    echo "Verificando trials expirados...\n";

    $stmt = $conn->prepare("
        SELECT l.*, c.nome as cliente_nome, c.email as cliente_email, p.nome as produto_nome
        FROM licencas l
        INNER JOIN clientes c ON l.cliente_id = c.id
        INNER JOIN produtos_licenciaveis p ON l.produto_id = p.id
        WHERE l.status = 'trial'
          AND l.data_fim_trial < NOW()
    ");
    $stmt->execute();
    $trials_expirados = $stmt->get_result();

    $count_trials = 0;
    while ($licenca = $trials_expirados->fetch_assoc()) {
        // Atualizar status para expirado
        $stmt2 = $conn->prepare("UPDATE licencas SET status = 'expirada' WHERE id = ?");
        $stmt2->bind_param("i", $licenca['id']);
        $stmt2->execute();
        $stmt2->close();

        // Enviar email para cliente
        enviarEmailTrialExpirado($emailService, $licenca);

        $count_trials++;
        echo "  - Licença #{$licenca['id']} ({$licenca['produto_nome']}) de {$licenca['cliente_nome']}: EXPIRADA\n";
    }
    $stmt->close();

    echo "Total de trials expirados: $count_trials\n\n";

    // ============================================================
    // 2. VERIFICAR PAGAMENTOS ATRASADOS (PERÍODO DE TOLERÂNCIA)
    // ============================================================
    echo "Verificando pagamentos atrasados...\n";

    $stmt = $conn->prepare("
        SELECT
            l.*,
            c.nome as cliente_nome,
            c.email as cliente_email,
            c.telefone as cliente_telefone,
            p.nome as produto_nome,
            p.dias_tolerancia_pagamento,
            DATEDIFF(NOW(), l.data_proxima_cobranca) as dias_atraso
        FROM licencas l
        INNER JOIN clientes c ON l.cliente_id = c.id
        INNER JOIN produtos_licenciaveis p ON l.produto_id = p.id
        WHERE l.status = 'ativa'
          AND l.tipo_licenca != 'vitalicia'
          AND l.data_proxima_cobranca < NOW()
    ");
    $stmt->execute();
    $pagamentos_atrasados = $stmt->get_result();

    $count_avisos = 0;
    $count_suspensas = 0;

    while ($licenca = $pagamentos_atrasados->fetch_assoc()) {
        $dias_atraso = $licenca['dias_atraso'];
        $tolerancia = $licenca['dias_tolerancia_pagamento'];

        if ($dias_atraso > $tolerancia) {
            // SUSPENDER LICENÇA
            $licenseManager->suspenderLicenca($licenca['id'], "Pagamento atrasado há {$dias_atraso} dias");

            enviarEmailLicencaSuspensa($emailService, $licenca, $dias_atraso);

            $count_suspensas++;
            echo "  - Licença #{$licenca['id']} ({$licenca['produto_nome']}) de {$licenca['cliente_nome']}: SUSPENSA ({$dias_atraso} dias de atraso)\n";

        } elseif ($dias_atraso > 0) {
            // ENVIAR AVISO
            enviarEmailAvisoVencimento($emailService, $licenca, $dias_atraso, $tolerancia);

            $count_avisos++;
            echo "  - Licença #{$licenca['id']} ({$licenca['produto_nome']}) de {$licenca['cliente_nome']}: AVISO ({$dias_atraso}/{$tolerancia} dias)\n";
        }
    }
    $stmt->close();

    echo "Total de avisos enviados: $count_avisos\n";
    echo "Total de licenças suspensas: $count_suspensas\n\n";

    // ============================================================
    // 3. VERIFICAR PRÓXIMOS VENCIMENTOS (7 DIAS)
    // ============================================================
    echo "Verificando próximos vencimentos...\n";

    $stmt = $conn->prepare("
        SELECT
            l.*,
            c.nome as cliente_nome,
            c.email as cliente_email,
            p.nome as produto_nome,
            DATEDIFF(l.data_proxima_cobranca, NOW()) as dias_restantes
        FROM licencas l
        INNER JOIN clientes c ON l.cliente_id = c.id
        INNER JOIN produtos_licenciaveis p ON l.produto_id = p.id
        WHERE l.status = 'ativa'
          AND l.tipo_licenca != 'vitalicia'
          AND l.data_proxima_cobranca BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)
          AND NOT EXISTS (
              SELECT 1 FROM logs_validacao_licenca
              WHERE licenca_id = l.id
                AND tipo_validacao = 'renovacao'
                AND DATE(data_validacao) = CURDATE()
          )
    ");
    $stmt->execute();
    $proximos_vencimentos = $stmt->get_result();

    $count_lembretes = 0;
    while ($licenca = $proximos_vencimentos->fetch_assoc()) {
        enviarEmailLembreteVencimento($emailService, $licenca);

        // Registrar que o lembrete foi enviado hoje
        $stmt2 = $conn->prepare("
            INSERT INTO logs_validacao_licenca
            (licenca_id, tipo_validacao, resultado, mensagem)
            VALUES (?, 'renovacao', 'sucesso', ?)
        ");
        $mensagem = "Lembrete de vencimento enviado ({$licenca['dias_restantes']} dias)";
        $stmt2->bind_param("is", $licenca['id'], $mensagem);
        $stmt2->execute();
        $stmt2->close();

        $count_lembretes++;
        echo "  - Licença #{$licenca['id']} ({$licenca['produto_nome']}) de {$licenca['cliente_nome']}: LEMBRETE ({$licenca['dias_restantes']} dias)\n";
    }
    $stmt->close();

    echo "Total de lembretes enviados: $count_lembretes\n\n";

    // ============================================================
    // 4. RELATÓRIO FINAL
    // ============================================================
    echo "==============================================\n";
    echo "Verificação concluída com sucesso!\n";
    echo "==============================================\n";
    echo "Trials expirados: $count_trials\n";
    echo "Avisos enviados: $count_avisos\n";
    echo "Licenças suspensas: $count_suspensas\n";
    echo "Lembretes enviados: $count_lembretes\n";
    echo "==============================================\n";

    // Registrar execução no log
    $total_acoes = $count_trials + $count_avisos + $count_suspensas + $count_lembretes;
    error_log("CRON Verificação de Licenças executado: {$total_acoes} ações realizadas");

} catch (Exception $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    error_log("Erro no CRON de verificação de licenças: " . $e->getMessage());
}

$conn->close();

// ============================================================
// FUNÇÕES DE EMAIL
// ============================================================

function enviarEmailTrialExpirado($emailService, $licenca) {
    $assunto = "[NetoNerd] Período Trial do {$licenca['produto_nome']} Expirado";
    $corpo = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #dc3545;'>⏰ Período Trial Expirado</h2>
            <p>Olá <strong>{$licenca['cliente_nome']}</strong>,</p>
            <p>O período de trial de 30 dias do <strong>{$licenca['produto_nome']}</strong> expirou.</p>
            <p>Para continuar usando o sistema, entre em contato conosco para contratar um plano:</p>
            <ul>
                <li>📧 Email: suporte@netonerd.com.br</li>
                <li>📱 WhatsApp: (21) 97739-5867</li>
            </ul>
            <p>Estamos à disposição para te ajudar!</p>
        </div>
    </body>
    </html>";

    $emailService->enviarEmail($licenca['cliente_email'], $licenca['cliente_nome'], $assunto, $corpo);
}

function enviarEmailAvisoVencimento($emailService, $licenca, $dias_atraso, $tolerancia) {
    $dias_restantes = $tolerancia - $dias_atraso;
    $assunto = "[NetoNerd] Pagamento em Atraso - {$licenca['produto_nome']}";

    $corpo = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #ffc107;'>⚠️ Pagamento em Atraso</h2>
            <p>Olá <strong>{$licenca['cliente_nome']}</strong>,</p>
            <p>O pagamento da sua licença do <strong>{$licenca['produto_nome']}</strong> está atrasado há <strong>{$dias_atraso} dia(s)</strong>.</p>
            <div style='background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin: 20px 0;'>
                <p style='margin: 0;'><strong>Atenção:</strong> Você tem mais <strong>{$dias_restantes} dia(s)</strong> para regularizar o pagamento.</p>
                <p style='margin: 10px 0 0 0;'>Após este prazo, sua licença será suspensa automaticamente e o sistema ficará indisponível.</p>
            </div>
            <p><strong>Valor:</strong> R$ " . number_format($licenca['valor_licenca'], 2, ',', '.') . "</p>
            <p>Para regularizar, entre em contato:</p>
            <ul>
                <li>📧 Email: financeiro@netonerd.com.br</li>
                <li>📱 WhatsApp: (21) 97739-5867</li>
            </ul>
        </div>
    </body>
    </html>";

    $emailService->enviarEmail($licenca['cliente_email'], $licenca['cliente_nome'], $assunto, $corpo);
}

function enviarEmailLicencaSuspensa($emailService, $licenca, $dias_atraso) {
    $assunto = "[NetoNerd] Licença Suspensa - {$licenca['produto_nome']}";

    $corpo = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #dc3545;'>🚫 Licença Suspensa</h2>
            <p>Olá <strong>{$licenca['cliente_nome']}</strong>,</p>
            <p>Sua licença do <strong>{$licenca['produto_nome']}</strong> foi suspensa devido ao pagamento atrasado há <strong>{$dias_atraso} dia(s)</strong>.</p>
            <div style='background: #f8d7da; border-left: 4px solid #dc3545; padding: 15px; margin: 20px 0;'>
                <p style='margin: 0;'><strong>O sistema está indisponível no momento.</strong></p>
            </div>
            <p><strong>Valor em atraso:</strong> R$ " . number_format($licenca['valor_licenca'], 2, ',', '.') . "</p>
            <p>Para reativar sua licença, regularize o pagamento entrando em contato:</p>
            <ul>
                <li>📧 Email: financeiro@netonerd.com.br</li>
                <li>📱 WhatsApp: (21) 97739-5867</li>
            </ul>
            <p>Após a confirmação do pagamento, reativaremos sua licença imediatamente.</p>
        </div>
    </body>
    </html>";

    $emailService->enviarEmail($licenca['cliente_email'], $licenca['cliente_nome'], $assunto, $corpo);
}

function enviarEmailLembreteVencimento($emailService, $licenca) {
    $dias = $licenca['dias_restantes'];
    $assunto = "[NetoNerd] Lembrete: Vencimento em {$dias} dia(s) - {$licenca['produto_nome']}";

    $corpo = "
    <html>
    <body style='font-family: Arial, sans-serif;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2 style='color: #17a2b8;'>🔔 Lembrete de Vencimento</h2>
            <p>Olá <strong>{$licenca['cliente_nome']}</strong>,</p>
            <p>Sua licença do <strong>{$licenca['produto_nome']}</strong> vence em <strong>{$dias} dia(s)</strong>.</p>
            <div style='background: #d1ecf1; border-left: 4px solid #17a2b8; padding: 15px; margin: 20px 0;'>
                <p style='margin: 0;'><strong>Vencimento:</strong> " . date('d/m/Y', strtotime($licenca['data_proxima_cobranca'])) . "</p>
                <p style='margin: 10px 0 0 0;'><strong>Valor:</strong> R$ " . number_format($licenca['valor_licenca'], 2, ',', '.') . "</p>
            </div>
            <p>Para evitar a suspensão do serviço, efetue o pagamento até a data de vencimento.</p>
            <p>Dúvidas? Entre em contato:</p>
            <ul>
                <li>📧 Email: financeiro@netonerd.com.br</li>
                <li>📱 WhatsApp: (21) 97739-5867</li>
            </ul>
        </div>
    </body>
    </html>";

    $emailService->enviarEmail($licenca['cliente_email'], $licenca['cliente_nome'], $assunto, $corpo);
}
?>
