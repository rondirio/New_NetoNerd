-- Migração: adiciona coluna valor_pago para registrar o valor real pago em contas vencidas
-- Executar UMA VEZ no banco de produção (Hostinger)
-- Data: 2026-03-18

ALTER TABLE despesas
  ADD COLUMN `valor_pago` DECIMAL(10, 2) DEFAULT NULL AFTER `valor`;
