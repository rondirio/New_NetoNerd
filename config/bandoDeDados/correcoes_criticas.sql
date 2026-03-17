-- ============================================================
-- Script de Correções Críticas — NetoNerd ITSM
-- Data: 2026-03-17 (revisado após análise do dump de produção)
--
-- INSTRUÇÕES:
--   1. No phpMyAdmin, selecione o banco u478690921_netonerd
--   2. Clique em "SQL" e execute este script
--
-- O que este script FAZ:
--   - Adiciona coluna `cpf` na tabela `clientes` (única coluna faltando)
--
-- O que NÃO precisa fazer (já existe em produção):
--   - tecnicos.telefone            ✅ já existe
--   - chamados.categoria_id        ✅ já existe
--   - chamados.tempo_atendimento_minutos ✅ já existe
--   - tabela categorias_chamado    ✅ já existe
--   - FK ordens_servico.created_by ✅ já aponta para tecnicos (fk_os_created_by)
-- ============================================================

ALTER TABLE `clientes`
  ADD COLUMN IF NOT EXISTS `cpf` VARCHAR(14) DEFAULT NULL AFTER `telefone`;
