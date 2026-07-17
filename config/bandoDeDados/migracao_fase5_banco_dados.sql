-- ====================================================================
-- MIGRAÇÃO: Fase 5 do plano de correção — Banco de dados: schema e conexão
-- NetoNerd ITSM
-- Data: 2026-07-15
-- ====================================================================
--
-- Cobre A3, A4, BE3 e BE26/C9 do plano de correção (docs/PLANO_DE_CORRECAO.md).
-- A2 não exige migração de schema (já estava resolvida antes desta fase).
--
-- Testar em cópia local do banco antes de aplicar em produção.
-- Rodar especificando o banco na linha de comando (nome varia por ambiente:
-- "netonerd" local, "u478690921_netonerd" em produção), ex:
--   mysql -u root netonerd < migracao_fase5_banco_dados.sql
-- ====================================================================

-- --------------------------------------------------------------------
-- A3: dropar tabela de backup viva dentro do banco de produção.
-- Confirmado por busca no código: nenhum arquivo PHP lê
-- `chamados_backup_20260121` — só é criada por migracao_sistema_chamados.sql
-- como backup de segurança pontual, já cumprida.
-- --------------------------------------------------------------------
DROP TABLE IF EXISTS `chamados_backup_20260121`;

-- --------------------------------------------------------------------
-- Achado novo, descoberto ao testar esta migração localmente (não estava
-- em nenhum documento de auditoria anterior): `historico_chamados.id`
-- não tem PRIMARY KEY nem AUTO_INCREMENT neste ambiente — todas as
-- linhas existentes têm id=0. O dump app/Database/NetoNerd_BD.sql (árvore
-- morta) tem a definição correta, mas nunca foi aplicada neste banco.
--
-- ATENÇÃO antes de rodar em produção: confirmar primeiro se produção tem
-- o mesmo problema (`SHOW CREATE TABLE historico_chamados`). Se as linhas
-- também estiverem todas com id=0 lá, esta migração as renumera pela
-- ordem física de armazenamento (não por data_alteracao) — aceitável
-- porque id nunca foi uma PK confiável para ordenar por lá mesmo; usar
-- data_alteracao para ordenação cronológica caso necessário depois.
-- --------------------------------------------------------------------
ALTER TABLE `historico_chamados` DROP COLUMN `id`;
ALTER TABLE `historico_chamados` ADD COLUMN `id` INT(11) NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id`);

-- --------------------------------------------------------------------
-- A4: remover o trigger `registrar_historico_status`.
-- O trigger sempre gravava usuario_id = NEW.tecnico_id, o que é errado
-- sempre que quem muda o status não é o técnico atribuído (cliente
-- fechando o chamado, admin abrindo ordem de serviço). Também causava
-- linhas duplicadas em historico_chamados nos 2 pontos que já faziam
-- INSERT manual (cliente/fechar_chamado.php, tecnico/processar_resolucao.php).
-- Todos os pontos que mudam chamados.status agora chamam
-- registrarHistoricoStatus() explicitamente (controller/historico_chamados.php),
-- passando o usuario_id de quem está de fato logado.
-- --------------------------------------------------------------------
DROP TRIGGER IF EXISTS `registrar_historico_status`;

-- --------------------------------------------------------------------
-- BE3: unificar `tecnicos.Ativo` e `tecnicos.status_tecnico`.
-- status_tecnico ('Active'/'Inactive') é a coluna mantida — é a única
-- editada pela tela de edição de técnico (admin/editar_tecnico.php) e a
-- mais lida no restante do sistema. Ativo é removida após sincronizar
-- os dados, para nenhum técnico "desativado" continuar recebendo
-- chamados novos via admin/atribuir_chamados.php e
-- admin/gerar_ordem_servico.php (que hoje filtram por Ativo = 1).
-- --------------------------------------------------------------------
UPDATE `tecnicos` SET `Ativo` = IF(`status_tecnico` = 'Active', 1, 0);

ALTER TABLE `tecnicos` DROP COLUMN `Ativo`;

-- --------------------------------------------------------------------
-- BE26 / C9: remover coluna `tecnicos.senha`, redundante com
-- `senha_hash` e sem uso funcional no login atual (confirmado por busca
-- no código: nenhum INSERT/UPDATE/SELECT ativo referencia esta coluna).
--
-- Pré-requisito (rodar antes desta migração, feito à parte por ser dado,
-- não schema): confirmar que senha_hash está populada e devidamente
-- hasheada (bcrypt/argon2, não texto puro) para todos os técnicos —
-- ver achado C9 original sobre o registro id=2 em produção. Forçar troca
-- de senha da pessoa afetada antes de derrubar a coluna `senha`.
-- --------------------------------------------------------------------
ALTER TABLE `tecnicos` DROP COLUMN `senha`;

SELECT 'Fase 5 aplicada: chamados_backup_20260121 removida, trigger registrar_historico_status removido, tecnicos.Ativo unificada em status_tecnico, tecnicos.senha removida.' AS status;
