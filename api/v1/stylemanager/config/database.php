<?php
/**
 * StyleManager API - Database Configuration
 *
 * Gerencia conexões multi-tenant com bancos de dados na Hostinger.
 * Cada estabelecimento tem seu próprio banco de dados isolado.
 */

class StyleManagerDatabase {
    private static $instances = [];
    private $conn;
    private $apiKey;
    private $estabelecimento;

    // Configurações dos bancos de dados por API Key
    // Em produção, isso viria de uma tabela no NetoNerd
    private static $databases = [
        // Exemplo de configuração (será populado do banco de licenças)
        // 'api_key_aqui' => [
        //     'host' => 'localhost',
        //     'name' => 'cliente1_stylemanager',
        //     'user' => 'cliente1_user',
        //     'pass' => 'senha_segura',
        //     'estabelecimento' => [
        //         'id' => 1,
        //         'nome' => 'Salão Exemplo',
        //         'logo' => null
        //     ]
        // ]
    ];

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
     * Busca configuração do banco pela API Key no NetoNerd
     */
    private function getDbConfigFromLicense(string $apiKey): ?array {
        // Conecta ao banco principal do NetoNerd para buscar a licença
        $netonerdConn = $this->getNetonerdConnection();
        if (!$netonerdConn) {
            return null;
        }

        // Busca a licença ativa
        $stmt = $netonerdConn->prepare("
            SELECT l.*, a.url_instalacao, a.ip_servidor, a.dominio,
                   p.nome as produto_nome, p.slug as produto_slug
            FROM licencas l
            LEFT JOIN ativacoes_licenca a ON l.id = a.licenca_id AND a.ativo = 1
            LEFT JOIN produtos_licenciaveis p ON l.produto_id = p.id
            WHERE l.api_key = ?
            AND l.status IN ('ativa', 'trial')
            AND p.slug = 'stylemanager'
            LIMIT 1
        ");

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param('s', $apiKey);
        $stmt->execute();
        $result = $stmt->get_result();
        $license = $result->fetch_assoc();
        $stmt->close();

        if (!$license) {
            return null;
        }

        // Busca configuração do banco do estabelecimento
        // Isso pode vir de uma tabela de configurações ou ser derivado do domínio
        $dbConfig = $this->getEstabelecimentoDbConfig($license);

        $netonerdConn->close();

        return $dbConfig;
    }

    /**
     * Obtém configuração do banco do estabelecimento
     * Em produção, isso viria de uma tabela de configurações
     */
    private function getEstabelecimentoDbConfig(array $license): ?array {
        // Por enquanto, usa configuração baseada no domínio
        // Em produção, isso seria uma tabela de mapeamento

        $domain = $license['dominio'] ?? '';

        // Configuração padrão para desenvolvimento/teste
        // Em produção, cada cliente terá suas credenciais próprias
        return [
            'host' => getenv('STYLEMANAGER_DB_HOST') ?: 'localhost',
            'name' => getenv('STYLEMANAGER_DB_NAME') ?: 'stylemanager_' . md5($license['api_key']),
            'user' => getenv('STYLEMANAGER_DB_USER') ?: 'stylemanager',
            'pass' => getenv('STYLEMANAGER_DB_PASS') ?: '',
            'port' => getenv('STYLEMANAGER_DB_PORT') ?: 3306,
            'estabelecimento' => [
                'id' => $license['id'],
                'nome' => $domain ?: 'StyleManager',
                'api_key' => $license['api_key'],
                'plano' => $license['tipo_licenca'] ?? 'basico',
                'status' => $license['status']
            ]
        ];
    }

    /**
     * Conexão com o banco principal do NetoNerd
     */
    private function getNetonerdConnection(): ?mysqli {
        $host = getenv('DB_HOST') ?: 'localhost';
        $user = getenv('DB_USERNAME') ?: 'root';
        $pass = getenv('DB_PASSWORD') ?: '';
        $name = getenv('DB_NAME') ?: 'netonerd_chamados';
        $port = getenv('DB_PORT') ?: 3306;

        $conn = new mysqli($host, $user, $pass, $name, $port);

        if ($conn->connect_error) {
            error_log("NetoNerd DB Connection Error: " . $conn->connect_error);
            return null;
        }

        $conn->set_charset('utf8mb4');
        return $conn;
    }

    /**
     * Conecta ao banco do estabelecimento
     */
    private function connect(string $apiKey): bool {
        $this->apiKey = $apiKey;

        // Primeiro tenta cache estático
        if (isset(self::$databases[$apiKey])) {
            $config = self::$databases[$apiKey];
        } else {
            // Busca do banco de licenças
            $config = $this->getDbConfigFromLicense($apiKey);
            if ($config) {
                self::$databases[$apiKey] = $config;
            }
        }

        if (!$config) {
            return false;
        }

        $this->estabelecimento = $config['estabelecimento'];

        $this->conn = new mysqli(
            $config['host'],
            $config['user'],
            $config['pass'],
            $config['name'],
            $config['port'] ?? 3306
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
     * Busca usuário por email em qualquer estabelecimento
     * Usado para login sem API Key pré-configurada
     */
    public static function findUserByEmail(string $email): ?array {
        // Conecta ao NetoNerd para buscar a licença do usuário
        $netonerdConn = (new self())->getNetonerdConnection();
        if (!$netonerdConn) {
            return null;
        }

        // Busca todas as licenças ativas do StyleManager
        $stmt = $netonerdConn->prepare("
            SELECT l.api_key, l.status, a.url_instalacao, a.dominio
            FROM licencas l
            JOIN ativacoes_licenca a ON l.id = a.licenca_id AND a.ativo = 1
            JOIN produtos_licenciaveis p ON l.produto_id = p.id
            WHERE l.status IN ('ativa', 'trial')
            AND p.slug = 'stylemanager'
        ");

        if (!$stmt) {
            $netonerdConn->close();
            return null;
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $licenses = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        $netonerdConn->close();

        // Para cada licença, tenta conectar e buscar o usuário
        foreach ($licenses as $license) {
            $db = self::getInstance($license['api_key']);
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
                    'api_key' => $license['api_key'],
                    'estabelecimento' => $db->getEstabelecimento()
                ];
            }
        }

        return null;
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
