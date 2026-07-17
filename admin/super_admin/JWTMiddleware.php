<?php
/**
 * JWT Middleware - Validação de Tokens nos Projetos NetoNerd
 * 
 * Este middleware deve ser incluído em TODOS os projetos:
 * - MyHealth
 * - BarberShop Manager
 * - New_NetoNerd (Suporte TI)
 * 
 * @author NetoNerd Development Team
 * @version 1.0.0
 */

namespace NetoNerd\Middleware;

class JWTMiddleware
{
    private $superAdminApiUrl;
    private $projectName;
    private $tokenData;
    
    /**
     * Construtor
     * 
     * @param string $superAdminApiUrl URL da API do SuperAdmin
     * @param string $projectName Nome do projeto (myhealth, barbershop, suporte_ti)
     */
    public function __construct($superAdminApiUrl, $projectName)
    {
        $this->superAdminApiUrl = rtrim($superAdminApiUrl, '/');
        $this->projectName = $projectName;
    }
    
    /**
     * Valida o token JWT
     * Pode ser chamado no início de cada requisição protegida
     * 
     * @return array|false Retorna dados do token ou false se inválido
     */
    public function authenticate()
    {
        // Extrair token do header Authorization
        $token = $this->extractTokenFromRequest();
        
        if (!$token) {
            $this->respondUnauthorized('Token de autenticação não fornecido');
            return false;
        }
        
        // Validar token com API do SuperAdmin
        $validation = $this->validateWithSuperAdmin($token);
        
        if (!$validation['valid']) {
            $this->respondUnauthorized($validation['message']);
            return false;
        }
        
        // Salvar dados do token
        $this->tokenData = $validation['data'];
        
        // Verificar se o projeto corresponde
        if ($this->tokenData['projeto'] !== $this->projectName) {
            $this->respondUnauthorized(
                "Token inválido para este projeto. " .
                "Este token é do projeto '{$this->tokenData['projeto']}', " .
                "mas você está tentando acessar '{$this->projectName}'"
            );
            return false;
        }
        
        return $this->tokenData;
    }
    
    /**
     * Extrai token do header Authorization ou query string
     * 
     * @return string|null
     */
    private function extractTokenFromRequest()
    {
        // Tentar pegar do header Authorization
        $headers = $this->getRequestHeaders();
        
        if (isset($headers['Authorization'])) {
            // Remover "Bearer " se presente
            return str_replace('Bearer ', '', $headers['Authorization']);
        }
        
        if (isset($headers['authorization'])) {
            return str_replace('Bearer ', '', $headers['authorization']);
        }
        
        // Query string só é aceita em ambiente de desenvolvimento explícito —
        // um token de 1 ano de validade nessa posição vaza via logs de
        // acesso, histórico de navegador, cache de proxy e header Referer.
        if (isset($_GET['token']) && $this->isDevelopmentEnvironment()) {
            return $_GET['token'];
        }

        return null;
    }

    /**
     * Verifica se o ambiente atual é de desenvolvimento, via variável de
     * ambiente do projeto que inclui este middleware (não depende de
     * nenhuma classe Config específica, já que é reusado entre projetos).
     * @return bool
     */
    private function isDevelopmentEnvironment()
    {
        $appEnv = getenv('APP_ENV') ?: ($_ENV['APP_ENV'] ?? '');
        return in_array(strtolower($appEnv), ['local', 'development', 'dev'], true);
    }
    
    /**
     * Valida token com a API do SuperAdmin
     * 
     * @param string $token
     * @return array ['valid' => bool, 'message' => string, 'data' => array|null]
     */
    private function validateWithSuperAdmin($token)
    {
        $url = $this->superAdminApiUrl . '/validate';
        
        // Preparar payload
        $payload = json_encode(['token' => $token]);
        
        // Configurar cURL
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-Projeto: ' . $this->projectName
            ],
            CURLOPT_TIMEOUT => 5, // Timeout de 5 segundos
            CURLOPT_CONNECTTIMEOUT => 3
        ]);
        
        // Executar requisição
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        // Verificar erro de conexão
        if ($response === false) {
            error_log("JWT Middleware Error: Não foi possível conectar ao SuperAdmin API - {$curlError}");
            return [
                'valid' => false,
                'message' => 'Erro ao validar token (servidor de autenticação indisponível)',
                'data' => null
            ];
        }
        
        // Decodificar resposta
        $data = json_decode($response, true);
        
        // Verificar resposta
        if ($httpCode === 200 && isset($data['valid']) && $data['valid'] === true) {
            return [
                'valid' => true,
                'message' => 'Token válido',
                'data' => $data['data']
            ];
        }
        
        return [
            'valid' => false,
            'message' => $data['message'] ?? 'Token inválido ou expirado',
            'data' => null
        ];
    }
    
    /**
     * Obtém os dados do token após autenticação bem-sucedida
     * 
     * @return array|null
     */
    public function getTokenData()
    {
        return $this->tokenData;
    }
    
    /**
     * Obtém o Tenant ID do token
     * 
     * @return string|null
     */
    public function getTenantId()
    {
        return $this->tokenData['tenant_id'] ?? null;
    }
    
    /**
     * Obtém o nome da empresa do token
     * 
     * @return string|null
     */
    public function getEmpresa()
    {
        return $this->tokenData['empresa'] ?? null;
    }
    
    /**
     * Obtém o plano do token
     * 
     * @return string|null
     */
    public function getPlano()
    {
        return $this->tokenData['plano'] ?? null;
    }
    
    /**
     * Verifica se o plano tem determinado recurso
     * 
     * @param string $feature Nome do recurso
     * @return bool
     */
    public function hasFeature($feature)
    {
        $plano = $this->getPlano();
        
        // Definir recursos por plano
        $plansFeatures = [
            'basico' => ['usuarios_5', 'armazenamento_1gb', 'suporte_email'],
            'profissional' => ['usuarios_20', 'armazenamento_10gb', 'suporte_email', 'suporte_chat', 'relatorios_basicos'],
            'premium' => ['usuarios_100', 'armazenamento_50gb', 'suporte_24x7', 'relatorios_avancados', 'api_access', 'white_label'],
            'enterprise' => ['usuarios_ilimitado', 'armazenamento_ilimitado', 'suporte_dedicado', 'relatorios_customizados', 'api_access', 'white_label', 'sla_garantido']
        ];
        
        if (!isset($plansFeatures[$plano])) {
            return false;
        }
        
        return in_array($feature, $plansFeatures[$plano]);
    }
    
    /**
     * Responde com erro 401 Unauthorized
     * 
     * @param string $message
     */
    private function respondUnauthorized($message)
    {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error' => 'Unauthorized',
            'message' => $message
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Obtém headers da requisição (compatível com diferentes servidores)
     * 
     * @return array
     */
    private function getRequestHeaders()
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }
        
        // Fallback para servidores que não têm getallheaders()
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $headerKey = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$headerKey] = $value;
            }
        }
        
        return $headers;
    }
    
    /**
     * Middleware opcional: Rate Limiting por tenant
     * Previne abuso da API
     * 
     * @param int $maxRequests Número máximo de requisições
     * @param int $timeWindow Janela de tempo em segundos
     * @return bool
     */
    public function checkRateLimit($maxRequests = 100, $timeWindow = 60)
    {
        $tenantId = $this->getTenantId();
        if (!$tenantId) {
            return false;
        }
        
        // Usar arquivo para rate limit simples (em produção, usar Redis ou Memcached)
        $rateLimitFile = sys_get_temp_dir() . "/jwt_rate_limit_{$tenantId}.json";
        
        $now = time();
        $requests = [];
        
        // Carregar histórico de requisições
        if (file_exists($rateLimitFile)) {
            $data = json_decode(file_get_contents($rateLimitFile), true);
            $requests = $data['requests'] ?? [];
        }
        
        // Remover requisições fora da janela de tempo
        $requests = array_filter($requests, function($timestamp) use ($now, $timeWindow) {
            return ($now - $timestamp) < $timeWindow;
        });
        
        // Verificar se excedeu o limite
        if (count($requests) >= $maxRequests) {
            $this->respondRateLimitExceeded();
            return false;
        }
        
        // Adicionar nova requisição
        $requests[] = $now;
        
        // Salvar
        file_put_contents($rateLimitFile, json_encode(['requests' => $requests]));
        
        return true;
    }
    
    /**
     * Responde com erro 429 Too Many Requests
     */
    private function respondRateLimitExceeded()
    {
        http_response_code(429);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'error' => 'Rate Limit Exceeded',
            'message' => 'Você excedeu o limite de requisições. Tente novamente em alguns instantes.'
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    /**
     * Log de acesso (opcional)
     * 
     * @param string $action Ação executada
     * @param array $additionalData Dados adicionais
     */
    public function logAccess($action, $additionalData = [])
    {
        $logData = [
            'timestamp' => date('Y-m-d H:i:s'),
            'tenant_id' => $this->getTenantId(),
            'empresa' => $this->getEmpresa(),
            'projeto' => $this->projectName,
            'action' => $action,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'additional_data' => $additionalData
        ];
        
        // Salvar em arquivo de log (em produção, enviar para sistema de logs centralizado)
        $logFile = __DIR__ . "/../../logs/jwt_access_{$this->projectName}.log";
        file_put_contents($logFile, json_encode($logData) . PHP_EOL, FILE_APPEND);
    }
}