-- ====================================================================
-- MIGRAÇÃO: Fase 6 do plano de correção — Estrutural / LGPD
-- NetoNerd ITSM
-- Data: 2026-07-15
-- ====================================================================
--
-- Cobre M5 do plano de correção (docs/PLANO_DE_CORRECAO.md).
--
-- Testar em cópia local do banco antes de aplicar em produção.
-- Rodar especificando o banco na linha de comando (nome varia por ambiente:
-- "netonerd" local, "u478690921_netonerd" em produção), ex:
--   mysql -u root netonerd < migracao_fase6_lgpd_estrutural.sql
-- ====================================================================

-- --------------------------------------------------------------------
-- Achado novo, descoberto ao testar esta migração localmente (mesmo
-- problema do A4/historico_chamados corrigido na Fase 5): `logs_sistema.id`
-- não tem PRIMARY KEY nem AUTO_INCREMENT neste ambiente — as 111 linhas
-- existentes têm todas id=0.
--
-- ATENÇÃO antes de rodar em produção: confirmar primeiro se produção tem
-- o mesmo problema (`SHOW CREATE TABLE logs_sistema`). Se as linhas também
-- estiverem todas com id=0 lá, esta migração as renumera pela ordem física
-- de armazenamento (não por data_hora) — aceitável pelo mesmo motivo do A4:
-- id nunca foi PK confiável para ordenar; usar data_hora para ordenação
-- cronológica caso necessário depois.
-- --------------------------------------------------------------------
ALTER TABLE `logs_sistema` DROP COLUMN `id`;
ALTER TABLE `logs_sistema` ADD COLUMN `id` INT(11) NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);

-- --------------------------------------------------------------------
-- M5: logs_sistema não tinha IP nem identificação do recurso afetado,
-- insuficiente para reconstruir escopo de um incidente (art. 48 LGPD).
-- Centralizado em registrarLogSistema() (controller/auth_middleware.php),
-- chamado nos 6 pontos reais de INSERT (login cliente/técnico, atribuição
-- de chamado, exclusão de técnico, ação do técnico no chamado, resolução).
-- --------------------------------------------------------------------
ALTER TABLE `logs_sistema`
    ADD COLUMN `ip_address` VARCHAR(45) NULL AFTER `acao`,
    ADD COLUMN `tipo_recurso` VARCHAR(50) NULL AFTER `ip_address`,
    ADD COLUMN `recurso_id` INT(11) NULL AFTER `tipo_recurso`,
    ADD INDEX `idx_tipo_recurso` (`tipo_recurso`, `recurso_id`);
