-- Script de Criação do Banco de Dados
-- Sistema de Gerenciamento de Despesas Multi-usuário

-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS despesas_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE despesas_db;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    cpf VARCHAR(14) NULL UNIQUE,
    foto_perfil VARCHAR(255) NULL,
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultimo_acesso TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_cpf (cpf),
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de despesas (com relacionamento ao usuário)
CREATE TABLE IF NOT EXISTS despesas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome_conta VARCHAR(255) NOT NULL,
    descricao TEXT,
    valor DECIMAL(10, 2) NOT NULL,
    data_vencimento DATE NOT NULL,
    modo_pagamento ENUM('Dinheiro', 'Cartão Crédito', 'Cartão Débito', 'PIX', 'Boleto', 'Transferência') NOT NULL,
    debito_automatico BOOLEAN DEFAULT FALSE,
    recorrente BOOLEAN DEFAULT FALSE,
    dia_vencimento_recorrente INT NULL,
    parcelado BOOLEAN DEFAULT FALSE,
    total_parcelas INT NULL,
    parcela_atual INT NULL,
    parcela_grupo VARCHAR(50) NULL,
    status ENUM('Pendente', 'Pago', 'Vencido') DEFAULT 'Pendente',
    data_pagamento DATE NULL,
    categoria VARCHAR(100),
    observacoes TEXT,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario_id (usuario_id),
    INDEX idx_data_vencimento (data_vencimento),
    INDEX idx_status (status),
    INDEX idx_recorrente (recorrente),
    INDEX idx_parcelado (parcelado),
    INDEX idx_parcela_grupo (parcela_grupo),
    INDEX idx_usuario_data (usuario_id, data_vencimento)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de sessões (segurança adicional)
CREATE TABLE IF NOT EXISTS sessoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    ip_address VARCHAR(45),
    user_agent VARCHAR(255),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expira_em TIMESTAMP NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expira (expira_em)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir usuário de exemplo (email: admin@exemplo.com | senha: admin123)
INSERT INTO usuarios (nome, email, senha, ativo) VALUES
('Administrador', 'admin@exemplo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', TRUE);

-- View para relatórios rápidos (por usuário)
CREATE OR REPLACE VIEW v_resumo_mensal AS
SELECT 
    usuario_id,
    YEAR(data_vencimento) as ano,
    MONTH(data_vencimento) as mes,
    COUNT(*) as total_contas,
    COUNT(CASE WHEN status = 'Pago' THEN 1 END) as contas_pagas,
    COUNT(CASE WHEN status = 'Pendente' THEN 1 END) as contas_pendentes,
    COUNT(CASE WHEN status = 'Vencido' THEN 1 END) as contas_vencidas,
    COALESCE(SUM(CASE WHEN status = 'Pago' THEN valor END), 0) as valor_pago,
    COALESCE(SUM(CASE WHEN status IN ('Pendente', 'Vencido') THEN valor END), 0) as valor_pendente,
    COALESCE(SUM(valor), 0) as valor_total
FROM despesas
GROUP BY usuario_id, YEAR(data_vencimento), MONTH(data_vencimento);

-- Procedure para atualizar despesas vencidas (por usuário)
DELIMITER //
CREATE PROCEDURE sp_atualizar_vencidas(IN p_usuario_id INT)
BEGIN
    UPDATE despesas 
    SET status = 'Vencido' 
    WHERE usuario_id = p_usuario_id
    AND data_vencimento < CURDATE() 
    AND status = 'Pendente';
END //
DELIMITER ;

-- Procedure para gerar despesas recorrentes do próximo mês (por usuário)
DELIMITER //
CREATE PROCEDURE sp_gerar_recorrentes(IN p_usuario_id INT)
BEGIN
    DECLARE proxMes INT;
    DECLARE proxAno INT;
    DECLARE mesAtual INT;
    DECLARE anoAtual INT;
    
    SET mesAtual = MONTH(CURDATE());
    SET anoAtual = YEAR(CURDATE());
    
    -- Calcular próximo mês e ano
    IF mesAtual = 12 THEN
        SET proxMes = 1;
        SET proxAno = anoAtual + 1;
    ELSE
        SET proxMes = mesAtual + 1;
        SET proxAno = anoAtual;
    END IF;
    
    -- Inserir despesas recorrentes para o próximo mês (apenas se ainda não existirem)
    INSERT INTO despesas (
        usuario_id,
        nome_conta, 
        descricao, 
        valor, 
        data_vencimento, 
        modo_pagamento, 
        debito_automatico, 
        recorrente, 
        dia_vencimento_recorrente, 
        categoria, 
        observacoes,
        status
    )
    SELECT 
        usuario_id,
        nome_conta,
        descricao,
        valor,
        DATE(CONCAT(proxAno, '-', LPAD(proxMes, 2, '0'), '-', LPAD(dia_vencimento_recorrente, 2, '0'))) as nova_data,
        modo_pagamento,
        debito_automatico,
        recorrente,
        dia_vencimento_recorrente,
        categoria,
        observacoes,
        'Pendente'
    FROM despesas
    WHERE usuario_id = p_usuario_id
    AND recorrente = TRUE
    AND NOT EXISTS (
        SELECT 1 FROM despesas d2
        WHERE d2.usuario_id = despesas.usuario_id
        AND d2.nome_conta = despesas.nome_conta
        AND MONTH(d2.data_vencimento) = proxMes
        AND YEAR(d2.data_vencimento) = proxAno
    );
    
    SELECT ROW_COUNT() as despesas_criadas;
END //
DELIMITER ;

-- Procedure para limpar sessões expiradas
DELIMITER //
CREATE PROCEDURE sp_limpar_sessoes_expiradas()
BEGIN
    DELETE FROM sessoes WHERE expira_em < NOW();
END //
DELIMITER ;

-- Event para executar automaticamente todos os dias às 00:00
-- (Requer que o MySQL tenha event_scheduler habilitado)
CREATE EVENT IF NOT EXISTS evt_limpar_sessoes
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_DATE + INTERVAL 1 DAY
DO
CALL sp_limpar_sessoes_expiradas();

-- Visualizar configuração
SELECT 'Banco de dados criado com sucesso!' as mensagem;
SELECT 'Usuário de exemplo criado: admin@exemplo.com / admin123' as info;
SHOW TABLES;
