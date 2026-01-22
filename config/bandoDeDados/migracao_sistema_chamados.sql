-- ====================================================================
-- MIGRAÇÃO: Sistema Completo de Gerenciamento de Chamados
-- NetoNerd ITSM
-- Data: 2026-01-21
-- ====================================================================
--
-- Esta migração adiciona:
-- 1. Campos para histórico detalhado de atendimento
-- 2. Flag para identificar serviços StyleManager (software - não cobra)
-- 3. Upload de fotos do serviço
-- 4. Ajuste de pagamento_forma para permitir NULL
-- 5. Tabela de atribuições com histórico
-- 6. Tabela de fotos do serviço (múltiplas fotos)
-- ====================================================================

USE netonerd_chamados;

-- ====================================================================
-- 1. BACKUP DE SEGURANÇA
-- ====================================================================

DROP TABLE IF EXISTS `chamados_backup_20260121`;
CREATE TABLE `chamados_backup_20260121` AS SELECT * FROM `chamados`;

SELECT 'Backup de chamados criado com sucesso!' AS status;

-- ====================================================================
-- 2. ADICIONAR NOVOS CAMPOS À TABELA CHAMADOS
-- ====================================================================

-- Histórico detalhado do atendimento (preenchido pelo técnico ao resolver)
ALTER TABLE `chamados`
ADD COLUMN IF NOT EXISTS `historico_atendimento` TEXT NULL COMMENT 'Histórico detalhado do atendimento realizado';

-- Flag para identificar se é suporte StyleManager (software)
-- Se TRUE, não cobra (pode ser erro de desenvolvimento)
ALTER TABLE `chamados`
ADD COLUMN IF NOT EXISTS `stylemanager_software` TINYINT(1) DEFAULT 0 COMMENT 'Se 1, é suporte StyleManager software (não cobra)';

-- Data de início do atendimento
ALTER TABLE `chamados`
ADD COLUMN IF NOT EXISTS `data_inicio_atendimento` TIMESTAMP NULL COMMENT 'Quando técnico colocou em andamento';

-- Data de resolução
ALTER TABLE `chamados`
ADD COLUMN IF NOT EXISTS `data_resolucao` TIMESTAMP NULL COMMENT 'Quando técnico marcou como resolvido';

-- Tempo total de atendimento em minutos (calculado automaticamente)
ALTER TABLE `chamados`
ADD COLUMN IF NOT EXISTS `tempo_atendimento_minutos` INT NULL COMMENT 'Tempo total em minutos';

SELECT 'Campos adicionados à tabela chamados!' AS status;

-- ====================================================================
-- 3. AJUSTAR CAMPO PAGAMENTO_FORMA
-- ====================================================================

-- Permitir NULL (só obrigatório ao resolver, exceto StyleManager software)
ALTER TABLE `chamados`
MODIFY COLUMN `pagamento_forma` ENUM('Cartão','Débito','PIX','Dinheiro') NULL;

SELECT 'Campo pagamento_forma ajustado para permitir NULL!' AS status;

-- ====================================================================
-- 4. CRIAR TABELA DE FOTOS DO SERVIÇO
-- ====================================================================

CREATE TABLE IF NOT EXISTS `chamado_fotos` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `chamado_id` INT(11) NOT NULL,
  `tecnico_id` INT(11) NOT NULL,
  `nome_arquivo` VARCHAR(255) NOT NULL,
  `caminho_arquivo` VARCHAR(500) NOT NULL,
  `descricao` VARCHAR(255) NULL,
  `data_upload` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_chamado` (`chamado_id`),
  INDEX `idx_tecnico` (`tecnico_id`),
  FOREIGN KEY (`chamado_id`) REFERENCES `chamados`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tecnico_id`) REFERENCES `tecnicos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Fotos dos serviços realizados pelos técnicos';

SELECT 'Tabela chamado_fotos criada!' AS status;

-- ====================================================================
-- 5. CRIAR TABELA DE HISTÓRICO DE ATRIBUIÇÕES
-- ====================================================================

CREATE TABLE IF NOT EXISTS `chamado_atribuicoes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `chamado_id` INT(11) NOT NULL,
  `tecnico_id` INT(11) NOT NULL,
  `admin_id` INT(11) NOT NULL COMMENT 'Admin que fez a atribuição',
  `data_atribuicao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comentario` TEXT NULL,
  `ativo` TINYINT(1) DEFAULT 1 COMMENT '1=Atribuição ativa, 0=Técnico foi removido',
  PRIMARY KEY (`id`),
  INDEX `idx_chamado` (`chamado_id`),
  INDEX `idx_tecnico` (`tecnico_id`),
  INDEX `idx_admin` (`admin_id`),
  INDEX `idx_ativo` (`ativo`),
  FOREIGN KEY (`chamado_id`) REFERENCES `chamados`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tecnico_id`) REFERENCES `tecnicos`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`admin_id`) REFERENCES `tecnicos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Histórico de atribuições de chamados a técnicos';

SELECT 'Tabela chamado_atribuicoes criada!' AS status;

-- ====================================================================
-- 6. CRIAR TABELA DE ATUALIZAÇÕES DO TÉCNICO
-- ====================================================================

CREATE TABLE IF NOT EXISTS `chamado_atualizacoes` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `chamado_id` INT(11) NOT NULL,
  `tecnico_id` INT(11) NOT NULL,
  `tipo_atualizacao` ENUM('comentario','inicio_atendimento','pausa','conclusao','necessita_peca','aguardando_cliente') NOT NULL,
  `descricao` TEXT NOT NULL,
  `data_atualizacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  INDEX `idx_chamado` (`chamado_id`),
  INDEX `idx_tecnico` (`tecnico_id`),
  INDEX `idx_tipo` (`tipo_atualizacao`),
  FOREIGN KEY (`chamado_id`) REFERENCES `chamados`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`tecnico_id`) REFERENCES `tecnicos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
COMMENT='Atualizações e comentários dos técnicos nos chamados';

SELECT 'Tabela chamado_atualizacoes criada!' AS status;

-- ====================================================================
-- 7. CRIAR TRIGGERS PARA CALCULAR TEMPO DE ATENDIMENTO
-- ====================================================================

DROP TRIGGER IF EXISTS `calcular_tempo_atendimento`;

DELIMITER $$
CREATE TRIGGER `calcular_tempo_atendimento` BEFORE UPDATE ON `chamados`
FOR EACH ROW
BEGIN
    -- Quando status mudar para 'em andamento', registrar data de início
    IF OLD.status != 'em andamento' AND NEW.status = 'em andamento' THEN
        SET NEW.data_inicio_atendimento = CURRENT_TIMESTAMP;
    END IF;

    -- Quando status mudar para 'resolvido', calcular tempo total
    IF OLD.status != 'resolvido' AND NEW.status = 'resolvido' THEN
        SET NEW.data_resolucao = CURRENT_TIMESTAMP;

        -- Calcular tempo em minutos (da abertura até resolução)
        IF NEW.data_inicio_atendimento IS NOT NULL THEN
            SET NEW.tempo_atendimento_minutos = TIMESTAMPDIFF(MINUTE, NEW.data_inicio_atendimento, NEW.data_resolucao);
        ELSE
            -- Se não tem data de início, calcular da abertura
            SET NEW.tempo_atendimento_minutos = TIMESTAMPDIFF(MINUTE, NEW.data_abertura, NEW.data_resolucao);
        END IF;
    END IF;
END$$
DELIMITER ;

SELECT 'Trigger calcular_tempo_atendimento criado!' AS status;

-- ====================================================================
-- 8. CRIAR VIEWS ÚTEIS
-- ====================================================================

-- View: Chamados com informações completas
DROP VIEW IF EXISTS `view_chamados_completos`;
CREATE VIEW `view_chamados_completos` AS
SELECT
    c.id,
    c.protocolo,
    c.titulo,
    c.descricao,
    c.status,
    c.prioridade,
    c.data_abertura,
    c.data_inicio_atendimento,
    c.data_resolucao,
    c.data_fechamento,
    c.tempo_atendimento_minutos,
    c.stylemanager_software,
    c.pagamento_forma,
    c.historico_atendimento,

    -- Dados do cliente
    cl.nome AS cliente_nome,
    cl.email AS cliente_email,
    cl.telefone AS cliente_telefone,

    -- Dados do técnico
    t.nome AS tecnico_nome,
    t.email AS tecnico_email,
    t.matricula AS tecnico_matricula,

    -- Contadores
    (SELECT COUNT(*) FROM chamado_fotos WHERE chamado_id = c.id) AS total_fotos,
    (SELECT COUNT(*) FROM chamado_atualizacoes WHERE chamado_id = c.id) AS total_atualizacoes,

    -- Status de cobrança
    CASE
        WHEN c.stylemanager_software = 1 THEN 'Não cobrar (StyleManager Software)'
        WHEN c.pagamento_forma IS NOT NULL THEN CONCAT('Pago via ', c.pagamento_forma)
        WHEN c.status = 'resolvido' THEN 'PENDENTE PAGAMENTO'
        ELSE 'Não aplicável'
    END AS status_pagamento

FROM chamados c
LEFT JOIN clientes cl ON c.cliente_id = cl.id
LEFT JOIN tecnicos t ON c.tecnico_id = t.id
ORDER BY c.data_abertura DESC;

SELECT 'View view_chamados_completos criada!' AS status;

-- View: Chamados por técnico
DROP VIEW IF EXISTS `view_chamados_por_tecnico`;
CREATE VIEW `view_chamados_por_tecnico` AS
SELECT
    t.id AS tecnico_id,
    t.nome AS tecnico_nome,
    t.matricula,
    COUNT(CASE WHEN c.status = 'aberto' THEN 1 END) AS chamados_abertos,
    COUNT(CASE WHEN c.status = 'em andamento' THEN 1 END) AS chamados_em_andamento,
    COUNT(CASE WHEN c.status = 'pendente' THEN 1 END) AS chamados_pendentes,
    COUNT(CASE WHEN c.status = 'resolvido' THEN 1 END) AS chamados_resolvidos,
    COUNT(c.id) AS total_chamados,
    AVG(c.tempo_atendimento_minutos) AS tempo_medio_atendimento,
    MAX(c.data_abertura) AS ultimo_chamado_atribuido
FROM tecnicos t
LEFT JOIN chamados c ON t.id = c.tecnico_id
GROUP BY t.id, t.nome, t.matricula
ORDER BY chamados_em_andamento DESC, chamados_abertos DESC;

SELECT 'View view_chamados_por_tecnico criada!' AS status;

-- View: Chamados não atribuídos (para admin atribuir)
DROP VIEW IF EXISTS `view_chamados_nao_atribuidos`;
CREATE VIEW `view_chamados_nao_atribuidos` AS
SELECT
    c.id,
    c.protocolo,
    c.titulo,
    c.descricao,
    c.status,
    c.prioridade,
    c.data_abertura,
    cl.nome AS cliente_nome,
    cl.telefone AS cliente_telefone,
    TIMESTAMPDIFF(HOUR, c.data_abertura, NOW()) AS horas_aguardando
FROM chamados c
LEFT JOIN clientes cl ON c.cliente_id = cl.id
WHERE c.tecnico_id IS NULL
  AND c.status != 'cancelado'
ORDER BY c.prioridade DESC, c.data_abertura ASC;

SELECT 'View view_chamados_nao_atribuidos criada!' AS status;

-- ====================================================================
-- 9. CRIAR ÍNDICES PARA PERFORMANCE
-- ====================================================================

ALTER TABLE `chamados`
ADD INDEX IF NOT EXISTS `idx_status` (`status`);

ALTER TABLE `chamados`
ADD INDEX IF NOT EXISTS `idx_tecnico_status` (`tecnico_id`, `status`);

ALTER TABLE `chamados`
ADD INDEX IF NOT EXISTS `idx_cliente` (`cliente_id`);

ALTER TABLE `chamados`
ADD INDEX IF NOT EXISTS `idx_data_abertura` (`data_abertura`);

ALTER TABLE `chamados`
ADD INDEX IF NOT EXISTS `idx_prioridade` (`prioridade`);

SELECT 'Índices criados para melhor performance!' AS status;

-- ====================================================================
-- 10. ATUALIZAR DADOS EXISTENTES
-- ====================================================================

-- Definir stylemanager_software como FALSE para todos os chamados existentes
UPDATE `chamados`
SET `stylemanager_software` = 0
WHERE `stylemanager_software` IS NULL;

SELECT 'Dados existentes atualizados!' AS status;

-- ====================================================================
-- 11. CRIAR STORED PROCEDURES ÚTEIS
-- ====================================================================

-- Procedure: Atribuir chamado a técnico (apenas admin)
DROP PROCEDURE IF EXISTS `sp_atribuir_chamado`;

DELIMITER $$
CREATE PROCEDURE `sp_atribuir_chamado`(
    IN p_chamado_id INT,
    IN p_tecnico_id INT,
    IN p_admin_id INT,
    IN p_comentario TEXT
)
BEGIN
    DECLARE v_tecnico_atual INT;

    -- Verificar se chamado existe
    IF NOT EXISTS (SELECT 1 FROM chamados WHERE id = p_chamado_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Chamado não encontrado';
    END IF;

    -- Verificar se técnico existe
    IF NOT EXISTS (SELECT 1 FROM tecnicos WHERE id = p_tecnico_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Técnico não encontrado';
    END IF;

    -- Obter técnico atual
    SELECT tecnico_id INTO v_tecnico_atual FROM chamados WHERE id = p_chamado_id;

    -- Se já tinha um técnico, desativar atribuição anterior
    IF v_tecnico_atual IS NOT NULL THEN
        UPDATE chamado_atribuicoes
        SET ativo = 0
        WHERE chamado_id = p_chamado_id AND tecnico_id = v_tecnico_atual AND ativo = 1;
    END IF;

    -- Atribuir novo técnico
    UPDATE chamados
    SET tecnico_id = p_tecnico_id,
        status = IF(status = 'aberto', 'aberto', status)
    WHERE id = p_chamado_id;

    -- Registrar atribuição
    INSERT INTO chamado_atribuicoes (chamado_id, tecnico_id, admin_id, comentario, ativo)
    VALUES (p_chamado_id, p_tecnico_id, p_admin_id, p_comentario, 1);

    -- Log
    INSERT INTO logs_sistema (usuario_id, acao)
    VALUES (p_admin_id, CONCAT('Atribuiu chamado #', p_chamado_id, ' ao técnico ID ', p_tecnico_id));

    SELECT 'Chamado atribuído com sucesso!' AS resultado;
END$$
DELIMITER ;

SELECT 'Stored procedure sp_atribuir_chamado criada!' AS status;

-- Procedure: Iniciar atendimento (técnico)
DROP PROCEDURE IF EXISTS `sp_iniciar_atendimento`;

DELIMITER $$
CREATE PROCEDURE `sp_iniciar_atendimento`(
    IN p_chamado_id INT,
    IN p_tecnico_id INT
)
BEGIN
    -- Verificar se chamado está atribuído ao técnico
    IF NOT EXISTS (SELECT 1 FROM chamados WHERE id = p_chamado_id AND tecnico_id = p_tecnico_id) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Chamado não atribuído a este técnico';
    END IF;

    -- Atualizar status
    UPDATE chamados
    SET status = 'em andamento',
        data_inicio_atendimento = CURRENT_TIMESTAMP
    WHERE id = p_chamado_id;

    -- Registrar atualização
    INSERT INTO chamado_atualizacoes (chamado_id, tecnico_id, tipo_atualizacao, descricao)
    VALUES (p_chamado_id, p_tecnico_id, 'inicio_atendimento', 'Técnico iniciou o atendimento');

    SELECT 'Atendimento iniciado com sucesso!' AS resultado;
END$$
DELIMITER ;

SELECT 'Stored procedure sp_iniciar_atendimento criada!' AS status;

-- ====================================================================
-- 12. RELATÓRIO FINAL
-- ====================================================================

SELECT '==================== RELATÓRIO FINAL ====================' AS '';

SELECT 'ESTRUTURA' AS categoria, 'Tabela' AS tipo, 'chamados' AS nome, 'Campos adicionados' AS status
UNION ALL
SELECT 'ESTRUTURA', 'Tabela', 'chamado_fotos', 'Criada' AS status
UNION ALL
SELECT 'ESTRUTURA', 'Tabela', 'chamado_atribuicoes', 'Criada' AS status
UNION ALL
SELECT 'ESTRUTURA', 'Tabela', 'chamado_atualizacoes', 'Criada' AS status
UNION ALL
SELECT 'VIEWS', 'View', 'view_chamados_completos', 'Criada' AS status
UNION ALL
SELECT 'VIEWS', 'View', 'view_chamados_por_tecnico', 'Criada' AS status
UNION ALL
SELECT 'VIEWS', 'View', 'view_chamados_nao_atribuidos', 'Criada' AS status
UNION ALL
SELECT 'PROCEDURES', 'SP', 'sp_atribuir_chamado', 'Criada' AS status
UNION ALL
SELECT 'PROCEDURES', 'SP', 'sp_iniciar_atendimento', 'Criada' AS status
UNION ALL
SELECT 'TRIGGERS', 'Trigger', 'calcular_tempo_atendimento', 'Criado' AS status;

-- Estatísticas
SELECT
    'ESTATÍSTICAS' AS '',
    (SELECT COUNT(*) FROM chamados) AS total_chamados,
    (SELECT COUNT(*) FROM chamados WHERE tecnico_id IS NULL) AS chamados_nao_atribuidos,
    (SELECT COUNT(*) FROM chamados WHERE status = 'em andamento') AS em_andamento,
    (SELECT COUNT(*) FROM chamados WHERE status = 'resolvido') AS resolvidos;

SELECT '==================== MIGRAÇÃO CONCLUÍDA ====================' AS '';

-- ====================================================================
-- NOTAS IMPORTANTES:
-- ====================================================================
--
-- 1. NOVOS CAMPOS EM CHAMADOS:
--    - historico_atendimento: Texto detalhado do que foi feito
--    - stylemanager_software: Se TRUE, não cobra (erro de desenvolvimento)
--    - data_inicio_atendimento: Quando começou o atendimento
--    - data_resolucao: Quando foi resolvido
--    - tempo_atendimento_minutos: Calculado automaticamente
--
-- 2. NOVAS TABELAS:
--    - chamado_fotos: Múltiplas fotos por chamado
--    - chamado_atribuicoes: Histórico de quem atribuiu para quem
--    - chamado_atualizacoes: Atualizações do técnico
--
-- 3. FLUXO DE TRABALHO:
--    Admin: Atribui chamado → Técnico: Inicia atendimento → Técnico: Resolve → Preenche formulário
--
-- 4. OBRIGATÓRIO AO RESOLVER:
--    - historico_atendimento (texto)
--    - stylemanager_software (checkbox)
--    - pagamento_forma (se não for StyleManager software)
--    - Pelo menos 1 foto
--
-- 5. VIEWS ÚTEIS:
--    - view_chamados_completos: Todos os dados em uma query
--    - view_chamados_por_tecnico: Estatísticas por técnico
--    - view_chamados_nao_atribuidos: Para admin atribuir
--
-- 6. STORED PROCEDURES:
--    - sp_atribuir_chamado(chamado_id, tecnico_id, admin_id, comentario)
--    - sp_iniciar_atendimento(chamado_id, tecnico_id)
--
-- ====================================================================
