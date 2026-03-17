<?php
require_once __DIR__ . '/Database.php';

class Auth {
    private $db;
    private $conn;
    
    public function __construct() {
        $this->db = new Database();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Registra um novo usuário
     */
    public function registrar($nome, $email, $senha) {
        // Validar email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email inválido.");
        }
        
        // Validar senha (mínimo 6 caracteres)
        if (strlen($senha) < 6) {
            throw new Exception("A senha deve ter no mínimo 6 caracteres.");
        }
        
        // Verificar se email já existe
        $sqlCheck = "SELECT id FROM usuarios WHERE email = :email";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->execute([':email' => $email]);
        
        if ($stmtCheck->fetch()) {
            throw new Exception("Este email já está cadastrado.");
        }
        
        // Hash da senha
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        
        // Inserir usuário
        $sql = "INSERT INTO usuarios (nome, email, senha, ativo) 
                VALUES (:nome, :email, :senha, TRUE)";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([
                ':nome' => $nome,
                ':email' => $email,
                ':senha' => $senhaHash
            ]);
            
            return $this->conn->lastInsertId();
        } catch(PDOException $e) {
            throw new Exception("Erro ao registrar usuário: " . $e->getMessage());
        }
    }
    
    /**
     * Faz login do usuário
     */
    public function login($email, $senha, $lembrar = false) {
        $sql = "SELECT id, nome, email, senha, ativo FROM usuarios WHERE email = :email";
        
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':email' => $email]);
            $usuario = $stmt->fetch();
            
            if (!$usuario) {
                throw new Exception("Email ou senha incorretos.");
            }
            
            if (!$usuario['ativo']) {
                throw new Exception("Sua conta está desativada. Entre em contato com o suporte.");
            }
            
            // Verificar senha
            if (!password_verify($senha, $usuario['senha'])) {
                throw new Exception("Email ou senha incorretos.");
            }
            
            // Criar sessão
            $this->criarSessao($usuario['id'], $lembrar);
            
            // Atualizar último acesso
            $sqlUpdate = "UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = :id";
            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute([':id' => $usuario['id']]);
            
            return [
                'id' => $usuario['id'],
                'nome' => $usuario['nome'],
                'email' => $usuario['email']
            ];
            
        } catch(PDOException $e) {
            throw new Exception("Erro ao fazer login: " . $e->getMessage());
        }
    }
    
    /**
     * Cria sessão do usuário
     */
    private function criarSessao($usuarioId, $lembrar = false) {
        // Iniciar sessão se não estiver iniciada
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Regenerar ID da sessão (segurança)
        session_regenerate_id(true);
        
        // Armazenar dados na sessão
        $_SESSION['usuario_id'] = $usuarioId;
        $_SESSION['usuario_logado'] = true;
        $_SESSION['tempo_login'] = time();
        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'] ?? '';
        
        // Token de segurança
        $token = bin2hex(random_bytes(32));
        $_SESSION['token'] = $token;
        
        // Salvar sessão no banco
        $expiraEm = $lembrar ? (time() + (30 * 24 * 60 * 60)) : (time() + (24 * 60 * 60)); // 30 dias ou 24h
        
        $sql = "INSERT INTO sessoes (usuario_id, token, ip_address, user_agent, expira_em)
                VALUES (:usuario_id, :token, :ip, :user_agent, FROM_UNIXTIME(:expira))";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':usuario_id' => $usuarioId,
            ':token' => $token,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            ':user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ':expira' => $expiraEm
        ]);
        
        // Cookie "Lembrar-me"
        if ($lembrar) {
            setcookie('usuario_token', $token, $expiraEm, '/', '', true, true);
        }
    }
    
    /**
     * Verifica se usuário está autenticado
     */
    public function verificarAutenticacao() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar sessão
        if (isset($_SESSION['usuario_logado']) && $_SESSION['usuario_logado'] === true) {
            // Verificar timeout (30 minutos de inatividade)
            if (isset($_SESSION['tempo_login']) && (time() - $_SESSION['tempo_login'] > 1800)) {
                $this->logout();
                return false;
            }
            
            // Atualizar timestamp
            $_SESSION['tempo_login'] = time();
            return true;
        }
        
        // Verificar cookie "Lembrar-me"
        if (isset($_COOKIE['usuario_token'])) {
            return $this->verificarToken($_COOKIE['usuario_token']);
        }
        
        return false;
    }
    
    /**
     * Verifica token de sessão
     */
    private function verificarToken($token) {
        $sql = "SELECT usuario_id FROM sessoes 
                WHERE token = :token 
                AND expira_em > NOW()";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':token' => $token]);
        $sessao = $stmt->fetch();
        
        if ($sessao) {
            $this->criarSessao($sessao['usuario_id'], true);
            return true;
        }
        
        return false;
    }
    
    /**
     * Obtém ID do usuário logado
     */
    public function getUsuarioId() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return $_SESSION['usuario_id'] ?? null;
    }
    
    /**
     * Obtém dados completos do usuário logado
     */
    public function getUsuario() {
        $usuarioId = $this->getUsuarioId();
        
        if (!$usuarioId) {
            return null;
        }
        
        $sql = "SELECT id, nome, email, foto_perfil, criado_em, ultimo_acesso 
                FROM usuarios WHERE id = :id";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $usuarioId]);
        
        return $stmt->fetch();
    }
    
    /**
     * Faz logout do usuário
     */
    public function logout() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Remover sessão do banco
        if (isset($_SESSION['token'])) {
            $sql = "DELETE FROM sessoes WHERE token = :token";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':token' => $_SESSION['token']]);
        }
        
        // Limpar sessão
        $_SESSION = array();
        
        // Destruir cookie de sessão
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 42000, '/');
        }
        
        // Destruir cookie "Lembrar-me"
        if (isset($_COOKIE['usuario_token'])) {
            setcookie('usuario_token', '', time() - 42000, '/', '', true, true);
        }
        
        // Destruir sessão
        session_destroy();
    }
    
    /**
     * Atualiza perfil do usuário
     */
    public function atualizarPerfil($usuarioId, $dados) {
        $sql = "UPDATE usuarios SET nome = :nome";
        $params = [':nome' => $dados['nome'], ':id' => $usuarioId];
        
        // Se há nova senha
        if (!empty($dados['senha_nova'])) {
            if (strlen($dados['senha_nova']) < 6) {
                throw new Exception("A nova senha deve ter no mínimo 6 caracteres.");
            }
            $sql .= ", senha = :senha";
            $params[':senha'] = password_hash($dados['senha_nova'], PASSWORD_DEFAULT);
        }
        
        $sql .= " WHERE id = :id";
        
        try {
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute($params);
        } catch(PDOException $e) {
            throw new Exception("Erro ao atualizar perfil: " . $e->getMessage());
        }
    }
    
    /**
     * Protege uma página (redireciona se não autenticado)
     */
    public function protegerPagina() {
        if (!$this->verificarAutenticacao()) {
            header('Location: index.php');
            exit;
        }
    }
    
    public function __destruct() {
        $this->db->close();
    }
}
