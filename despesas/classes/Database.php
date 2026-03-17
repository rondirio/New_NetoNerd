<?php
class Database {
    private $conn;
    
    public function __construct() {
        try {
            require_once __DIR__ . '/../config/database.php';
            
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $this->conn = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch(PDOException $e) {
            throw new Exception("Erro na conexão: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    public function close() {
        $this->conn = null;
    }
}
