<?php
/**
 * API de Validação de Licenças - NetoNerd
 * Endpoint para os produtos validarem suas licenças
 *
 * Uso:
 * POST /api/validar_licenca.php
 * Content-Type: application/json
 *
 * Body:
 * {
 *   "api_key": "sua-api-key-aqui",
 *   "acao": "ativar" | "validar",
 *   "url": "https://seusite.com.br",
 *   "ip": "192.168.1.1",
 *   "dominio": "seusite.com.br",
 *   "versao": "1.0.0",
 *   "os": "Linux",
 *   "php_version": "8.2.0"
 * }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/../config/bandoDeDados/conexao.php';
require_once __DIR__ . '/../config/LicenseManager.php';

// Verificar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'sucesso' => false,
        'erro' => 'Método não permitido. Use POST.'
    ]);
    exit;
}

// Ler dados JSON
$json = file_get_contents('php://input');
$dados = json_decode($json, true);

if (!$dados) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'erro' => 'JSON inválido'
    ]);
    exit;
}

// Validar campos obrigatórios
$api_key = $dados['api_key'] ?? '';
$acao = $dados['acao'] ?? 'validar';
$url = $dados['url'] ?? '';

if (!$api_key) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'erro' => 'API Key não informada'
    ]);
    exit;
}

if (!$url) {
    http_response_code(400);
    echo json_encode([
        'sucesso' => false,
        'erro' => 'URL de instalação não informada'
    ]);
    exit;
}

try {
    $licenseManager = new LicenseManager();

    switch ($acao) {
        case 'ativar':
            // Primeira ativação da licença
            $dados_instalacao = [
                'url' => $url,
                'ip' => $dados['ip'] ?? $_SERVER['REMOTE_ADDR'],
                'dominio' => $dados['dominio'] ?? parse_url($url, PHP_URL_HOST),
                'versao' => $dados['versao'] ?? '1.0.0',
                'os' => $dados['os'] ?? PHP_OS,
                'php_version' => $dados['php_version'] ?? PHP_VERSION
            ];

            $resultado = $licenseManager->ativarLicenca($api_key, $dados_instalacao);

            if ($resultado['sucesso']) {
                http_response_code(200);
            } else {
                http_response_code(403);
            }

            echo json_encode($resultado);
            break;

        case 'validar':
            // Validação periódica (sistema já ativado)
            $resultado = $licenseManager->validarLicenca($api_key, $url);

            if ($resultado['valida']) {
                http_response_code(200);
            } else {
                http_response_code(403);
            }

            echo json_encode($resultado);
            break;

        case 'status':
            // Apenas checar status sem atualizar última validação
            $stmt = getConnection()->prepare("
                SELECT
                    l.status,
                    l.tipo_licenca,
                    l.data_proxima_cobranca,
                    p.nome as produto
                FROM licencas l
                INNER JOIN produtos_licenciaveis p ON l.produto_id = p.id
                WHERE l.api_key = ?
            ");
            $stmt->bind_param("s", $api_key);
            $stmt->execute();
            $licenca = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($licenca) {
                echo json_encode([
                    'sucesso' => true,
                    'dados' => $licenca
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'sucesso' => false,
                    'erro' => 'Licença não encontrada'
                ]);
            }
            break;

        default:
            http_response_code(400);
            echo json_encode([
                'sucesso' => false,
                'erro' => 'Ação inválida. Use: ativar, validar ou status'
            ]);
    }

} catch (Exception $e) {
    error_log("Erro na API de validação: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'sucesso' => false,
        'erro' => 'Erro interno no servidor',
        'detalhes' => Config::isDebug() ? $e->getMessage() : null
    ]);
}
?>
