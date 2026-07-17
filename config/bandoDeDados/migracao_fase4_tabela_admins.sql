-- ====================================================================
-- MIGRAÇÃO: Separa admins em tabela própria (substitui a tentativa
-- anterior de coluna tecnicos.role — ver migracao_fase4_role_tecnico.sql)
-- NetoNerd ITSM — Fase 4 do plano de correção (C10/S2/BE2)
-- Data: 2026-07-13
-- ====================================================================
--
-- Contexto: isAdmin() decidia o cargo por regex sobre a matrícula
-- (stripos($matricula,'ADM') ou preg_match('/\d{4}A\d{3}/')), replicado
-- de forma incompatível em 3 lugares. A convenção matricula com "A" =
-- admin é intencional (F = funcionário/técnico), mas usá-la como ÚNICA
-- fonte de verdade permite que qualquer pessoa se autopromova a admin
-- só escolhendo uma matrícula no padrão certo no cadastro público (C10).
--
-- Decisão do usuário: separar fisicamente em tabela `admins`, distinta
-- de `tecnicos` — não uma coluna de role. `usuarios` (tabela vestígio,
-- não usada como fonte de dado ativo, só lida em 1 lugar para checar
-- FK antes de logar) é removida junto, pois seria fonte de confusão.
--
-- IMPORTANTE: os `id` dos admins são preservados (não re-gerados) porque
-- `chamados.criado_por_admin` e `chamado_atribuicoes.admin_id` já têm
-- dados reais apontando para esses IDs (nenhuma FK formal existe hoje,
-- então nada impede a migração, mas os valores já gravados precisam
-- continuar corretos).
--
-- Ambiente de teste — sem preocupação em preservar dados de cliente;
-- produção será adequada ao schema atualizado no lançamento único.
--
-- Reverte a migração anterior (migracao_fase4_role_tecnico.sql) se ela
-- já tiver sido aplicada neste ambiente.
--
-- Rodar especificando o banco na linha de comando, ex:
--   mysql -u root netonerd < migracao_fase4_tabela_admins.sql
-- ====================================================================

-- Reverte a coluna role, se existir (tentativa anterior, descartada)
ALTER TABLE `tecnicos` DROP COLUMN IF EXISTS `role`;

-- Tabela vestígio, não usada como fonte de dado ativo no fluxo real
DROP TABLE IF EXISTS `usuarios`;

-- ordens_servico.tecnico_id e created_by tinham FK formal para tecnicos.id,
-- mas dados reais mostram os dois campos apontando ora para técnico ora
-- para admin (created_by=3 em toda OS, tecnico_id incluindo os próprios
-- admins em OS antigas) — ou seja, a FK nunca refletiu a regra real do
-- negócio. Removida: o campo passa a ser int solto, como tecnico_id em
-- chamados e admin_id em chamado_atribuicoes (nenhuma das duas tem FK
-- formal hoje, mesmo referenciando pessoas de tabelas diferentes).
ALTER TABLE `ordens_servico` DROP FOREIGN KEY `ordens_servico_ibfk_2`;
ALTER TABLE `ordens_servico` DROP FOREIGN KEY `fk_os_created_by`;

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `matricula` varchar(20) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `Ativo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `matricula` (`matricula`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Move os registros hoje identificados como admin pelo padrão de matrícula
INSERT INTO `admins` (`id`, `nome`, `email`, `matricula`, `senha_hash`, `Ativo`, `created_at`)
SELECT `id`, `nome`, `email`, `matricula`, `senha_hash`, `Ativo`, `created_at`
FROM `tecnicos`
WHERE matricula LIKE '%ADM%' OR matricula LIKE '%adm%' OR matricula REGEXP '[0-9]{4}A[0-9]{3}';

-- Ajusta o AUTO_INCREMENT de admins para continuar depois do maior id migrado
ALTER TABLE `admins` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
SET @next_id = (SELECT COALESCE(MAX(id), 0) + 1 FROM `admins`);
SET @sql = CONCAT('ALTER TABLE `admins` AUTO_INCREMENT = ', @next_id);
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Remove os mesmos registros de tecnicos (agora vivem só em admins)
DELETE FROM `tecnicos`
WHERE matricula LIKE '%ADM%' OR matricula LIKE '%adm%' OR matricula REGEXP '[0-9]{4}A[0-9]{3}';

SELECT 'Tabela admins criada e populada; tecnicos agora contém só técnicos!' AS status;
SELECT id, nome, matricula FROM admins;
SELECT id, nome, matricula FROM tecnicos;
