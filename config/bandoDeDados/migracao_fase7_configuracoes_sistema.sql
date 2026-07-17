-- ====================================================================
-- MIGRAÇÃO: Fase 7 do plano de correção — Design System (achado de dado)
-- NetoNerd ITSM
-- Data: 2026-07-15
-- ====================================================================
--
-- Achado durante a migração de admin/configura.php para o Design System:
-- a tela nova deixou visível um bug de dado pré-existente, mesmo padrão
-- do A4 (historico_chamados) e M5 (logs_sistema) já corrigidos nas
-- Fases 5/6 — `configuracoes_sistema.id` não tem PRIMARY KEY nem
-- AUTO_INCREMENT neste ambiente. Diferente das duas tabelas anteriores,
-- aqui nem todas as linhas têm id=0: as chaves reais (sistema_email,
-- email_notificacoes, chamado_protocolo_prefix, tempo_sessao,
-- upload_max_size, upload_allowed_types) já existem corretamente nos
-- grupos certos (email/chamados/seguranca/uploads) com id válido — só o
-- grupo "geral" tem linhas duplicadas com id=0 para essas mesmas chaves
-- (resíduo de uma inserção sem índice único funcionando, já que id=0
-- quebra a PRIMARY KEY implícita). A exceção é "sistema_nome", cuja
-- única linha válida (id=1) está corretamente no grupo "geral".
--
-- Testar em cópia local do banco antes de aplicar em produção. Antes de
-- rodar em produção, confirmar com
--   SELECT chave, COUNT(*) FROM configuracoes_sistema GROUP BY chave HAVING COUNT(*) > 1;
-- se lá também há duplicatas com id=0 — se produção já estiver limpa,
-- pular o DELETE abaixo e aplicar só a normalização de PK.
--
-- Confirmado (2026-07-15, banco local): as 7 chaves existentes têm
-- exatamente 3 linhas cada — 1 com id válido e 2 com id=0. Para 6 delas
-- a linha válida está em outro grupo (email/chamados/seguranca/uploads);
-- para "sistema_nome" a linha válida (id=1) está no próprio grupo
-- "geral", junto das 2 duplicatas com id=0 — por isso o DELETE abaixo
-- remove TODA linha com id=0 (nenhuma delas é a versão correta de
-- nenhuma chave), em vez de filtrar por chave/grupo específico.
-- ====================================================================

-- --------------------------------------------------------------------
-- Remove todas as linhas duplicadas (id=0) — a versão correta de cada
-- uma das 7 chaves já existe com id válido (1 a 7).
-- --------------------------------------------------------------------
DELETE FROM `configuracoes_sistema` WHERE `id` = 0;

-- --------------------------------------------------------------------
-- Normaliza id como PRIMARY KEY AUTO_INCREMENT (mesmo padrão usado em
-- historico_chamados/logs_sistema nas Fases 5/6).
-- --------------------------------------------------------------------
ALTER TABLE `configuracoes_sistema` DROP COLUMN `id`;
ALTER TABLE `configuracoes_sistema` ADD COLUMN `id` INT(11) NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);

-- --------------------------------------------------------------------
-- Índice único em `chave` — impede que o mesmo problema se repita no
-- futuro: com isso, o ON DUPLICATE KEY UPDATE já usado em
-- admin/configura.php passa a funcionar de fato (antes não tinha
-- nenhuma chave única para o MySQL detectar duplicidade).
-- --------------------------------------------------------------------
ALTER TABLE `configuracoes_sistema` ADD UNIQUE KEY `uk_configuracoes_chave` (`chave`);
