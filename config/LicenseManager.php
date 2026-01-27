<?php
/**
 * Gerenciador de Licenças - NetoNerd
 * Sistema centralizado para gestão de licenças de produtos
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/bandoDeDados/conexao.php';

class LicenseManager {
    private $conn;

    public function __construct() {
        $this->conn = getConnection();
    }

    /**
     * Gera uma nova API Key única
     * Formato: XXXX-XXXX-XXXX-XXXX (64 caracteres)
     */
    private function gerarApiKey() {
        return bin2hex(random_bytes(32));
    }

    /**
     * Gera uma nova licença para um cliente
     *
     * @param int $produto_id ID do produto
     * @param int $cliente_id ID do cliente
     * @param string $tipo_licenca mensal|anual|vitalicia
     * @param int $vendedor_id ID do técnico/vendedor
     * @return array Dados da licença criada
     */
    public function gerarLicenca($produto_id, $cliente_id, $tipo_licenca = 'mensal', $vendedor_id = null) {
        try {
            // Buscar informações do produto
            $stmt = $this->conn->prepare("SELECT * FROM produtos_licenciaveis WHERE id = ?");
            $stmt->bind_param("i", $produto_id);
            $stmt->execute();
            $produto = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$produto) {
                throw new Exception("Produto não encontrado");
            }

            // Calcular valor da licença
            $valor = 0;
            switch ($tipo_licenca) {
                case 'mensal':
                    $valor = $produto['preco_mensal'];
                    break;
                case 'anual':
                    $valor = $produto['preco_anual'];
                    break;
                case 'vitalicia':
                    $valor = $produto['preco_vitalicio'];
                    break;
            }

            // Gerar API Key única
            do {
                $api_key = $this->gerarApiKey();
                $stmt = $this->conn->prepare("SELECT id FROM licencas WHERE api_key = ?");
                $stmt->bind_param("s", $api_key);
                $stmt->execute();
                $existe = $stmt->get_result()->num_rows > 0;
                $stmt->close();
            } while ($existe);

            // Inserir licença
            $stmt = $this->conn->prepare("
                INSERT INTO licencas
                (api_key, produto_id, cliente_id, tipo_licenca, status, max_instalacoes, valor_licenca, vendedor_id)
                VALUES (?, ?, ?, ?, 'ativa', ?, ?, ?)
            ");

            $max_instalacoes = $produto['max_instalacoes'];
            $stmt->bind_param("siisidi",
                $api_key,
                $produto_id,
                $cliente_id,
                $tipo_licenca,
                $max_instalacoes,
                $valor,
                $vendedor_id
            );

            $stmt->execute();
            $licenca_id = $this->conn->insert_id;
            $stmt->close();

            // Buscar licença completa
            return $this->obterLicenca($licenca_id);

        } catch (Exception $e) {
            error_log("Erro ao gerar licença: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Ativa uma licença (primeira vez que o cliente usa)
     *
     * @param string $api_key
     * @param array $dados_instalacao URL, IP, domínio, etc
     * @return array Resultado da ativação
     */
    public function ativarLicenca($api_key, $dados_instalacao) {
        try {
            // Buscar licença
            $stmt = $this->conn->prepare("
                SELECT l.*, p.dias_trial, p.nome as produto_nome
                FROM licencas l
                INNER JOIN produtos_licenciaveis p ON l.produto_id = p.id
                WHERE l.api_key = ?
            ");
            $stmt->bind_param("s", $api_key);
            $stmt->execute();
            $licenca = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$licenca) {
                return [
                    'sucesso' => false,
                    'mensagem' => 'Licença não encontrada'
                ];
            }

            // Verificar se licença está ativa
            if ($licenca['status'] === 'suspensa') {
                return [
                    'sucesso' => false,
                    'mensagem' => 'Licença suspensa por falta de pagamento. Entre em contato com o suporte.'
                ];
            }

            if ($licenca['status'] === 'cancelada') {
                return [
                    'sucesso' => false,
                    'mensagem' => 'Licença cancelada. Entre em contato com o suporte.'
                ];
            }

            // Verificar número de instalações
            $stmt = $this->conn->prepare("
                SELECT COUNT(*) as total FROM ativacoes_licenca
                WHERE licenca_id = ? AND ativo = 1
            ");
            $stmt->bind_param("i", $licenca['id']);
            $stmt->execute();
            $count = $stmt->get_result()->fetch_assoc()['total'];
            $stmt->close();

            if ($count >= $licenca['max_instalacoes']) {
                return [
                    'sucesso' => false,
                    'mensagem' => 'Número máximo de instalações atingido. Entre em contato com o suporte.'
                ];
            }

            // Verificar se já existe ativação para esta URL
            $stmt = $this->conn->prepare("
                SELECT id FROM ativacoes_licenca
                WHERE licenca_id = ? AND url_instalacao = ?
            ");
            $stmt->bind_param("is", $licenca['id'], $dados_instalacao['url']);
            $stmt->execute();
            $ativacao_existente = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if ($ativacao_existente) {
                // Reativar instalação existente
                $stmt = $this->conn->prepare("
                    UPDATE ativacoes_licenca
                    SET ativo = 1, data_ultima_validacao = NOW()
                    WHERE id = ?
                ");
                $stmt->bind_param("i", $ativacao_existente['id']);
                $stmt->execute();
                $stmt->close();

                $ativacao_id = $ativacao_existente['id'];
            } else {
                // Criar usuário admin para o cliente
                $usuario_admin = $this->gerarUsuarioAdmin($dados_instalacao['url']);
                $senha_temp = bin2hex(random_bytes(8));
                $senha_hash = password_hash($senha_temp, PASSWORD_BCRYPT);

                // Registrar nova ativação
                $stmt = $this->conn->prepare("
                    INSERT INTO ativacoes_licenca
                    (licenca_id, url_instalacao, ip_servidor, dominio, usuario_admin_criado, senha_admin_hash,
                     data_ultima_validacao, versao_produto, sistema_operacional, php_version)
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?)
                ");
                $stmt->bind_param("issssssss",
                    $licenca['id'],
                    $dados_instalacao['url'],
                    $dados_instalacao['ip'] ?? null,
                    $dados_instalacao['dominio'] ?? null,
                    $usuario_admin,
                    $senha_hash,
                    $dados_instalacao['versao'] ?? '1.0.0',
                    $dados_instalacao['os'] ?? null,
                    $dados_instalacao['php_version'] ?? PHP_VERSION
                );
                $stmt->execute();
                $ativacao_id = $this->conn->insert_id;
                $stmt->close();
            }

            // Atualizar licença (primeira ativação)
            if (!$licenca['data_ativacao']) {
                $data_agora = date('Y-m-d H:i:s');
                $data_fim_trial = date('Y-m-d H:i:s', strtotime("+{$licenca['dias_trial']} days"));

                $stmt = $this->conn->prepare("
                    UPDATE licencas
                    SET data_ativacao = ?,
                        data_inicio_trial = ?,
                        data_fim_trial = ?,
                        data_proxima_cobranca = ?,
                        status = 'trial'
                    WHERE id = ?
                ");
                $stmt->bind_param("ssssi", $data_agora, $data_agora, $data_fim_trial, $data_fim_trial, $licenca['id']);
                $stmt->execute();
                $stmt->close();
            }

            // Registrar log
            $this->registrarLog($licenca['id'], $ativacao_id, 'ativacao', 'sucesso',
                'Licença ativada com sucesso', $dados_instalacao['ip'] ?? null);

            // Retornar dados de ativação
            return [
                'sucesso' => true,
                'mensagem' => 'Licença ativada com sucesso!',
                'dados' => [
                    'produto' => $licenca['produto_nome'],
                    'tipo_licenca' => $licenca['tipo_licenca'],
                    'status' => 'trial',
                    'data_fim_trial' => $licenca['data_fim_trial'] ?? $data_fim_trial,
                    'usuario_admin' => $usuario_admin ?? null,
                    'senha_temporaria' => $senha_temp ?? null,
                    'instrucoes' => 'Altere a senha após o primeiro login'
                ]
            ];

        } catch (Exception $e) {
            error_log("Erro ao ativar licença: " . $e->getMessage());
            return [
                'sucesso' => false,
                'mensagem' => 'Erro ao ativar licença: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Valida uma licença (verificação periódica do cliente)
     *
     * @param string $api_key
     * @param string $url_instalacao
     * @return array Status da licença
     */
    public function validarLicenca($api_key, $url_instalacao) {
        try {
            // Buscar licença e ativação
            $stmt = $this->conn->prepare("
                SELECT
                    l.*,
                    p.nome as produto_nome,
                    p.dias_tolerancia_pagamento,
                    a.id as ativacao_id
                FROM licencas l
                INNER JOIN produtos_licenciaveis p ON l.produto_id = p.id
                LEFT JOIN ativacoes_licenca a ON l.id = a.licenca_id
                    AND a.url_instalacao = ? AND a.ativo = 1
                WHERE l.api_key = ?
            ");
            $stmt->bind_param("ss", $url_instalacao, $api_key);
            $stmt->execute();
            $licenca = $stmt->get_result()->fetch_assoc();
            $stmt->close();

            if (!$licenca) {
                return [
                    'valida' => false,
                    'mensagem' => 'Licença não encontrada ou instalação não autorizada'
                ];
            }

            // Atualizar última validação
            if ($licenca['ativacao_id']) {
                $stmt = $this->conn->prepare("
                    UPDATE ativacoes_licenca
                    SET data_ultima_validacao = NOW()
                    WHERE id = ?
                ");
                $stmt->bind_param("i", $licenca['ativacao_id']);
                $stmt->execute();
                $stmt->close();
            }

            // Verificar status
            $status_real = $this->calcularStatusReal($licenca);

            if ($status_real === 'suspensa') {
                $this->registrarLog($licenca['id'], $licenca['ativacao_id'], 'verificacao', 'bloqueada',
                    'Licença suspensa por falta de pagamento');

                return [
                    'valida' => false,
                    'mensagem' => 'Licença suspensa por falta de pagamento. Regularize para continuar usando o sistema.',
                    'dias_atraso' => $this->calcularDiasAtraso($licenca)
                ];
            }

            if ($status_real === 'expirada') {
                $this->registrarLog($licenca['id'], $licenca['ativacao_id'], 'verificacao', 'expirada',
                    'Período trial expirado');

                return [
                    'valida' => false,
                    'mensagem' => 'Período trial expirado. Entre em contato para contratar o plano.'
                ];
            }

            // Licença válida
            $this->registrarLog($licenca['id'], $licenca['ativacao_id'], 'verificacao', 'sucesso',
                'Validação bem-sucedida');

            return [
                'valida' => true,
                'mensagem' => 'Licença válida',
                'dados' => [
                    'produto' => $licenca['produto_nome'],
                    'tipo_licenca' => $licenca['tipo_licenca'],
                    'status' => $status_real,
                    'data_proxima_cobranca' => $licenca['data_proxima_cobranca'],
                    'dias_restantes' => $this->calcularDiasRestantes($licenca)
                ]
            ];

        } catch (Exception $e) {
            error_log("Erro ao validar licença: " . $e->getMessage());
            return [
                'valida' => false,
                'mensagem' => 'Erro ao validar licença'
            ];
        }
    }

    /**
     * Suspende uma licença por falta de pagamento
     */
    public function suspenderLicenca($licenca_id, $motivo = 'Falta de pagamento') {
        $stmt = $this->conn->prepare("UPDATE licencas SET status = 'suspensa' WHERE id = ?");
        $stmt->bind_param("i", $licenca_id);
        $stmt->execute();
        $stmt->close();

        $this->registrarLog($licenca_id, null, 'bloqueio', 'bloqueada', $motivo);
    }

    /**
     * Reativa uma licença após pagamento
     */
    public function reativarLicenca($licenca_id, $proxima_cobranca) {
        $stmt = $this->conn->prepare("
            UPDATE licencas
            SET status = 'ativa', data_proxima_cobranca = ?
            WHERE id = ?
        ");
        $stmt->bind_param("si", $proxima_cobranca, $licenca_id);
        $stmt->execute();
        $stmt->close();

        $this->registrarLog($licenca_id, null, 'desbloqueio', 'sucesso', 'Licença reativada após pagamento');
    }

    /**
     * Obtém dados completos de uma licença
     */
    public function obterLicenca($licenca_id) {
        $stmt = $this->conn->prepare("SELECT * FROM vw_licencas_completas WHERE id = ?");
        $stmt->bind_param("i", $licenca_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    /**
     * Lista todas as licenças
     */
    public function listarLicencas($filtros = []) {
        $sql = "SELECT * FROM vw_licencas_completas WHERE 1=1";
        $params = [];
        $types = '';

        if (!empty($filtros['status'])) {
            $sql .= " AND status = ?";
            $params[] = $filtros['status'];
            $types .= 's';
        }

        if (!empty($filtros['produto_id'])) {
            $sql .= " AND produto_id = ?";
            $params[] = $filtros['produto_id'];
            $types .= 'i';
        }

        $sql .= " ORDER BY data_ativacao DESC";

        $stmt = $this->conn->prepare($sql);
        if ($params) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $licencas = [];
        while ($row = $result->fetch_assoc()) {
            $licencas[] = $row;
        }
        $stmt->close();

        return $licencas;
    }

    // Métodos auxiliares privados

    private function gerarUsuarioAdmin($url) {
        $dominio = parse_url($url, PHP_URL_HOST) ?? 'sistema';
        $dominio = preg_replace('/[^a-z0-9]/i', '', $dominio);
        return 'admin_' . substr($dominio, 0, 10);
    }

    private function calcularStatusReal($licenca) {
        // Licença vitalícia
        if ($licenca['tipo_licenca'] === 'vitalicia') {
            return $licenca['status'] === 'suspensa' ? 'suspensa' : 'ativa';
        }

        $agora = time();

        // Trial expirado
        if ($licenca['status'] === 'trial' && $licenca['data_fim_trial']) {
            if (strtotime($licenca['data_fim_trial']) < $agora) {
                return 'expirada';
            }
            return 'trial';
        }

        // Verificar pagamento atrasado
        if ($licenca['data_proxima_cobranca']) {
            $dias_atraso = $this->calcularDiasAtraso($licenca);
            $tolerancia = $licenca['dias_tolerancia_pagamento'] ?? 7;

            if ($dias_atraso > $tolerancia) {
                return 'suspensa';
            }
        }

        return $licenca['status'];
    }

    private function calcularDiasAtraso($licenca) {
        if (!$licenca['data_proxima_cobranca']) return 0;

        $vencimento = strtotime($licenca['data_proxima_cobranca']);
        $agora = time();

        if ($agora <= $vencimento) return 0;

        return floor(($agora - $vencimento) / 86400);
    }

    private function calcularDiasRestantes($licenca) {
        if ($licenca['tipo_licenca'] === 'vitalicia') {
            return 999999; // Ilimitado
        }

        if (!$licenca['data_proxima_cobranca']) return 0;

        $vencimento = strtotime($licenca['data_proxima_cobranca']);
        $agora = time();

        if ($agora >= $vencimento) return 0;

        return floor(($vencimento - $agora) / 86400);
    }

    private function registrarLog($licenca_id, $ativacao_id, $tipo, $resultado, $mensagem, $ip = null) {
        $ip = $ip ?? $_SERVER['REMOTE_ADDR'] ?? null;
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $stmt = $this->conn->prepare("
            INSERT INTO logs_validacao_licenca
            (licenca_id, ativacao_id, tipo_validacao, resultado, mensagem, ip_origem, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iisssss",
            $licenca_id,
            $ativacao_id,
            $tipo,
            $resultado,
            $mensagem,
            $ip,
            $user_agent
        );
        $stmt->execute();
        $stmt->close();
    }
}
