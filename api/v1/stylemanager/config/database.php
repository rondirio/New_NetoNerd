<?php
/**
 * StyleManager API - Database Configuration
 *
 * Gerencia conexões multi-tenant com bancos de dados.
 * Cada estabelecimento tem seu próprio banco de dados isolado.
 *
 * A configuração dos clientes está em clientes.php
 */

class StyleManagerDatabase {
    private static $instances = [];
    private static $config = null;
    private $conn;
    private $apiKey;
    private $estabelecimento;

    /**
     * Carrega a configuração de clientes
     */
    private static function loadConfig(): array {
        if (self::$config === null) {
            self::$config = require __DIR__ . '/clientes.php';
        }
        return self::$config;
    }

    /**
     * Obtém configurações globais
     */
    public static function getGlobalConfig(): array {
        $config = self::loadConfig();
        return $config['config'] ?? [];
    }

    /**
     * Obtém instância do banco para uma API Key específica
     */
    public static function getInstance(string $apiKey): ?self {
        if (!isset(self::$instances[$apiKey])) {
            $instance = new self();
            if ($instance->connect($apiKey)) {
                self::$instances[$apiKey] = $instance;
            } else {
                return null;
            }
        }
        return self::$instances[$apiKey];
    }

    /**
     * Busca configuração do banco pela API Key no arquivo de configuração
     */
    private function getDbConfig(string $apiKey): ?array {
        $config = self::loadConfig();
        $clientes = $config['clientes'] ?? [];

        if (!isset($clientes[$apiKey])) {
            return null;
        }

        $cliente = $clientes[$apiKey];

        return [
            'host' => $cliente['db_host'],
            'name' => $cliente['db_name'],
            'user' => $cliente['db_user'],
            'pass' => $cliente['db_pass'],
            'port' => $cliente['db_port'] ?? 3306,
            'estabelecimento' => $cliente['estabelecimento']
        ];
    }

    /**
     * Conecta ao banco do estabelecimento
     */
    private function connect(string $apiKey): bool {
        $this->apiKey = $apiKey;

        $config = $this->getDbConfig($apiKey);

        if (!$config) {
            error_log("StyleManager: API Key não encontrada: $apiKey");
            return false;
        }

        $this->estabelecimento = $config['estabelecimento'];
        $this->estabelecimento['api_key'] = $apiKey;

        $this->conn = new mysqli(
            $config['host'],
            $config['user'],
            $config['pass'],
            $config['name'],
            $config['port']
        );

        if ($this->conn->connect_error) {
            error_log("StyleManager DB Connection Error: " . $this->conn->connect_error);
            return false;
        }

        $this->conn->set_charset('utf8mb4');
        return true;
    }

    /**
     * Obtém a conexão mysqli
     */
    public function getConnection(): mysqli {
        return $this->conn;
    }

    /**
     * Obtém dados do estabelecimento
     */
    public function getEstabelecimento(): array {
        return $this->estabelecimento;
    }

    /**
     * Obtém a API Key
     */
    public function getApiKey(): string {
        return $this->apiKey;
    }

    /**
     * Busca usuário por email em qualquer estabelecimento configurado
     * Usado para login sem API Key pré-configurada
     */
    public static function findUserByEmail(string $email): ?array {
        $config = self::loadConfig();
        $clientes = $config['clientes'] ?? [];

        // Para cada cliente configurado, tenta encontrar o usuário
        foreach ($clientes as $apiKey => $clienteConfig) {
            $db = self::getInstance($apiKey);
            if (!$db) continue;

            $conn = $db->getConnection();

            // Busca na tabela usuarios
            $stmt = $conn->prepare("
                SELECT id, nome, email, senha, tipo, telefone, foto, ativo
                FROM usuarios
                WHERE email = ? AND ativo = 1
                LIMIT 1
            ");

            if (!$stmt) continue;

            $stmt->bind_param('s', $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if ($user) {
                return [
                    'user' => $user,
                    'api_key' => $apiKey,
                    'estabelecimento' => $db->getEstabelecimento()
                ];
            }
        }

        return null;
    }

    /**
     * Lista todas as API Keys configuradas (para debug)
     */
    public static function listApiKeys(): array {
        $config = self::loadConfig();
        $clientes = $config['clientes'] ?? [];

        $keys = [];
        foreach ($clientes as $apiKey => $cliente) {
            $keys[] = [
                'api_key' => $apiKey,
                'estabelecimento' => $cliente['estabelecimento']['nome'] ?? 'N/A'
            ];
        }

        return $keys;
    }

    /**
     * Fecha a conexão
     */
    public function close(): void {
        if ($this->conn) {
            $this->conn->close();
        }
    }

    /**
     * Destructor
     */
    public function __destruct() {
        $this->close();
    }
}
