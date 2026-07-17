-- ============================================================
-- Remove conta de teste 2026A002 ("Thaina") do banco de producao
-- ============================================================
-- Contexto: matricula usada pelo proprio desenvolvedor durante testes das
-- telas administrativas, com nome da esposa. Nao e funcionaria real.
-- Confirmado pelo usuario em 2026-07-13 como "lixo de teste" a remover.
--
-- Relacionado a C10 (docs/AUDITORIA_ACHADOS.md): essa matricula batia no
-- regex de escalacao de privilegio (\d{4}A\d{3}) e virava admin no login.
--
-- IMPORTANTE: rode isso manualmente contra o banco real (local ou producao)
-- apos confirmar que nao ha chamados/historico vinculados a essa conta que
-- precisem ser preservados. Nao foi executado automaticamente.

-- 1. Conferir o que sera removido antes de rodar o DELETE:
SELECT id, nome, email, matricula, status_tecnico, Ativo
FROM tecnicos
WHERE matricula = '2026A002';

-- 2. Verificar se ha chamados vinculados a essa conta (evitar orfaos, ver A8 em docs/AUDITORIA_ACHADOS.md):
SELECT id, protocolo, titulo, status, tecnico_id
FROM chamados
WHERE tecnico_id = (SELECT id FROM tecnicos WHERE matricula = '2026A002');

-- 3. Se nao houver chamados vinculados (ou apos reatribui-los), remover a conta:
-- DELETE FROM tecnicos WHERE matricula = '2026A002';
