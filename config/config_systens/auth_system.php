<?php
/**
 * Sistema de Autenticação Seguro - NetoNerd
 * Versão 2.0 - Com proteção contra ataques comuns
 */

class AuthSystem {
    private $conn;
    private $max_attempts = 5;
    private $lockout_time = 900; // 15 minutos em segundos
    
    public function __construct($connection) {
        $this->conn = $connection;
        $this->createSecurityTables();
    }
    
    /**
     * Cria tabelas de segurança se não existirem
     */
    private function createSecurityTables() {
        $sql = "CREATE TABLE IF NOT EXISTS login_attempts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            identificador VARCHAR(255) NOT NULL,
            tipo_usuario ENUM('cliente', 'tecnico') NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            tentativa_data TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            sucesso BOOLEAN DEFAULT FALSE,
            INDEX idx_identificador (identificador),
            INDEX idx_ip (ip_address),
            INDEX idx_data (tentativa_data)
        )";
        
        $this->conn->query($sql);
        
        $sql2 = "CREATE TABLE IF NOT EXISTS sessoes_ativas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario_id INT NOT NULL,
            tipo_usuario ENUM('cliente', 'tecnico', 'admin') NOT NULL,
            session_token VARCHAR(255) UNIQUE NOT NULL,
            ip_address VARCHAR(45) NOT NULL,
            user_agent VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            expires_at TIMESTAMP NOT NULL,
            last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_token (session_token),
            INDEX idx_usuario (usuario_id, tipo_usuario)
        )";
        
        $this->conn->query($sql2);
    }
    
    /**
     * Verifica se o usuário está bloqueado por tentativas excessivas
     */
    private function isLockedOut($identificador, $ip_address) {
        $time_ago = date('Y-m-d H:i:s', time() - $this->lockout_time);
        
        $stmt = $this->conn->prepare(
            "SELECT COUNT(*) as attempts FROM login_attempts 
             WHERE (identificador = ? OR ip_address = ?) 
             AND tentativa_data > ? 
             AND sucesso = FALSE"
        );
        $stmt->bind_param("sss", $identificador, $ip_address, $time_ago);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        return $result['attempts'] >= $this->max_attempts;
    }
    
    /**
     * Registra tentativa de login
     */
    private function logAttempt($identificador, $tipo, $ip, $sucesso) {
        $stmt = $this->conn->prepare(
            "INSERT INTO login_attempts (identificador, tipo_usuario, ip_address, sucesso) 
             VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("sssi", $identificador, $tipo, $ip, $sucesso);
        $stmt->execute();
    }
    
    /**
     * Limpa tentativas antigas de login
     */
    private function cleanOldAttempts($identificador) {
        $time_ago = date('Y-m-d H:i:s', time() - $this->lockout_time);
        $stmt = $this->conn->prepare(
            "DELETE FROM login_attempts WHERE identificador = ? AND tentativa_data < ?"
        );
        $stmt->bind_param("ss", $identificador, $time_ago);
        $stmt->execute();
    }
    
    /**
     * Gera token de sessão seguro
     */
    private function generateSessionToken() {
        return bin2hex(random_bytes(32));
    }
    
    /**
     * Cria sessão segura
     */
    private function createSecureSession($usuario_id, $tipo_usuario, $usuario_dados) {
        $token = $this->generateSessionToken();
        $ip = $_SERVER['REMOTE_ADDR'];
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $expires = date('Y-m-d H:i:s', time() + 28800); // 8 horas
        
        // Remove sessões antigas do usuário
        $stmt = $this->conn->prepare(
            "DELETE FROM sessoes_ativas WHERE usuario_id = ? AND tipo_usuario = ?"
        );
        $stmt->bind_param("is", $usuario_id, $tipo_usuario);
        $stmt->execute();
        
        // Cria nova sessão
        $stmt = $this->conn->prepare(
            "INSERT INTO sessoes_ativas (usuario_id, tipo_usuario, session_token, ip_address, user_agent, expires_at) 
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("isssss", $usuario_id, $tipo_usuario, $token, $ip, $user_agent, $expires);
        $stmt->execute();
        
        // Configura sessão PHP
        session_start();
        session_regenerate_id(true);
        $_SESSION['auth_token'] = $token;
        $_SESSION['usuario_id'] = $usuario_id;
        $_SESSION['tipo_usuario'] = $tipo_usuario;
        $_SESSION['usuario_nome'] = $usuario_dados['nome'];
        $_SESSION['last_activity'] = time();
        
        return $token;
    }
    
    /**
     * Login de Cliente
     */
    public function loginCliente($email, $senha) {
        $ip = $_SERVER['REMOTE_ADDR'];
        
        // Verifica bloqueio
        if ($this->isLockedOut($email, $ip)) {
            return [
                'sucesso' => false,
                'erro' => 'Muitas tentativas de login. Tente novamente em 15 minutos.',
                'bloqueado' => true
            ];
        }
        
        // Busca cliente
        $stmt = $this->conn->prepare(
            "SELECT id, nome, email, senha_hash, ativo, genero FROM clientes WHERE email = ? AND ativo = TRUE"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $this->logAttempt($email, 'cliente', $ip, false);
            return ['sucesso' => false, 'erro' => 'Credenciais inválidas.'];
        }
        
        $cliente = $result->fetch_assoc();
        
        // Verifica senha
        if (!password_verify($senha, $cliente['senha_hash'])) {
            $this->logAttempt($email, 'cliente', $ip, false);
            return ['sucesso' => false, 'erro' => 'Credenciais inválidas.'];
        }
        
        // Login bem-sucedido
        $this->logAttempt($email, 'cliente', $ip, true);
        $this->cleanOldAttempts($email);
        $token = $this->createSecureSession($cliente['id'], 'cliente', $cliente);
        
        return [
            'sucesso' => true,
            'usuario' => [
                'id' => $cliente['id'],
                'nome' => $cliente['nome'],
                'email' => $cliente['email'],
                'genero' => $cliente['genero']
            ],
            'redirect' => 'home.php'
        ];
    }
    
    /**
     * Login de Técnico/Admin
     */
    public function loginTecnico($matricula, $senha) {
        $ip = $_SERVER['REMOTE_ADDR'];
        
        // Verifica bloqueio
        if ($this->isLockedOut($matricula, $ip)) {
            return [
                'sucesso' => false,
                'erro' => 'Muitas tentativas de login. Tente novamente em 15 minutos.',
                'bloqueado' => true
            ];
        }
        
        // Busca técnico
        $stmt = $this->conn->prepare(
            "SELECT id, nome, email, matricula, senha_hash, status_tecnico, carro_do_dia 
             FROM tecnicos 
             WHERE matricula = ? AND Ativo = TRUE"
        );
        $stmt->bind_param("s", $matricula);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $this->logAttempt($matricula, 'tecnico', $ip, false);
            return ['sucesso' => false, 'erro' => 'Credenciais inválidas.'];
        }
        
        $tecnico = $result->fetch_assoc();
        
        // Verifica se está ativo
        if ($tecnico['status_tecnico'] !== 'Ativo') {
            return [
                'sucesso' => false, 
                'erro' => 'Sua conta está inativa. Contate a gerência.'
            ];
        }
        
        // Verifica senha
        if (!password_verify($senha, $tecnico['senha_hash'])) {
            $this->logAttempt($matricula, 'tecnico', $ip, false);
            return ['sucesso' => false, 'erro' => 'Credenciais inválidas.'];
        }
        
        // Determina tipo (admin ou técnico)
        $tipo_usuario = (strpos($matricula, 'ADM') !== false) ? 'admin' : 'tecnico';
        
        // Login bem-sucedido
        $this->logAttempt($matricula, 'tecnico', $ip, true);
        $this->cleanOldAttempts($matricula);
        $token = $this->createSecureSession($tecnico['id'], $tipo_usuario, $tecnico);
        
        $_SESSION['matricula'] = $tecnico['matricula'];
        $_SESSION['carro_do_dia'] = $tecnico['carro_do_dia'];
        
        return [
            'sucesso' => true,
            'usuario' => [
                'id' => $tecnico['id'],
                'nome' => $tecnico['nome'],
                'matricula' => $tecnico['matricula'],
                'tipo' => $tipo_usuario
            ],
            'redirect' => ($tipo_usuario === 'admin') ? 'dashboard.php' : 'paineltecnico.php'
        ];
    }
    
    /**
     * Valida sessão ativa
     */
    public function validarSessao() {
        session_start();
        
        if (!isset($_SESSION['auth_token'])) {
            return false;
        }
        
        $token = $_SESSION['auth_token'];
        $ip = $_SERVER['REMOTE_ADDR'];
        
        $stmt = $this->conn->prepare(
            "SELECT s.*, 
                    CASE 
                        WHEN s.tipo_usuario = 'cliente' THEN c.nome
                        WHEN s.tipo_usuario IN ('tecnico', 'admin') THEN t.nome
                    END as nome
             FROM sessoes_ativas s
             LEFT JOIN clientes c ON s.usuario_id = c.id AND s.tipo_usuario = 'cliente'
             LEFT JOIN tecnicos t ON s.usuario_id = t.id AND s.tipo_usuario IN ('tecnico', 'admin')
             WHERE s.session_token = ? 
             AND s.ip_address = ?
             AND s.expires_at > NOW()"
        );
        $stmt->bind_param("ss", $token, $ip);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $this->logout();
            return false;
        }
        
        $sessao = $result->fetch_assoc();
        
        // Atualiza última atividade
        $stmt = $this->conn->prepare(
            "UPDATE sessoes_ativas SET last_activity = NOW() WHERE session_token = ?"
        );
        $stmt->bind_param("s", $token);
        $stmt->execute();
        
        // Verifica timeout de inatividade (30 minutos)
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > 1800) {
            $this->logout();
            return false;
        }
        
        $_SESSION['last_activity'] = time();
        
        return [
            'usuario_id' => $sessao['usuario_id'],
            'tipo_usuario' => $sessao['tipo_usuario'],
            'nome' => $sessao['nome']
        ];
    }
    
    /**
     * Logout seguro
     */
    public function logout() {
        session_start();
        
        if (isset($_SESSION['auth_token'])) {
            $token = $_SESSION['auth_token'];
            $stmt = $this->conn->prepare("DELETE FROM sessoes_ativas WHERE session_token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();
        }
        
        $_SESSION = array();
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
    }
    
    /**
     * Limpa sessões expiradas (executar via cron)
     */
    public function cleanExpiredSessions() {
        $this->conn->query("DELETE FROM sessoes_ativas WHERE expires_at < NOW()");
        $this->conn->query("DELETE FROM login_attempts WHERE tentativa_data < DATE_SUB(NOW(), INTERVAL 24 HOUR)");
    }
}
?>