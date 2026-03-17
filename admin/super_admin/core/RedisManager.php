<?php
/**
 * Redis Manager - Sistema de Cache para JWT
 * NetoNerd Solutions - Super Admin
 * 
 * @author NetoNerd Development Team
 * @version 2.0.0
 */

namespace NetoNerd\Core;

use Redis;
use Exception;

class RedisManager
{
    private $redis;
    private $connected = false;
    private $prefix = 'netonerd_jwt:';
    
    // Configurações
    private $host;
    private $port;
    private $password;
    private $database;
    
    /**
     * Construtor
     * 
     * @param string $host Host do Redis
     * @param int $port Porta do Redis
     * @param string $password Senha (opcional)
     * @param int $database Database index
     */
    public function __construct(
        $host = '127.0.0.1',
        $port = 6379,
        $password = null,
        $database = 0
    ) {
        $this->host = $host;
        $this->port = $port;
        $this->password = $password;
        $this->database = $database;
        
        $this->connect();
    }
    
    /**
     * Conectar ao Redis
     */
    private function connect()
    {
        try {
            if (!extension_loaded('redis')) {
                throw new Exception('Extensão Redis não está instalada');
            }
            
            $this->redis = new Redis();
            
            // Tentar conectar
            $connected = $this->redis->connect(
                $this->host,
                $this->port,
                2.5  // Timeout de 2.5 segundos
            );
            
            if (!$connected) {
                throw new Exception('Não foi possível conectar ao Redis');
            }
            
            // Autenticar se necessário
            if ($this->password !== null) {
                $this->redis->auth($this->password);
            }
            
            // Selecionar database
            $this->redis->select($this->database);
            
            $this->connected = true;
            
        } catch (Exception $e) {
            error_log("Redis Connection Error: " . $e->getMessage());
            $this->connected = false;
        }
    }
    
    /**
     * Verifica se está conectado
     * 
     * @return bool
     */
    public function isConnected()
    {
        return $this->connected;
    }
    
    /**
     * Cache de token válido
     * 
     * @param string $token Token JWT
     * @param array $payload Dados do token
     * @param int $ttl Tempo de vida em segundos (300 = 5 minutos)
     * @return bool
     */
    public function cacheValidToken($token, $payload, $ttl = 300)
    {
        if (!$this->connected) return false;
        
        try {
            $key = $this->prefix . 'valid:' . hash('sha256', $token);
            $value = json_encode($payload);
            
            return $this->redis->setex($key, $ttl, $value);
            
        } catch (Exception $e) {
            error_log("Redis Cache Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Buscar token em cache
     * 
     * @param string $token Token JWT
     * @return array|null Payload do token ou null se não encontrado
     */
    public function getCachedToken($token)
    {
        if (!$this->connected) return null;
        
        try {
            $key = $this->prefix . 'valid:' . hash('sha256', $token);
            $value = $this->redis->get($key);
            
            if ($value === false) {
                return null;
            }
            
            return json_decode($value, true);
            
        } catch (Exception $e) {
            error_log("Redis Get Error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Invalidar cache de um token
     * 
     * @param string $token Token JWT
     * @return bool
     */
    public function invalidateToken($token)
    {
        if (!$this->connected) return false;
        
        try {
            $key = $this->prefix . 'valid:' . hash('sha256', $token);
            return $this->redis->del($key) > 0;
            
        } catch (Exception $e) {
            error_log("Redis Delete Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Cache de blacklist de tokens revogados
     * 
     * @param string $tokenHash Hash do token
     * @param int $ttl Tempo até expiração natural do token
     * @return bool
     */
    public function addToBlacklist($tokenHash, $ttl = 31536000)
    {
        if (!$this->connected) return false;
        
        try {
            $key = $this->prefix . 'blacklist:' . $tokenHash;
            return $this->redis->setex($key, $ttl, '1');
            
        } catch (Exception $e) {
            error_log("Redis Blacklist Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verificar se token está na blacklist
     * 
     * @param string $tokenHash Hash do token
     * @return bool
     */
    public function isBlacklisted($tokenHash)
    {
        if (!$this->connected) return false;
        
        try {
            $key = $this->prefix . 'blacklist:' . $tokenHash;
            return $this->redis->exists($key);
            
        } catch (Exception $e) {
            error_log("Redis Blacklist Check Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Rate Limiting por IP
     * 
     * @param string $ip Endereço IP
     * @param int $maxRequests Máximo de requisições
     * @param int $window Janela de tempo em segundos
     * @return array ['allowed' => bool, 'remaining' => int, 'reset' => timestamp]
     */
    public function checkRateLimit($ip, $maxRequests = 100, $window = 60)
    {
        if (!$this->connected) {
            return ['allowed' => true, 'remaining' => $maxRequests, 'reset' => time() + $window];
        }
        
        try {
            $key = $this->prefix . 'ratelimit:ip:' . $ip;
            
            // Incrementar contador
            $current = $this->redis->incr($key);
            
            // Se é a primeira requisição, definir expiração
            if ($current === 1) {
                $this->redis->expire($key, $window);
            }
            
            // Obter TTL restante
            $ttl = $this->redis->ttl($key);
            $reset = time() + ($ttl > 0 ? $ttl : $window);
            
            // Verificar se excedeu limite
            $allowed = $current <= $maxRequests;
            $remaining = max(0, $maxRequests - $current);
            
            return [
                'allowed' => $allowed,
                'remaining' => $remaining,
                'reset' => $reset,
                'current' => $current
            ];
            
        } catch (Exception $e) {
            error_log("Redis Rate Limit Error: " . $e->getMessage());
            return ['allowed' => true, 'remaining' => $maxRequests, 'reset' => time() + $window];
        }
    }
    
    /**
     * Rate Limiting por Tenant
     * 
     * @param string $tenantId ID do tenant
     * @param int $maxRequests Máximo de requisições
     * @param int $window Janela de tempo em segundos
     * @return array
     */
    public function checkTenantRateLimit($tenantId, $maxRequests = 1000, $window = 3600)
    {
        if (!$this->connected) {
            return ['allowed' => true, 'remaining' => $maxRequests, 'reset' => time() + $window];
        }
        
        try {
            $key = $this->prefix . 'ratelimit:tenant:' . $tenantId;
            
            $current = $this->redis->incr($key);
            
            if ($current === 1) {
                $this->redis->expire($key, $window);
            }
            
            $ttl = $this->redis->ttl($key);
            $reset = time() + ($ttl > 0 ? $ttl : $window);
            
            $allowed = $current <= $maxRequests;
            $remaining = max(0, $maxRequests - $current);
            
            return [
                'allowed' => $allowed,
                'remaining' => $remaining,
                'reset' => $reset,
                'current' => $current
            ];
            
        } catch (Exception $e) {
            error_log("Redis Tenant Rate Limit Error: " . $e->getMessage());
            return ['allowed' => true, 'remaining' => $maxRequests, 'reset' => time() + $window];
        }
    }
    
    /**
     * Armazenar estatísticas em tempo real
     * 
     * @param string $metric Nome da métrica
     * @param int $value Valor
     * @param int $ttl Tempo de vida
     */
    public function incrementMetric($metric, $value = 1, $ttl = 86400)
    {
        if (!$this->connected) return false;
        
        try {
            $key = $this->prefix . 'metrics:' . $metric . ':' . date('Y-m-d');
            $this->redis->incrBy($key, $value);
            $this->redis->expire($key, $ttl);
            
            return true;
            
        } catch (Exception $e) {
            error_log("Redis Metrics Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obter estatísticas
     * 
     * @param string $metric Nome da métrica
     * @param int $days Número de dias para buscar
     * @return array
     */
    public function getMetrics($metric, $days = 7)
    {
        if (!$this->connected) return [];
        
        try {
            $metrics = [];
            
            for ($i = 0; $i < $days; $i++) {
                $date = date('Y-m-d', strtotime("-{$i} days"));
                $key = $this->prefix . 'metrics:' . $metric . ':' . $date;
                $value = $this->redis->get($key);
                
                $metrics[$date] = $value !== false ? (int)$value : 0;
            }
            
            return array_reverse($metrics);
            
        } catch (Exception $e) {
            error_log("Redis Get Metrics Error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Limpar todo o cache
     * 
     * @return bool
     */
    public function flushCache()
    {
        if (!$this->connected) return false;
        
        try {
            // Buscar todas as chaves com nosso prefixo
            $keys = $this->redis->keys($this->prefix . '*');
            
            if (!empty($keys)) {
                return $this->redis->del($keys) > 0;
            }
            
            return true;
            
        } catch (Exception $e) {
            error_log("Redis Flush Error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Obter informações do Redis
     * 
     * @return array
     */
    public function getInfo()
    {
        if (!$this->connected) {
            return ['connected' => false];
        }
        
        try {
            $info = $this->redis->info();
            
            return [
                'connected' => true,
                'version' => $info['redis_version'] ?? 'unknown',
                'used_memory' => $info['used_memory_human'] ?? 'unknown',
                'total_keys' => $this->redis->dbSize(),
                'uptime_days' => isset($info['uptime_in_seconds']) 
                    ? round($info['uptime_in_seconds'] / 86400, 1) 
                    : 0
            ];
            
        } catch (Exception $e) {
            error_log("Redis Info Error: " . $e->getMessage());
            return ['connected' => true, 'error' => $e->getMessage()];
        }
    }
    
    /**
     * Fechar conexão
     */
    public function close()
    {
        if ($this->connected && $this->redis) {
            $this->redis->close();
            $this->connected = false;
        }
    }
    
    /**
     * Destrutor
     */
    public function __destruct()
    {
        $this->close();
    }
}