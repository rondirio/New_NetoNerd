-- =============================================
-- Tabela de Chaves API - NetoNerd ITSM
-- Sistema de autenticação para aplicativo mobile
-- Cada chave está vinculada ao banco de dados do cliente
-- =============================================

CREATE TABLE IF NOT EXISTS api_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(64) NOT NULL UNIQUE,
    descricao VARCHAR(255) DEFAULT NULL,
    cliente_nome VARCHAR(255) DEFAULT NULL,

    -- Dados de conexão do banco de dados do cliente (Hostinger)
    db_host VARCHAR(255) NOT NULL COMMENT 'Host do banco de dados do cliente',
    db_nome VARCHAR(255) NOT NULL COMMENT 'Nome do banco de dados',
    db_usuario VARCHAR(255) NOT NULL COMMENT 'Usuário do banco de dados',
    db_senha VARCHAR(255) NOT NULL COMMENT 'Senha do banco de dados (criptografada)',
    db_porta INT DEFAULT 3306 COMMENT 'Porta do banco de dados',

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

-- Para adicionar os campos em tabela existente:
-- ALTER TABLE api_keys
--     ADD COLUMN db_host VARCHAR(255) NOT NULL DEFAULT '' AFTER cliente_nome,
--     ADD COLUMN db_nome VARCHAR(255) NOT NULL DEFAULT '' AFTER db_host,
--     ADD COLUMN db_usuario VARCHAR(255) NOT NULL DEFAULT '' AFTER db_nome,
--     ADD COLUMN db_senha VARCHAR(255) NOT NULL DEFAULT '' AFTER db_usuario,
--     ADD COLUMN db_porta INT DEFAULT 3306 AFTER db_senha;
