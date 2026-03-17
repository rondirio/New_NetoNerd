-- Script de migração para adicionar campos de parcelamento
-- Execute este SQL se você já tem o sistema instalado

USE despesas_db;

-- Adicionar campos de parcelamento se não existirem
ALTER TABLE despesas 
ADD COLUMN IF NOT EXISTS parcelado BOOLEAN DEFAULT FALSE AFTER dia_vencimento_recorrente,
ADD COLUMN IF NOT EXISTS parcela_atual INT NULL AFTER parcelado,
ADD COLUMN IF NOT EXISTS total_parcelas INT NULL AFTER parcela_atual,
ADD COLUMN IF NOT EXISTS grupo_parcelamento VARCHAR(36) NULL AFTER total_parcelas;

-- Adicionar índice para parcelamento
ALTER TABLE despesas ADD INDEX IF NOT EXISTS idx_parcelado (parcelado);
ALTER TABLE despesas ADD INDEX IF NOT EXISTS idx_grupo_parcelamento (grupo_parcelamento);

SELECT 'Campos de parcelamento adicionados com sucesso!' as mensagem;
