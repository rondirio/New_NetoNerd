<?php
/**
 * JWTHandler V2 - Com suporte a Redis Cache
 * NetoNerd Solutions - Super Admin
 * 
 * @author NetoNerd Development Team
 * @version 2.0.0
 */

namespace NetoNerd\Core;

use Exception;

require_once __DIR__ . '/RedisManager.php';

class JWTHandlerV2
{
    private $secretKey;
    private $algorithm;
    private $issuer;
    private $db;
    private $redis;
    private $accessTokenExpiration = 31536000; // 1 ano
    
    // Flag para ativar/desativar cache
    private $cacheEnabled = true;
    private $cacheTTL = 300; // 5 minutos
    
    /**
     * Construtor
     * 
     * @param mysqli $dbConnection Conexão com banco
     * @param bool $enableCache Habilitar cache Redis
     */
    public function __construct($dbConnection, $enableCache = true)
    {
        $this->db = $dbConnection;
        $this->loadConfig();
        
        // Inicializar Redis se habilitado
        if ($enableCache) {
            try {
                $this->redis = new RedisManager(
                    getenv('REDIS_HOST') ?: '127.0.0.1',
                    getenv('REDIS_PORT') ?: 6379,
                    getenv('REDIS_PASSWORD') ?: null,
                    getenv('REDIS_DB') ?: 0
                );
                
                $this->cacheEnabled = $this->redis->isConnected();
                
                if ($this->cacheEnabled) {
                    error_log("JWTHandler: Redis cache ativado");
                } else {
                    error_log("JWTHandler: Redis não conectado, cache desabilitado");
                }
                
            } catch (Exception $e) {
                error_log("JWTHandler: Erro ao inicializar Redis - " . $e->getMessage());
                $this->cacheEnabled = false;
            }
        } else {
            $this->cacheEnabled = false;
        }
    }
    
    /**
     * Carrega configurações JWT do banco
     */
    private function loadConfig()
    {
        $query = "SELECT * FROM jwt_config WHERE ativo = TRUE ORDER BY id DESC LIMIT 1";
        $result = $this->db->query($query);
        
        if ($result && $config = $result->fetch_assoc()) {
            $this->secretKey = $config['chave_secreta'];
            $this->algorithm = $config['algoritmo'];
            $this->issuer = $config['emissor'];
            $this->accessTokenExpiration = $config['expiracao_access_token'];
        } else {
            throw new Exception("Configuração JWT não encontrada");
        }
    }
    
    /**
     * Gera um JWT Token (mesmo da V1)
     */
    public function generateToken($tenantData)
    {
        $required = ['tenant_id', 'nome_empresa', 'tipo_projeto', 'email_owner', 'plano'];
        foreach ($required as $field) {
            if (!isset($tenantData[$field]) || empty($tenantData[$field])) {
                throw new Exception("Campo obrigatório ausente: {$field}");
            }
        }
        
        $issuedAt = time();
        $expiresAt = $issuedAt + $this->accessTokenExpiration;
        
        $header = [
            'typ' => 'JWT',
            'alg' => $this->algorithm
        ];
        
        $payload = [
            'iss' => $this->issuer,
            'iat' => $issuedAt,
            'exp' => $expiresAt,
            'jti' => $this->generateJti(),
            'tenant_id' => $tenantData['tenant_id'],
            'empresa' => $tenantData['nome_empresa'],
            'projeto' => $tenantData['tipo_projeto'],
            'plano' => $tenantData['plano'],
            'email_owner' => $tenantData['email_owner']
        ];
        
        $base64Header = $this->base64UrlEncode(json_encode($header));
        $base64Payload = $this->base64UrlEncode(json_encode($payload));
        $signature = $this->createSignature($base64Header, $base64Payload);
        
        $jwt = "{$base64Header}.{$base64Payload}.{$signature}";
        $tokenHash = hash('sha256', $jwt);
        
        return [
            'token' => $jwt,
            'token_hash' => $tokenHash,
            'expiration' => $expiresAt,
            'expiration_date' => date('Y-m-d H:i:s', $expiresAt),
            'issued_at' => date('Y-m-d H:i:s', $issuedAt)
        ];
    }
    
    /**
     * Valida token com suporte a cache
     */
    public function validateToken($token, $projectFilter = null)
    {
        try {
            // PASSO 1: Verificar cache primeiro
            if ($this->cacheEnabled) {
                $cachedPayload = $this->redis->getCachedToken($token);
                
                if ($cachedPayload !== null) {
                    // Token encontrado em cache!
                    $this->logValidation(
                        hash('sha256', $token),
                        $cachedPayload['tenant_id'] ?? null,
                        'success',
                        'Validado via cache'
                    );
                    
                    // Incrementar métrica de cache hit
                    $this->redis->incrementMetric('cache_hits');
                    
                    return $cachedPayload;
                }
                
                // Cache miss
                $this->redis->incrementMetric('cache_misses');
            }
            
            // PASSO 2: Validação completa (cache miss ou desabilitado)
            $parts = explode('.', $token);
            
            if (count($parts) !== 3) {
                $this->logValidation(null, null, 'invalid', 'Token mal formatado');
                return false;
            }
            
            list($base64Header, $base64Payload, $signature) = $parts;
            
            // Verificar assinatura
            $validSignature = $this->createSignature($base64Header, $base64Payload);
            
            if (!hash_equals($signature, $validSignature)) {
                $this->logValidation(null, null, 'invalid', 'Assinatura inválida');
                return false;
            }
            
            // Decodificar payload
            $payload = json_decode($this->base64UrlDecode($base64Payload), true);
            
            if (!$payload) {
                $this->logValidation(null, null, 'invalid', 'Payload inválido');
                return false;
            }
            
            // Verificar expiração
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                $this->logValidation(null, $payload['tenant_id'] ?? null, 'expired', 'Token expirado');
                return false;
            }
            
            // PASSO 3: Verificar blacklist (Redis ou DB)
            $tokenHash = hash('sha256', $token);
            
            if ($this->cacheEnabled && $this->redis->isBlacklisted($tokenHash)) {
                $this->logValidation(null, $payload['tenant_id'] ?? null, 'revoked', 'Token na blacklist (cache)');
                return false;
            }
            
            if ($this->isTokenRevoked($tokenHash)) {
                $this->logValidation(null, $payload['tenant_id'] ?? null, 'revoked', 'Token revogado (DB)');
                
                // Adicionar à blacklist do Redis
                if ($this->cacheEnabled) {
                    $ttl = $payload['exp'] - time();
                    if ($ttl > 0) {
                        $this->redis->addToBlacklist($tokenHash, $ttl);
                    }
                }
                
                return false;
            }
            
            // PASSO 4: Verificar filtro de projeto (se fornecido)
            if ($projectFilter && isset($payload['projeto'])) {
                if ($payload['projeto'] !== $projectFilter) {
                    $this->logValidation(
                        $tokenHash,
                        $payload['tenant_id'] ?? null,
                        'invalid',
                        "Projeto incorreto. Esperado: {$projectFilter}, Recebido: {$payload['projeto']}"
                    );
                    return false;
                }
            }
            
            // PASSO 5: Token válido! Cachear resultado
            if ($this->cacheEnabled) {
                $this->redis->cacheValidToken($token, $payload, $this->cacheTTL);
            }
            
            // Registrar uso
            $this->registerTokenUsage($tokenHash);
            
            // Log de sucesso
            $this->logValidation($tokenHash, $payload['tenant_id'] ?? null, 'success', 'Validação bem-sucedida');
            
            // Incrementar métrica
            if ($this->cacheEnabled) {
                $this->redis->incrementMetric('validations_success');
            }
            
            return $payload;
            
        } catch (Exception $e) {
            $this->logValidation(null, null, 'invalid', $e->getMessage());
            return false;
        }
    }
    
    /**
     * Salvar token no banco
     */
    public function saveToken($tenantId, $tokenData)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO jwt_tokens 
            (tenant_id, token_hash, jwt_token, data_expiracao) 
            VALUES (?, ?, ?, ?)"
        );
        
        $expirationDate = date('Y-m-d H:i:s', $tokenData['expiration']);
        
        $stmt->bind_param(
            'isss',
            $tenantId,
            $tokenData['token_hash'],
            $tokenData['token'],
            $expirationDate
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Erro ao salvar token: " . $stmt->error);
        }
        
        return $stmt->insert_id;
    }
    
    /**
     * Revogar token com invalidação de cache
     */
    public function revokeToken($tokenHash, $motivo = 'Revogado manualmente')
    {
        // Revogar no banco
        $stmt = $this->db->prepare(
            "UPDATE jwt_tokens 
            SET revogado = TRUE, data_revogacao = NOW(), motivo_revogacao = ? 
            WHERE token_hash = ?"
        );
        
        $stmt->bind_param('ss', $motivo, $tokenHash);
        $success = $stmt->execute();
        
        if ($success && $this->cacheEnabled) {
            // Adicionar à blacklist do Redis
            // Buscar token para pegar expiração
            $stmt = $this->db->prepare(
                "SELECT jwt_token, data_expiracao FROM jwt_tokens WHERE token_hash = ? LIMIT 1"
            );
            $stmt->bind_param('s', $tokenHash);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $ttl = strtotime($row['data_expiracao']) - time();
                if ($ttl > 0) {
                    $this->redis->addToBlacklist($tokenHash, $ttl);
                }
                
                // Invalidar cache do token
                $this->redis->invalidateToken($row['jwt_token']);
            }
        }
        
        return $success;
    }
    
    /**
     * Verificar se token foi revogado (DB)
     */
    private function isTokenRevoked($tokenHash)
    {
        $stmt = $this->db->prepare(
            "SELECT revogado FROM jwt_tokens WHERE token_hash = ? LIMIT 1"
        );
        
        $stmt->bind_param('s', $tokenHash);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return (bool)$row['revogado'];
        }
        
        return false;
    }
    
    /**
     * Registrar uso do token
     */
    private function registerTokenUsage($tokenHash)
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        $stmt = $this->db->prepare(
            "UPDATE jwt_tokens 
            SET ultimo_uso = NOW(), ip_ultimo_uso = ? 
            WHERE token_hash = ?"
        );
        
        $stmt->bind_param('ss', $ipAddress, $tokenHash);
        $stmt->execute();
    }
    
    /**
     * Log de validação
     */
    private function logValidation($tokenHash, $tenantId, $status, $mensagem)
    {
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
        $projeto = $_SERVER['HTTP_X_PROJETO'] ?? 'unknown';
        
        // Buscar IDs internos
        $tokenId = null;
        if ($tokenHash) {
            $stmt = $this->db->prepare("SELECT id FROM jwt_tokens WHERE token_hash = ? LIMIT 1");
            $stmt->bind_param('s', $tokenHash);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $tokenId = $row['id'];
            }
        }
        
        $tenantInternalId = null;
        if ($tenantId) {
            $stmt = $this->db->prepare("SELECT id FROM tenants WHERE tenant_id = ? LIMIT 1");
            $stmt->bind_param('s', $tenantId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $tenantInternalId = $row['id'];
            }
        }
        
        $stmt = $this->db->prepare(
            "INSERT INTO jwt_validation_logs 
            (token_id, tenant_id, projeto, status, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?)"
        );
        
        $stmt->bind_param('iissss', $tokenId, $tenantInternalId, $projeto, $status, $ipAddress, $userAgent);
        $stmt->execute();
    }
    
    // Métodos auxiliares (mesmos da V1)
    private function createSignature($base64Header, $base64Payload)
    {
        $data = "{$base64Header}.{$base64Payload}";
        
        switch ($this->algorithm) {
            case 'HS256':
                $signature = hash_hmac('sha256', $data, $this->secretKey, true);
                break;
            case 'HS512':
                $signature = hash_hmac('sha512', $data, $this->secretKey, true);
                break;
            default:
                throw new Exception("Algoritmo não suportado: {$this->algorithm}");
        }
        
        return $this->base64UrlEncode($signature);
    }
    
    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
    
    private function base64UrlDecode($data)
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
    
    private function generateJti()
    {
        return bin2hex(random_bytes(16));
    }
    
    /**
     * Obter estatísticas com métricas do Redis
     */
    public function getTokenStatistics()
    {
        $query = "
            SELECT 
                COUNT(*) as total_tokens,
                SUM(CASE WHEN revogado = FALSE AND data_expiracao > NOW() THEN 1 ELSE 0 END) as tokens_ativos,
                SUM(CASE WHEN revogado = TRUE THEN 1 ELSE 0 END) as tokens_revogados,
                SUM(CASE WHEN data_expiracao < NOW() THEN 1 ELSE 0 END) as tokens_expirados
            FROM jwt_tokens
        ";
        
        $stats = $this->db->query($query)->fetch_assoc();
        
        // Adicionar métricas do Redis se disponível
        if ($this->cacheEnabled) {
            $stats['cache_enabled'] = true;
            $stats['cache_hits'] = $this->redis->getMetrics('cache_hits', 1)[date('Y-m-d')] ?? 0;
            $stats['cache_misses'] = $this->redis->getMetrics('cache_misses', 1)[date('Y-m-d')] ?? 0;
            $stats['validations_success'] = $this->redis->getMetrics('validations_success', 1)[date('Y-m-d')] ?? 0;
            
            // Calcular taxa de cache hit
            $total = $stats['cache_hits'] + $stats['cache_misses'];
            $stats['cache_hit_rate'] = $total > 0 ? round(($stats['cache_hits'] / $total) * 100, 2) : 0;
            
            $stats['redis_info'] = $this->redis->getInfo();
        } else {
            $stats['cache_enabled'] = false;
        }
        
        return $stats;
    }
    
    /**
     * Obter Redis Manager
     */
    public function getRedis()
    {
        return $this->redis;
    }
}