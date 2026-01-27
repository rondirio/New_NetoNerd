-- ============================================================
-- Sistema de Licenciamento de Produtos - NetoNerd
-- MyHealth | Escritorius | NetoNerd PJ | StyleManager
-- ============================================================

USE netonerd_chamados;

-- ============================================================
-- 1. TABELA DE PRODUTOS LICENCIÁVEIS
-- ============================================================

CREATE TABLE IF NOT EXISTS `produtos_licenciaveis` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL UNIQUE,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `descricao` TEXT,
  `versao_atual` VARCHAR(20) DEFAULT '1.0.0',
  `preco_mensal` DECIMAL(10,2) DEFAULT 0.00,
  `preco_anual` DECIMAL(10,2) DEFAULT 0.00,
  `preco_vitalicio` DECIMAL(10,2) DEFAULT 0.00,
  `dias_trial` INT(11) DEFAULT 30,
  `dias_tolerancia_pagamento` INT(11) DEFAULT 7,
  `max_instalacoes` INT(11) DEFAULT 1,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_produto_slug` (`slug`),
  KEY `idx_produto_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Inserir os 4 produtos
INSERT INTO `produtos_licenciaveis`
(`nome`, `slug`, `descricao`, `preco_mensal`, `preco_anual`, `preco_vitalicio`, `dias_trial`)
VALUES
('MyHealth', 'myhealth', 'Sistema de Prontuário Eletrônico para Hospitais e Clínicas', 299.90, 2999.00, 9999.00, 30),
('Escritorius', 'escritorius', 'Sistema de Gestão para Escritórios de Advocacia', 199.90, 1999.00, 6999.00, 30),
('NetoNerd PJ', 'netonerd-pj', 'Sistema de Gestão Empresarial Completo', 399.90, 3999.00, 12999.00, 30),
('StyleManager', 'stylemanager', 'Sistema de Gestão para Salões de Beleza', 149.90, 1499.00, 4999.00, 30)
ON DUPLICATE KEY UPDATE nome=nome;

-- ============================================================
-- 2. TABELA DE LICENÇAS
-- ============================================================

CREATE TABLE IF NOT EXISTS `licencas` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `api_key` VARCHAR(64) NOT NULL UNIQUE COMMENT 'Chave única de licença',
  `produto_id` INT(11) NOT NULL,
  `cliente_id` INT(11) NOT NULL COMMENT 'FK para tabela clientes',
  `tipo_licenca` ENUM('mensal', 'anual', 'vitalicia', 'trial') NOT NULL DEFAULT 'trial',
  `status` ENUM('ativa', 'trial', 'expirada', 'suspensa', 'cancelada') NOT NULL DEFAULT 'trial',
  `max_instalacoes` INT(11) DEFAULT 1,
  `data_geracao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_ativacao` DATETIME DEFAULT NULL COMMENT 'Quando o cliente ativou pela primeira vez',
  `data_inicio_trial` DATETIME DEFAULT NULL,
  `data_fim_trial` DATETIME DEFAULT NULL,
  `data_proxima_cobranca` DATETIME DEFAULT NULL,
  `data_expiracao` DATETIME DEFAULT NULL COMMENT 'Para licenças com prazo determinado',
  `valor_licenca` DECIMAL(10,2) DEFAULT 0.00,
  `observacoes` TEXT,
  `vendedor_id` INT(11) DEFAULT NULL COMMENT 'Técnico/vendedor que gerou',
  `data_atualizacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `api_key` (`api_key`),
  KEY `idx_licenca_produto` (`produto_id`),
  KEY `idx_licenca_cliente` (`cliente_id`),
  KEY `idx_licenca_status` (`status`),
  KEY `idx_licenca_proxima_cobranca` (`data_proxima_cobranca`),
  CONSTRAINT `fk_licenca_produto` FOREIGN KEY (`produto_id`) REFERENCES `produtos_licenciaveis` (`id`),
  CONSTRAINT `fk_licenca_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 3. TABELA DE ATIVAÇÕES (INSTALAÇÕES)
-- ============================================================

CREATE TABLE IF NOT EXISTS `ativacoes_licenca` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `licenca_id` INT(11) NOT NULL,
  `url_instalacao` VARCHAR(255) NOT NULL COMMENT 'URL onde o sistema foi instalado',
  `ip_servidor` VARCHAR(45) DEFAULT NULL,
  `dominio` VARCHAR(255) DEFAULT NULL,
  `usuario_admin_criado` VARCHAR(255) DEFAULT NULL COMMENT 'Usuário admin criado automaticamente',
  `senha_admin_hash` VARCHAR(255) DEFAULT NULL,
  `data_ativacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_ultima_validacao` DATETIME DEFAULT NULL COMMENT 'Última vez que validou a licença',
  `versao_produto` VARCHAR(20) DEFAULT NULL,
  `sistema_operacional` VARCHAR(100) DEFAULT NULL,
  `php_version` VARCHAR(20) DEFAULT NULL,
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_ativacao_licenca` (`licenca_id`),
  KEY `idx_ativacao_url` (`url_instalacao`),
  KEY `idx_ativacao_ultima_validacao` (`data_ultima_validacao`),
  CONSTRAINT `fk_ativacao_licenca` FOREIGN KEY (`licenca_id`) REFERENCES `licencas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 4. TABELA DE HISTÓRICO DE VALIDAÇÕES
-- ============================================================

CREATE TABLE IF NOT EXISTS `logs_validacao_licenca` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `licenca_id` INT(11) NOT NULL,
  `ativacao_id` INT(11) DEFAULT NULL,
  `tipo_validacao` ENUM('ativacao', 'verificacao', 'renovacao', 'bloqueio', 'desbloqueio') NOT NULL,
  `resultado` ENUM('sucesso', 'falha', 'bloqueada', 'expirada') NOT NULL,
  `mensagem` TEXT,
  `ip_origem` VARCHAR(45) DEFAULT NULL,
  `user_agent` VARCHAR(255) DEFAULT NULL,
  `data_validacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_log_licenca` (`licenca_id`),
  KEY `idx_log_data` (`data_validacao`),
  CONSTRAINT `fk_log_licenca` FOREIGN KEY (`licenca_id`) REFERENCES `licencas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 5. TABELA DE PAGAMENTOS/COBRANÇAS
-- ============================================================

CREATE TABLE IF NOT EXISTS `cobrancas_licenca` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `licenca_id` INT(11) NOT NULL,
  `cliente_id` INT(11) NOT NULL,
  `valor` DECIMAL(10,2) NOT NULL,
  `descricao` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('pendente', 'paga', 'atrasada', 'cancelada') NOT NULL DEFAULT 'pendente',
  `data_vencimento` DATE NOT NULL,
  `data_pagamento` DATETIME DEFAULT NULL,
  `forma_pagamento` VARCHAR(50) DEFAULT NULL,
  `referencia_pagamento` VARCHAR(100) DEFAULT NULL COMMENT 'ID transação, boleto, etc',
  `observacoes` TEXT,
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_cobranca_licenca` (`licenca_id`),
  KEY `idx_cobranca_status` (`status`),
  KEY `idx_cobranca_vencimento` (`data_vencimento`),
  CONSTRAINT `fk_cobranca_licenca` FOREIGN KEY (`licenca_id`) REFERENCES `licencas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cobranca_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 6. TRIGGERS AUTOMÁTICOS
-- ============================================================

-- Trigger para calcular data de trial ao ativar licença
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `calcular_periodo_trial`
AFTER UPDATE ON `licencas`
FOR EACH ROW
BEGIN
    -- Quando a licença é ativada pela primeira vez
    IF NEW.data_ativacao IS NOT NULL AND OLD.data_ativacao IS NULL THEN
        -- Buscar dias de trial do produto
        DECLARE dias_trial INT;
        SELECT p.dias_trial INTO dias_trial
        FROM produtos_licenciaveis p
        WHERE p.id = NEW.produto_id;

        -- Atualizar datas de trial
        UPDATE licencas
        SET
            data_inicio_trial = NEW.data_ativacao,
            data_fim_trial = DATE_ADD(NEW.data_ativacao, INTERVAL dias_trial DAY),
            data_proxima_cobranca = DATE_ADD(NEW.data_ativacao, INTERVAL dias_trial DAY),
            status = 'trial'
        WHERE id = NEW.id;
    END IF;
END$$
DELIMITER ;

-- ============================================================
-- 7. VIEWS ÚTEIS
-- ============================================================

-- View de licenças completas com informações do cliente e produto
CREATE OR REPLACE VIEW `vw_licencas_completas` AS
SELECT
    l.id,
    l.api_key,
    l.tipo_licenca,
    l.status,
    l.data_ativacao,
    l.data_fim_trial,
    l.data_proxima_cobranca,
    l.valor_licenca,
    p.nome as produto_nome,
    p.slug as produto_slug,
    p.versao_atual as produto_versao,
    c.nome as cliente_nome,
    c.email as cliente_email,
    c.telefone as cliente_telefone,
    t.nome as vendedor_nome,
    COUNT(DISTINCT a.id) as total_instalacoes,
    l.max_instalacoes,
    CASE
        WHEN l.status = 'trial' AND l.data_fim_trial < NOW() THEN 'trial_expirado'
        WHEN l.status = 'ativa' AND l.data_proxima_cobranca < NOW() THEN 'pagamento_atrasado'
        WHEN l.status = 'ativa' AND l.data_proxima_cobranca < DATE_ADD(NOW(), INTERVAL 7 DAY) THEN 'proximo_vencimento'
        ELSE l.status
    END as status_calculado,
    DATEDIFF(l.data_proxima_cobranca, NOW()) as dias_para_vencimento
FROM licencas l
INNER JOIN produtos_licenciaveis p ON l.produto_id = p.id
INNER JOIN clientes c ON l.cliente_id = c.id
LEFT JOIN tecnicos t ON l.vendedor_id = t.id
LEFT JOIN ativacoes_licenca a ON l.id = a.licenca_id AND a.ativo = 1
GROUP BY l.id;

-- View de licenças para verificação de pagamento
CREATE OR REPLACE VIEW `vw_licencas_verificar_pagamento` AS
SELECT
    l.id,
    l.api_key,
    l.status,
    c.nome as cliente_nome,
    c.email as cliente_email,
    p.nome as produto_nome,
    l.data_proxima_cobranca,
    DATEDIFF(NOW(), l.data_proxima_cobranca) as dias_atraso,
    l.valor_licenca
FROM licencas l
INNER JOIN clientes c ON l.cliente_id = c.id
INNER JOIN produtos_licenciaveis p ON l.produto_id = p.id
WHERE l.status IN ('ativa', 'trial')
  AND l.data_proxima_cobranca < NOW()
  AND l.tipo_licenca != 'vitalicia';

-- ============================================================
-- 8. ÍNDICES ADICIONAIS PARA PERFORMANCE
-- ============================================================

ALTER TABLE licencas
ADD INDEX `idx_licenca_expiracao` (`data_expiracao`),
ADD INDEX `idx_licenca_tipo_status` (`tipo_licenca`, `status`);

ALTER TABLE ativacoes_licenca
ADD INDEX `idx_ativacao_dominio` (`dominio`);

ALTER TABLE cobrancas_licenca
ADD INDEX `idx_cobranca_cliente` (`cliente_id`),
ADD INDEX `idx_cobranca_status_vencimento` (`status`, `data_vencimento`);

-- ============================================================
-- FIM DO SCRIPT
-- ============================================================

SELECT 'Sistema de Licenciamento criado com sucesso!' AS status;
