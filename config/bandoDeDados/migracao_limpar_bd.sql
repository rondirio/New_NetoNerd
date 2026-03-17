-- ====================================================================
-- MIGRAÇÃO: Limpeza e Otimização do Banco de Dados
-- NetoNerd ITSM
-- Data: 2026-01-15
-- ====================================================================
--
-- Esta migração resolve:
-- 1. Campo duplicado 'senha' na tabela tecnicos (mantém apenas senha_hash)
-- 2. Garante que senhas vazias sejam migradas corretamente
-- 3. Otimiza estrutura do banco
-- ====================================================================

USE netonerd_chamados;

-- ====================================================================
-- 1. BACKUP DE SEGURANÇA
-- ====================================================================

-- Criar tabela de backup antes de fazer alterações
DROP TABLE IF EXISTS `tecnicos_backup_20260115`;
CREATE TABLE `tecnicos_backup_20260115` AS SELECT * FROM `tecnicos`;

DROP TABLE IF EXISTS `clientes_backup_20260115`;
CREATE TABLE `clientes_backup_20260115` AS SELECT * FROM `clientes`;

SELECT 'Backup criado com sucesso!' AS status;

-- ====================================================================
-- 2. MIGRAÇÃO DE SENHAS - TECNICOS
-- ====================================================================

-- Corrigir senhas vazias em senha_hash copiando de senha (se existir)
UPDATE `tecnicos`
SET `senha_hash` = `senha`
WHERE (`senha_hash` IS NULL OR `senha_hash` = '')
  AND `senha` IS NOT NULL
  AND `senha` != '';

-- Garantir que todas as senhas em texto plano sejam consistentes
UPDATE `tecnicos`
SET `senha_hash` = 'Rcouto95'
WHERE `senha_hash` = '' OR `senha_hash` IS NULL;

SELECT 'Senhas migradas na tabela tecnicos!' AS status;

-- ====================================================================
-- 3. REMOVER CAMPO DUPLICADO 'senha'
-- ====================================================================

-- Verificar se coluna existe antes de remover
SET @column_exists = (
    SELECT COUNT(*)
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = 'netonerd_chamados'
      AND TABLE_NAME = 'tecnicos'
      AND COLUMN_NAME = 'senha'
);

-- Remover campo 'senha' duplicado (mantém apenas senha_hash)
SET @sql = IF(
    @column_exists > 0,
    'ALTER TABLE `tecnicos` DROP COLUMN `senha`;',
    'SELECT "Campo senha já foi removido" AS status;'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SELECT 'Campo senha removido da tabela tecnicos!' AS status;

-- ====================================================================
-- 4. MIGRAÇÃO DE SENHAS - CLIENTES
-- ====================================================================

-- Garantir que clientes com senhas vazias tenham uma senha padrão
-- ATENÇÃO: Altere estas senhas após o login inicial!
UPDATE `clientes`
SET `senha_hash` = 'senha123'
WHERE `senha_hash` = '' OR `senha_hash` IS NULL;

SELECT 'Senhas verificadas na tabela clientes!' AS status;

-- ====================================================================
-- 5. ADICIONAR ÍNDICES PARA PERFORMANCE
-- ====================================================================

-- Índice para login de clientes (por email)
ALTER TABLE `clientes`
ADD INDEX IF NOT EXISTS `idx_email` (`email`);

-- Índice para login de técnicos (por matrícula)
ALTER TABLE `tecnicos`
ADD INDEX IF NOT EXISTS `idx_matricula` (`matricula`);

-- Índice para sessões
ALTER TABLE `tecnicos`
ADD INDEX IF NOT EXISTS `idx_ativo` (`Ativo`);

SELECT 'Índices criados com sucesso!' AS status;

-- ====================================================================
-- 6. CRIAR VISUALIZAÇÕES ÚTEIS
-- ====================================================================

-- View para listar todos os administradores
DROP VIEW IF EXISTS `view_administradores`;
CREATE VIEW `view_administradores` AS
SELECT
    id,
    nome,
    email,
    matricula,
    status_tecnico,
    Ativo,
    created_at,
    CASE
        WHEN matricula LIKE '%ADM%' THEN 'Admin (padrão ADM)'
        WHEN matricula REGEXP '[0-9]{4}A[0-9]{3}' THEN 'Admin (padrão A###)'
        ELSE 'Admin (outro padrão)'
    END AS tipo_admin
FROM tecnicos
WHERE matricula LIKE '%ADM%'
   OR matricula REGEXP '[0-9]{4}A[0-9]{3}'
ORDER BY created_at DESC;

SELECT 'View de administradores criada!' AS status;

-- View para listar todos os técnicos (não-admins)
DROP VIEW IF EXISTS `view_tecnicos`;
CREATE VIEW `view_tecnicos` AS
SELECT
    id,
    nome,
    email,
    matricula,
    status_tecnico,
    Ativo,
    carro_do_dia,
    created_at
FROM tecnicos
WHERE matricula NOT LIKE '%ADM%'
  AND matricula NOT REGEXP '[0-9]{4}A[0-9]{3}'
ORDER BY created_at DESC;

SELECT 'View de técnicos criada!' AS status;

-- ====================================================================
-- 7. RELATÓRIO DE VERIFICAÇÃO
-- ====================================================================

SELECT '==================== RELATÓRIO FINAL ====================' AS '';

SELECT 'TECNICOS' AS tabela, COUNT(*) AS total_registros FROM tecnicos
UNION ALL
SELECT 'CLIENTES' AS tabela, COUNT(*) AS total_registros FROM clientes
UNION ALL
SELECT 'ADMINISTRADORES' AS tabela, COUNT(*) AS total_registros FROM view_administradores
UNION ALL
SELECT 'TÉCNICOS' AS tabela, COUNT(*) AS total_registros FROM view_tecnicos;

-- Verificar se há senhas vazias restantes
SELECT
    'ATENÇÃO: Senhas vazias encontradas!' AS alerta,
    COUNT(*) AS total
FROM tecnicos
WHERE senha_hash = '' OR senha_hash IS NULL
HAVING COUNT(*) > 0;

SELECT
    'ATENÇÃO: Senhas vazias em clientes!' AS alerta,
    COUNT(*) AS total
FROM clientes
WHERE senha_hash = '' OR senha_hash IS NULL
HAVING COUNT(*) > 0;

SELECT '==================== MIGRAÇÃO CONCLUÍDA ====================' AS '';

-- ====================================================================
-- NOTAS IMPORTANTES:
-- ====================================================================
--
-- 1. BACKUPS CRIADOS:
--    - tecnicos_backup_20260115
--    - clientes_backup_20260115
--
-- 2. ALTERAÇÕES FEITAS:
--    - Campo 'senha' removido da tabela tecnicos
--    - Apenas 'senha_hash' é usado agora
--    - Senhas vazias receberam valor padrão
--
-- 3. APÓS EXECUTAR ESTA MIGRAÇÃO:
--    - Todos os usuários devem fazer login com suas senhas
--    - As senhas serão automaticamente convertidas para bcrypt
--    - O sistema continuará funcionando normalmente
--
-- 4. VIEWS CRIADAS:
--    - view_administradores: Lista apenas admins
--    - view_tecnicos: Lista apenas técnicos
--
-- 5. PARA REVERTER (se necessário):
--    - DROP TABLE tecnicos;
--    - CREATE TABLE tecnicos AS SELECT * FROM tecnicos_backup_20260115;
--
-- ====================================================================
