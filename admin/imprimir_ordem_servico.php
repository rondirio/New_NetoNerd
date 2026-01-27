<?php
/**
 * Imprimir Ordem de Serviço - NetoNerd ITSM v2.0
 */
session_start();
require_once '../controller/auth_middleware.php';
require_once '../config/bandoDeDados/conexao.php';

requireAdmin();

$conn = getConnection();

// Validar ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Ordem de Serviço inválida');
}

$os_id = intval($_GET['id']);

// Buscar dados da OS
$sql = "
    SELECT 
        os.*,
        t.nome as tecnico_nome,
        t.matricula as tecnico_matricula,
        t.email as tecnico_email,
        tc.nome as criado_por_nome,
        tc.matricula as criado_por_matricula
    FROM ordens_servico os
    INNER JOIN tecnicos t ON os.tecnico_id = t.id
    INNER JOIN tecnicos tc ON os.created_by = tc.id
    WHERE os.id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $os_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die('Ordem de Serviço não encontrada');
}

$os = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Determinar cor do status
$status_color = match($os['status']) {
    'aberta' => '#007bff',
    'em_andamento' => '#17a2b8',
    'concluida' => '#28a745',
    'cancelada' => '#dc3545',
    default => '#6c757d'
};

$status_texto = match($os['status']) {
    'aberta' => 'ABERTA',
    'em_andamento' => 'EM ANDAMENTO',
    'concluida' => 'CONCLUÍDA',
    'cancelada' => 'CANCELADA',
    default => strtoupper($os['status'])
};
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OS <?= htmlspecialchars($os['numero_os']) ?> - NetoNerd ITSM</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
            background: white;
            color: #333;
            line-height: 1.6;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
        }

        /* Cabeçalho */
        .header {
            border-bottom: 4px solid #007bff;
            padding-bottom: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .header-left h1 {
            color: #007bff;
            font-size: 28px;
            margin-bottom: 5px;
        }

        .header-left p {
            font-size: 12px;
            color: #666;
            margin: 2px 0;
        }

        .header-right {
            text-align: right;
        }

        .os-number {
            background: #007bff;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            text-align: center;
        }

        .os-number strong {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .os-number span {
            font-size: 24px;
            font-weight: bold;
        }

        /* Info Bar */
        .info-bar {
            display: flex;
            border: 1px solid #dee2e6;
            margin-bottom: 20px;
            background: #f8f9fa;
        }

        .info-item {
            flex: 1;
            padding: 12px;
            border-right: 1px solid #dee2e6;
        }

        .info-item:last-child {
            border-right: none;
            text-align: center;
        }

        .info-item strong {
            display: block;
            font-size: 11px;
            color: #666;
            margin-bottom: 4px;
        }

        .info-item span {
            display: block;
            font-size: 13px;
            color: #333;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 5px;
            color: white;
            font-weight: bold;
            font-size: 13px;
        }

        /* Seções */
        .section {
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            overflow: hidden;
        }

        .section-header {
            background: #007bff;
            color: white;
            padding: 10px 15px;
            font-weight: bold;
            font-size: 14px;
        }

        .section-header.green {
            background: #28a745;
        }

        .section-header.yellow {
            background: #ffc107;
            color: #000;
        }

        .section-header.teal {
            background: #17a2b8;
        }

        .section-header.gray {
            background: #6c757d;
        }

        .section-content {
            padding: 15px;
            background: #f8f9fa;
        }

        .section-row {
            display: flex;
            margin-bottom: 10px;
        }

        .section-row:last-child {
            margin-bottom: 0;
        }

        .section-col {
            flex: 1;
            padding-right: 15px;
        }

        .section-col:last-child {
            padding-right: 0;
        }

        .section-col strong {
            display: block;
            font-size: 12px;
            color: #666;
            margin-bottom: 3px;
        }

        .section-col span {
            display: block;
            font-size: 14px;
            color: #333;
        }

        .section-text {
            padding: 15px;
            background: white;
            border-top: 1px solid #dee2e6;
            white-space: pre-wrap;
            text-align: justify;
        }

        /* Valores */
        .valores-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 2px solid #007bff;
        }

        .valores-table thead {
            background: #007bff;
            color: white;
        }

        .valores-table th {
            padding: 12px;
            text-align: center;
            font-size: 14px;
        }

        .valores-table td {
            padding: 12px;
            border-top: 1px solid #dee2e6;
        }

        .valores-table td:last-child {
            text-align: right;
            font-weight: bold;
        }

        .valores-table .total-row {
            background: #28a745;
            color: white;
            font-size: 16px;
            font-weight: bold;
        }

        /* Assinaturas */
        .assinaturas {
            display: flex;
            margin-top: 50px;
            gap: 30px;
        }

        .assinatura-box {
            flex: 1;
            text-align: center;
        }

        .assinatura-linha {
            border-top: 2px solid #333;
            margin-top: 60px;
            padding-top: 8px;
        }

        .assinatura-box strong {
            display: block;
            font-size: 14px;
            margin-bottom: 3px;
        }

        .assinatura-box small {
            font-size: 12px;
            color: #666;
        }

        /* Rodapé */
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 11px;
            color: #666;
        }

        /* Botão Imprimir */
        .no-print {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn-print {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-print:hover {
            background: #0056b3;
        }

        /* Print Styles */
        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .container {
                max-width: 100%;
            }

            .section {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        
        <!-- Botão Imprimir (não aparece na impressão) -->
        <div class="no-print">
            <button class="btn-print" onclick="window.print()">
                🖨️ Imprimir Ordem de Serviço
            </button>
        </div>

        <!-- Cabeçalho -->
        <div class="header">
            <div class="header-left">
                <h1>NetoNerd</h1>
                <p>R. Conselheiro Macedo Soares, 354 Sala 216 - Centro - Araruama/RJ</p>
                <p>Tel: (21) 977395867 | Email: netonerdinterno@gmail.com</p>
                <p>CNPJ: 51.243.583/0001-12</p>
            </div>
            <div class="header-right">
                <div class="os-number">
                    <strong>ORDEM DE SERVIÇO</strong>
                    <span><?= htmlspecialchars($os['numero_os']) ?></span>
                </div>
            </div>
        </div>

        <!-- Barra de Informações -->
        <div class="info-bar">
            <div class="info-item">
                <strong>DATA DE ABERTURA</strong>
                <span><?= date('d/m/Y H:i', strtotime($os['data_criacao'])) ?></span>
            </div>
            <div class="info-item">
                <strong>TÉCNICO RESPONSÁVEL</strong>
                <span><?= htmlspecialchars($os['tecnico_nome']) ?></span>
                <span style="font-size: 11px; color: #666;"><?= htmlspecialchars($os['tecnico_matricula']) ?></span>
            </div>
            <div class="info-item">
                <strong>STATUS</strong>
                <span class="status-badge" style="background: <?= $status_color ?>;">
                    <?= $status_texto ?>
                </span>
            </div>
        </div>

        <!-- Dados do Cliente -->
        <div class="section">
            <div class="section-header">
                👤 DADOS DO CLIENTE
            </div>
            <div class="section-content">
                <div class="section-row">
                    <div class="section-col">
                        <strong>Nome Completo:</strong>
                        <span><?= htmlspecialchars($os['cliente_nome']) ?></span>
                    </div>
                    <div class="section-col">
                        <strong>Telefone:</strong>
                        <span><?= htmlspecialchars($os['cliente_telefone'] ?: 'Não informado') ?></span>
                    </div>
                </div>
                <div class="section-row">
                    <div class="section-col">
                        <strong>Email:</strong>
                        <span><?= htmlspecialchars($os['cliente_email'] ?: 'Não informado') ?></span>
                    </div>
                    <div class="section-col">
                        <strong>Endereço:</strong>
                        <span><?= htmlspecialchars($os['cliente_endereco'] ?: 'Não informado') ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dados do Equipamento -->
        <?php if ($os['equipamento_tipo']): ?>
        <div class="section">
            <div class="section-header green">
                💻 DADOS DO EQUIPAMENTO
            </div>
            <div class="section-content">
                <div class="section-row">
                    <div class="section-col">
                        <strong>Tipo:</strong>
                        <span><?= htmlspecialchars($os['equipamento_tipo']) ?></span>
                    </div>
                    <div class="section-col">
                        <strong>Marca:</strong>
                        <span><?= htmlspecialchars($os['equipamento_marca'] ?: 'Não informado') ?></span>
                    </div>
                </div>
                <div class="section-row">
                    <div class="section-col">
                        <strong>Modelo:</strong>
                        <span><?= htmlspecialchars($os['equipamento_modelo'] ?: 'Não informado') ?></span>
                    </div>
                    <div class="section-col">
                        <strong>Número de Série:</strong>
                        <span><?= htmlspecialchars($os['equipamento_serial'] ?: 'Não informado') ?></span>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Problema Relatado -->
        <div class="section">
            <div class="section-header yellow">
                📋 PROBLEMA RELATADO
            </div>
            <div class="section-text">
                <?= nl2br(htmlspecialchars($os['problema_relatado'])) ?>
            </div>
        </div>

        <!-- Serviços Executados -->
        <?php if ($os['servicos_executados']): ?>
        <div class="section">
            <div class="section-header teal">
                🔧 SERVIÇOS EXECUTADOS
            </div>
            <div class="section-text">
                <?= nl2br(htmlspecialchars($os['servicos_executados'])) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Peças Utilizadas -->
        <?php if ($os['pecas_utilizadas']): ?>
        <div class="section">
            <div class="section-header gray">
                ⚙️ PEÇAS UTILIZADAS
            </div>
            <div class="section-text">
                <?= nl2br(htmlspecialchars($os['pecas_utilizadas'])) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Observações -->
        <?php if ($os['observacoes']): ?>
        <div class="section">
            <div class="section-header gray">
                💬 OBSERVAÇÕES
            </div>
            <div class="section-text">
                <?= nl2br(htmlspecialchars($os['observacoes'])) ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Valores -->
        <table class="valores-table">
            <thead>
                <tr>
                    <th colspan="2">VALORES</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Mão de Obra</td>
                    <td>R$ <?= number_format($os['valor_mao_obra'], 2, ',', '.') ?></td>
                </tr>
                <tr>
                    <td>Peças</td>
                    <td>R$ <?= number_format($os['valor_pecas'], 2, ',', '.') ?></td>
                </tr>
                <tr class="total-row">
                    <td>VALOR TOTAL</td>
                    <td>R$ <?= number_format($os['valor_total'], 2, ',', '.') ?></td>
                </tr>
            </tbody>
        </table>

        <!-- Assinaturas -->
        <div class="assinaturas">
            <div class="assinatura-box">
                <div class="assinatura-linha">
                    <strong>Assinatura do Técnico</strong>
                    <small><?= htmlspecialchars($os['tecnico_nome']) ?></small>
                </div>
            </div>
            <div class="assinatura-box">
                <div class="assinatura-linha">
                    <strong>Assinatura do Cliente</strong>
                    <small><?= htmlspecialchars($os['cliente_nome']) ?></small>
                </div>
            </div>
        </div>

        <!-- Rodapé -->
        <div class="footer">
            <p>NetoNerd Informática - Documento gerado em <?= date('d/m/Y H:i:s') ?></p>
            <p>Este documento tem validade jurídica e comprova a prestação de serviços</p>
        </div>

    </div>

    <script>
        // Auto-imprimir ao carregar (opcional)
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>