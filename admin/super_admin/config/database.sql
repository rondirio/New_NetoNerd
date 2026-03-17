-- =====================================================
-- SCHEMA JWT TOKEN MANAGEMENT - SUPERADMIN NETONERD
-- =====================================================

-- Tabela principal de Tenants (clientes da NetoNerd)
CREATE TABLE `tenants` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` VARCHAR(50) UNIQUE NOT NULL, -- Ex: TENANT-HEALTH-001
  `nome_empresa` VARCHAR(255) NOT NULL,
  `tipo_projeto` ENUM('myhealth', 'barbershop', 'suporte_ti') NOT NULL,
  `email_owner` VARCHAR(255) NOT NULL,
  `telefone` VARCHAR(20),
  `plano` ENUM('basico', 'profissional', 'premium', 'enterprise') DEFAULT 'basico',
  `ativo` BOOLEAN DEFAULT TRUE,
  `data_criacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `data_ultima_atualizacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_tenant_id (`tenant_id`),
  INDEX idx_tipo_projeto (`tipo_projeto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de JWT Tokens gerados
CREATE TABLE `jwt_tokens` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `tenant_id` INT NOT NULL,
  `token_hash` VARCHAR(64) UNIQUE NOT NULL, -- Hash SHA256 do token para busca rápida
  `jwt_token` TEXT NOT NULL, -- Token JWT completo
  `tipo_token` ENUM('access', 'refresh') DEFAULT 'access',
  `data_emissao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `data_expiracao` TIMESTAMP NOT NULL,
  `revogado` BOOLEAN DEFAULT FALSE,
  `data_revogacao` TIMESTAMP NULL,
  `motivo_revogacao` TEXT,
  `ultimo_uso` TIMESTAMP NULL,
  `ip_ultimo_uso` VARCHAR(45),
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE CASCADE,
  INDEX idx_token_hash (`token_hash`),
  INDEX idx_tenant_id (`tenant_id`),
  INDEX idx_expiracao (`data_expiracao`),
  INDEX idx_revogado (`revogado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Logs de Validação (auditoria)
CREATE TABLE `jwt_validation_logs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `token_id` INT,
  `tenant_id` INT,
  `projeto` VARCHAR(50), -- De qual projeto veio a validação
  `status` ENUM('success', 'expired', 'invalid', 'revoked') NOT NULL,
  `ip_address` VARCHAR(45),
  `user_agent` TEXT,
  `data_validacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`token_id`) REFERENCES `jwt_tokens`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`tenant_id`) REFERENCES `tenants`(`id`) ON DELETE SET NULL,
  INDEX idx_tenant_id (`tenant_id`),
  INDEX idx_data_validacao (`data_validacao`),
  INDEX idx_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de Configurações JWT (chave secreta, algoritmo, etc)
CREATE TABLE `jwt_config` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `chave_secreta` VARCHAR(512) NOT NULL, -- Chave secreta para assinar tokens
  `algoritmo` VARCHAR(10) DEFAULT 'HS256',
  `expiracao_access_token` INT DEFAULT 31536000, -- 1 ano em segundos
  `expiracao_refresh_token` INT DEFAULT 63072000, -- 2 anos em segundos
  `emissor` VARCHAR(255) DEFAULT 'NetoNerd Super Admin',
  `ativo` BOOLEAN DEFAULT TRUE,
  `data_criacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `data_rotacao` TIMESTAMP NULL -- Para rotação de chaves
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir configuração inicial (ALTERAR A CHAVE EM PRODUÇÃO!)
INSERT INTO `jwt_config` (`chave_secreta`, `algoritmo`, `emissor`) VALUES
(SHA2(CONCAT('NetoNerd-JWT-Secret-', UUID(), NOW()), 512), 'HS256', 'NetoNerd Super Admin');

-- =====================================================
-- VIEWS ÚTEIS
-- =====================================================

-- View de tokens ativos
CREATE VIEW `vw_tokens_ativos` AS
SELECT 
  t.tenant_id,
  tn.nome_empresa,
  tn.tipo_projeto,
  tn.plano,
  t.tipo_token,
  t.data_emissao,
  t.data_expiracao,
  t.ultimo_uso,
  DATEDIFF(t.data_expiracao, NOW()) as dias_ate_expiracao
FROM jwt_tokens t
INNER JOIN tenants tn ON t.tenant_id = tn.id
WHERE t.revogado = FALSE 
  AND t.data_expiracao > NOW()
  AND tn.ativo = TRUE;

-- View de estatísticas por tenant
CREATE VIEW `vw_tenant_stats` AS
SELECT 
  tn.id,
  tn.tenant_id,
  tn.nome_empresa,
  tn.tipo_projeto,
  COUNT(DISTINCT t.id) as total_tokens,
  COUNT(DISTINCT CASE WHEN t.revogado = FALSE AND t.data_expiracao > NOW() THEN t.id END) as tokens_ativos,
  COUNT(DISTINCT l.id) as total_validacoes,
  MAX(t.ultimo_uso) as ultima_atividade
FROM tenants tn
LEFT JOIN jwt_tokens t ON tn.id = t.tenant_id
LEFT JOIN jwt_validation_logs l ON tn.id = l.tenant_id
GROUP BY tn.id;

-- =====================================================
-- STORED PROCEDURES
-- =====================================================

DELIMITER //

-- Procedure para revogar token
CREATE PROCEDURE sp_revogar_token(
  IN p_token_hash VARCHAR(64),
  IN p_motivo TEXT
)
BEGIN
  UPDATE jwt_tokens 
  SET revogado = TRUE,
      data_revogacao = NOW(),
      motivo_revogacao = p_motivo
  WHERE token_hash = p_token_hash;
END //

-- Procedure para limpar tokens expirados (executar periodicamente)
CREATE PROCEDURE sp_limpar_tokens_expirados()
BEGIN
  -- Mover para uma tabela de histórico antes de deletar (opcional)
  DELETE FROM jwt_tokens 
  WHERE data_expiracao < DATE_SUB(NOW(), INTERVAL 90 DAY)
    AND revogado = TRUE;
END //

-- Procedure para registrar uso do token
CREATE PROCEDURE sp_registrar_uso_token(
  IN p_token_hash VARCHAR(64),
  IN p_ip_address VARCHAR(45)
)
BEGIN
  UPDATE jwt_tokens 
  SET ultimo_uso = NOW(),
      ip_ultimo_uso = p_ip_address
  WHERE token_hash = p_token_hash;
END //

DELIMITER ;

-- =====================================================
-- TRIGGERS
-- =====================================================

DELIMITER //

-- Trigger para criar tenant_id automático
CREATE TRIGGER before_insert_tenant
BEFORE INSERT ON tenants
FOR EACH ROW
BEGIN
  IF NEW.tenant_id IS NULL OR NEW.tenant_id = '' THEN
    SET NEW.tenant_id = CONCAT(
      'TENANT-',
      UPPER(SUBSTRING(NEW.tipo_projeto, 1, 3)),
      '-',
      LPAD((SELECT COALESCE(MAX(id), 0) + 1 FROM tenants), 6, '0')
    );
  END IF;
END //

DELIMITER ;

-- =====================================================
-- ÍNDICES ADICIONAIS PARA PERFORMANCE
-- =====================================================

-- Índice composto para queries frequentes
CREATE INDEX idx_tenant_active_tokens ON jwt_tokens(tenant_id, revogado, data_expiracao);
CREATE INDEX idx_validation_tenant_date ON jwt_validation_logs(tenant_id, data_validacao);

-- =====================================================
-- DADOS DE EXEMPLO (OPCIONAL - REMOVER EM PRODUÇÃO)
-- =====================================================

-- Exemplos de tenants
INSERT INTO `tenants` (`nome_empresa`, `tipo_projeto`, `email_owner`, `plano`) VALUES
('Hospital São Lucas', 'myhealth', 'admin@hospitalsaolucas.com', 'premium'),
('Barbearia do João', 'barbershop', 'joao@barbearia.com', 'profissional'),
('TechCorp Ltda', 'suporte_ti', 'suporte@techcorp.com', 'enterprise');

-- =====================================================
-- COMENTÁRIOS E DOCUMENTAÇÃO
-- =====================================================

/*
INSTRUÇÕES DE USO:

1. Execute este script no banco de dados 'super_admin_netonerd'
2. A chave secreta é gerada automaticamente - ANOTE-A para uso na aplicação
3. Configure um CRON JOB para executar sp_limpar_tokens_expirados() mensalmente
4. Faça backup regular da tabela jwt_config (contém a chave secreta)
5. Para rotação de chaves, gere nova entrada em jwt_config e atualize a aplicação

SEGURANÇA:
- NUNCA exponha a chave_secreta em repositórios públicos
- Use HTTPS em todas as comunicações
- Implemente rate limiting nos endpoints de validação
- Monitore a tabela jwt_validation_logs para detectar acessos suspeitos
*/