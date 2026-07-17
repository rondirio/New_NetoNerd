-- ====================================================================
-- MIGRAÇÃO: Adiciona status 'fechado' ao ENUM de chamados
-- NetoNerd ITSM — Fase 3 do plano de correção (A1/A7/BE10)
-- Data: 2026-07-13
-- ====================================================================
--
-- Contexto: cliente/fechar_chamado.php tenta gravar status='fechado' ao
-- confirmar a resolução de um chamado, mas esse valor não existe no ENUM
-- real (aberto, em andamento, pendente, resolvido, cancelado) — o UPDATE
-- falha em modo estrito. 'resolvido' passa a significar "técnico
-- terminou o atendimento" e 'fechado' significa "cliente confirmou e
-- encerrou definitivamente".
--
-- Testar em cópia local do banco antes de aplicar em produção.
-- Rodar especificando o banco na linha de comando (nome varia por ambiente:
-- "netonerd" local, "u478690921_netonerd" em produção), ex:
--   mysql -u root netonerd < migracao_fase3_status_fechado.sql
-- ====================================================================

ALTER TABLE `chamados`
MODIFY COLUMN `status` ENUM('aberto','em andamento','pendente','resolvido','fechado','cancelado') NOT NULL DEFAULT 'aberto';

ALTER TABLE `historico_chamados`
MODIFY COLUMN `status_novo` ENUM('aberto','em andamento','pendente','resolvido','fechado','cancelado') NULL;

ALTER TABLE `historico_chamados`
MODIFY COLUMN `status_anterior` ENUM('aberto','em andamento','pendente','resolvido','fechado','cancelado') NULL;

SELECT 'Status fechado adicionado ao ENUM de chamados e historico_chamados!' AS status;
