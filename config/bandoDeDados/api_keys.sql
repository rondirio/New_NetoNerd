-- =============================================
-- Tabela de Chaves API - NetoNerd ITSM
-- Sistema de autenticação para aplicativo mobile
-- =============================================

CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(64) NOT NULL UNIQUE,
    descricao VARCHAR(255) DEFAULT NULL,
    cliente_nome VARCHAR(255) DEFAULT NULL,
    status ENUM('ativa', 'inativa', 'revogada') DEFAULT 'ativa',
    data_criacao DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_expiracao DATETIME DEFAULT NULL,
    ultimo_uso DATETIME DEFAULT NULL,
    total_requisicoes INT DEFAULT 0,
    ip_permitido VARCHAR(255) DEFAULT NULL COMMENT 'IPs permitidos separados por vírgula, NULL = todos',
    criado_por INT DEFAULT NULL,
    INDEX idx_chave (chave),
    INDEX idx_status (status),
    INDEX idx_expiracao (data_expiracao)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Log de uso das chaves API
CREATE TABLE IF NOT EXISTS api_keys_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    api_key_id INT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    endpoint VARCHAR(255) DEFAULT NULL,
    user_agent VARCHAR(500) DEFAULT NULL,
    data_acesso DATETIME DEFAULT CURRENT_TIMESTAMP,
    resposta_status INT DEFAULT NULL,
    FOREIGN KEY (api_key_id) REFERENCES api_keys(id) ON DELETE CASCADE,
    INDEX idx_api_key (api_key_id),
    INDEX idx_data (data_acesso)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
