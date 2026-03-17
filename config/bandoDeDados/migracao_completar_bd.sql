-- ============================================================
-- Script de Migração para Completar o Banco de Dados
-- NetoNerd ITSM - Sistema de Gerenciamento de Chamados
-- Data: 2026-01-14
-- ============================================================

USE u478690921_netonerd; -- corrigido: nome real do banco em producao

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================================
-- 1. CRIAÇÃO DA TABELA DE CATEGORIAS
-- ============================================================

CREATE TABLE IF NOT EXISTS `categorias_chamado` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(100) NOT NULL UNIQUE,
  `descricao` TEXT,
  `cor` VARCHAR(7) DEFAULT '#007bff',
  `icone` VARCHAR(50) DEFAULT 'fa-ticket',
  `ativo` TINYINT(1) NOT NULL DEFAULT 1,
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_categoria_ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Inserir categorias padrão
INSERT INTO `categorias_chamado` (`nome`, `descricao`, `cor`, `icone`) VALUES
('Hardware', 'Problemas relacionados a equipamentos físicos (computadores, impressoras, periféricos)', '#dc3545', 'fa-desktop'),
('Software', 'Problemas com aplicativos, sistemas operacionais e licenças', '#007bff', 'fa-code'),
('Rede', 'Problemas de conectividade, internet, Wi-Fi e infraestrutura de rede', '#28a745', 'fa-network-wired'),
('Email', 'Problemas com contas de email, envio e recebimento de mensagens', '#ffc107', 'fa-envelope'),
('Acesso', 'Problemas com senhas, permissões e acessos a sistemas', '#6c757d', 'fa-key'),
('Impressora', 'Problemas com impressoras, scanners e dispositivos de impressão', '#17a2b8', 'fa-print'),
('Telefonia', 'Problemas com sistemas de telefonia, ramais e comunicação', '#6610f2', 'fa-phone'),
('Outros', 'Demais solicitações não categorizadas', '#fd7e14', 'fa-question-circle')
ON DUPLICATE KEY UPDATE nome=nome;

-- ============================================================
-- 2. CRIAÇÃO DA TABELA DE RESPOSTAS/COMENTÁRIOS
-- ============================================================

CREATE TABLE IF NOT EXISTS `respostas_chamado` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `chamado_id` INT(11) NOT NULL,
  `usuario_id` INT(11) NOT NULL,
  `tipo_usuario` ENUM('cliente', 'tecnico', 'admin') NOT NULL,
  `resposta` TEXT NOT NULL,
  `tipo_resposta` ENUM('publica', 'interna') DEFAULT 'publica',
  `tempo_gasto` INT(11) DEFAULT NULL COMMENT 'Tempo gasto em minutos',
  `data_resposta` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_resposta_chamado` (`chamado_id`),
  KEY `idx_resposta_data` (`data_resposta`),
  KEY `idx_resposta_tipo` (`tipo_resposta`),
  CONSTRAINT `fk_resposta_chamado` FOREIGN KEY (`chamado_id`) REFERENCES `chamados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 3. CRIAÇÃO DA TABELA DE ANEXOS
-- ============================================================

CREATE TABLE IF NOT EXISTS `anexos_chamado` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `chamado_id` INT(11) NOT NULL,
  `resposta_id` INT(11) DEFAULT NULL,
  `nome_arquivo` VARCHAR(255) NOT NULL,
  `nome_original` VARCHAR(255) NOT NULL,
  `caminho_arquivo` VARCHAR(512) NOT NULL,
  `tipo_mime` VARCHAR(100),
  `tamanho_bytes` INT(11) NOT NULL,
  `usuario_upload_id` INT(11) NOT NULL,
  `tipo_usuario` ENUM('cliente', 'tecnico', 'admin') NOT NULL,
  `data_upload` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_anexo_chamado` (`chamado_id`),
  KEY `idx_anexo_resposta` (`resposta_id`),
  KEY `idx_anexo_data` (`data_upload`),
  CONSTRAINT `fk_anexo_chamado` FOREIGN KEY (`chamado_id`) REFERENCES `chamados` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_anexo_resposta` FOREIGN KEY (`resposta_id`) REFERENCES `respostas_chamado` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 4. CRIAÇÃO DA TABELA DE BASE DE CONHECIMENTO
-- ============================================================

CREATE TABLE IF NOT EXISTS `base_conhecimento` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `categoria_id` INT(11) NOT NULL,
  `titulo` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) NOT NULL UNIQUE,
  `conteudo` LONGTEXT NOT NULL,
  `palavras_chave` TEXT,
  `autor_id` INT(11) NOT NULL,
  `visualizacoes` INT(11) DEFAULT 0,
  `util_sim` INT(11) DEFAULT 0,
  `util_nao` INT(11) DEFAULT 0,
  `publicado` TINYINT(1) NOT NULL DEFAULT 1,
  `destaque` TINYINT(1) NOT NULL DEFAULT 0,
  `data_criacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `data_atualizacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `idx_kb_categoria` (`categoria_id`),
  KEY `idx_kb_publicado` (`publicado`),
  KEY `idx_kb_destaque` (`destaque`),
  FULLTEXT KEY `idx_kb_busca` (`titulo`, `conteudo`, `palavras_chave`),
  CONSTRAINT `fk_kb_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_chamado` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Inserir artigos de exemplo na base de conhecimento
INSERT INTO `base_conhecimento` (`categoria_id`, `titulo`, `slug`, `conteudo`, `palavras_chave`, `autor_id`, `publicado`, `destaque`) VALUES
(2, 'Como Resetar sua Senha do Windows', 'resetar-senha-windows',
'<h3>Resetando sua Senha do Windows</h3>
<p>Se você esqueceu sua senha do Windows, siga estes passos:</p>
<ol>
<li>Na tela de login, clique em "Esqueci minha senha"</li>
<li>Responda às perguntas de segurança configuradas</li>
<li>Crie uma nova senha</li>
<li>Faça login com a nova senha</li>
</ol>
<p><strong>Importante:</strong> Se você não conseguir resetar, entre em contato com o suporte técnico.</p>',
'senha, windows, login, resetar, recuperar', 1, 1, 1),

(3, 'Problemas de Conexão com Wi-Fi', 'problemas-wifi',
'<h3>Solucionando Problemas de Wi-Fi</h3>
<p>Se você está com problemas de conexão Wi-Fi, tente:</p>
<ul>
<li>Desligar e ligar o Wi-Fi do seu dispositivo</li>
<li>Esquecer a rede e reconectar</li>
<li>Reiniciar o roteador</li>
<li>Verificar se outros dispositivos conseguem conectar</li>
<li>Verificar se você está próximo ao roteador</li>
</ul>
<p>Se o problema persistir, abra um chamado para o suporte técnico.</p>',
'wifi, internet, rede, conexão, wireless', 1, 1, 1),

(6, 'Configurar Impressora em Rede', 'configurar-impressora-rede',
'<h3>Como Configurar uma Impressora em Rede</h3>
<p><strong>Windows 10/11:</strong></p>
<ol>
<li>Abra "Configurações" > "Dispositivos" > "Impressoras e scanners"</li>
<li>Clique em "Adicionar impressora ou scanner"</li>
<li>Aguarde o Windows detectar a impressora na rede</li>
<li>Selecione a impressora e clique em "Adicionar dispositivo"</li>
</ol>
<p>Se a impressora não for detectada automaticamente, clique em "A impressora que desejo não está na lista" e siga as instruções.</p>',
'impressora, rede, configurar, instalar, scanner', 1, 1, 0);

-- ============================================================
-- 5. ALTERAÇÕES NA TABELA CHAMADOS
-- ============================================================

-- Adicionar coluna de categoria como FK (se ainda não existir)
ALTER TABLE `chamados`
ADD COLUMN `categoria_id` INT(11) DEFAULT NULL AFTER `equipamento_id`,
ADD KEY `idx_chamados_categoria` (`categoria_id`);

-- Migrar dados existentes da coluna 'categoria' (VARCHAR) para 'categoria_id' (INT)
-- Tentativa de mapeamento básico
UPDATE `chamados` c
INNER JOIN `categorias_chamado` cat ON LOWER(c.categoria) = LOWER(cat.nome)
SET c.categoria_id = cat.id
WHERE c.categoria_id IS NULL AND c.categoria IS NOT NULL;

-- Definir categoria padrão para chamados sem categoria
UPDATE `chamados`
SET categoria_id = (SELECT id FROM categorias_chamado WHERE nome = 'Outros' LIMIT 1)
WHERE categoria_id IS NULL;

-- Adicionar a constraint de FK após popular os dados
ALTER TABLE `chamados`
ADD CONSTRAINT `fk_chamados_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias_chamado` (`id`) ON DELETE SET NULL;

-- Adicionar campo para última atualização
ALTER TABLE `chamados`
ADD COLUMN `data_atualizacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `data_fechamento`;

-- ============================================================
-- 6. CRIAÇÃO DE VIEWS ÚTEIS
-- ============================================================

-- View de chamados com informações completas
CREATE OR REPLACE VIEW `vw_chamados_completos` AS
SELECT
    c.id,
    c.protocolo,
    c.titulo,
    c.descricao,
    c.status,
    c.prioridade,
    c.data_abertura,
    c.data_fechamento,
    c.data_atualizacao,
    cat.nome AS categoria_nome,
    cat.cor AS categoria_cor,
    cat.icone AS categoria_icone,
    cli.nome AS cliente_nome,
    cli.email AS cliente_email,
    cli.telefone AS cliente_telefone,
    tec.nome AS tecnico_nome,
    tec.email AS tecnico_email,
    tec.matricula AS tecnico_matricula,
    (SELECT COUNT(*) FROM respostas_chamado WHERE chamado_id = c.id) AS total_respostas,
    (SELECT COUNT(*) FROM anexos_chamado WHERE chamado_id = c.id) AS total_anexos,
    TIMESTAMPDIFF(HOUR, c.data_abertura, COALESCE(c.data_fechamento, NOW())) AS tempo_decorrido_horas
FROM chamados c
LEFT JOIN categorias_chamado cat ON c.categoria_id = cat.id
LEFT JOIN clientes cli ON c.cliente_id = cli.id
LEFT JOIN tecnicos tec ON c.tecnico_id = tec.id;

-- View de estatísticas por técnico
CREATE OR REPLACE VIEW `vw_estatisticas_tecnico` AS
SELECT
    t.id,
    t.nome,
    t.email,
    t.matricula,
    t.status_tecnico,
    COUNT(DISTINCT c.id) AS total_chamados,
    COUNT(DISTINCT CASE WHEN c.status = 'resolvido' THEN c.id END) AS chamados_resolvidos,
    COUNT(DISTINCT CASE WHEN c.status IN ('aberto', 'em andamento') THEN c.id END) AS chamados_abertos,
    AVG(TIMESTAMPDIFF(HOUR, c.data_abertura, c.data_fechamento)) AS tempo_medio_resolucao_horas,
    MAX(c.data_atualizacao) AS ultima_atividade
FROM tecnicos t
LEFT JOIN chamados c ON t.id = c.tecnico_id
WHERE t.Ativo = 1
GROUP BY t.id, t.nome, t.email, t.matricula, t.status_tecnico;

-- View de estatísticas por categoria
CREATE OR REPLACE VIEW `vw_estatisticas_categoria` AS
SELECT
    cat.id,
    cat.nome,
    cat.descricao,
    cat.cor,
    cat.icone,
    COUNT(c.id) AS total_chamados,
    COUNT(CASE WHEN c.status = 'resolvido' THEN 1 END) AS chamados_resolvidos,
    COUNT(CASE WHEN c.status IN ('aberto', 'em andamento') THEN 1 END) AS chamados_abertos,
    AVG(TIMESTAMPDIFF(HOUR, c.data_abertura, c.data_fechamento)) AS tempo_medio_resolucao_horas
FROM categorias_chamado cat
LEFT JOIN chamados c ON cat.id = c.categoria_id
WHERE cat.ativo = 1
GROUP BY cat.id, cat.nome, cat.descricao, cat.cor, cat.icone;

-- ============================================================
-- 7. TRIGGERS ADICIONAIS
-- ============================================================

-- Trigger para atualizar contador de visualizações na base de conhecimento
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `atualizar_visualizacao_kb`
BEFORE UPDATE ON `base_conhecimento`
FOR EACH ROW
BEGIN
    IF NEW.visualizacoes > OLD.visualizacoes THEN
        SET NEW.data_atualizacao = CURRENT_TIMESTAMP;
    END IF;
END$$
DELIMITER ;

-- Trigger para registrar respostas no histórico
DELIMITER $$
CREATE TRIGGER IF NOT EXISTS `registrar_resposta_historico`
AFTER INSERT ON `respostas_chamado`
FOR EACH ROW
BEGIN
    INSERT INTO logs_sistema (usuario_id, acao)
    VALUES (NEW.usuario_id, CONCAT('Nova resposta adicionada ao chamado #', NEW.chamado_id));
END$$
DELIMITER ;

-- ============================================================
-- 8. ÍNDICES ADICIONAIS PARA PERFORMANCE
-- ============================================================

-- Índices para melhorar performance de consultas
ALTER TABLE `chamados`
ADD INDEX `idx_chamados_datas` (`data_abertura`, `data_fechamento`),
ADD INDEX `idx_chamados_status_prioridade` (`status`, `prioridade`);

ALTER TABLE `respostas_chamado`
ADD INDEX `idx_resposta_usuario` (`usuario_id`, `tipo_usuario`);

ALTER TABLE `clientes`
ADD INDEX `idx_cliente_nome` (`nome`);

ALTER TABLE `tecnicos`
ADD INDEX `idx_tecnico_status` (`status_tecnico`, `Ativo`);

-- ============================================================
-- 9. CONFIGURAÇÕES DO SISTEMA
-- ============================================================

-- Criar tabela de configurações gerais
CREATE TABLE IF NOT EXISTS `configuracoes_sistema` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `chave` VARCHAR(100) NOT NULL UNIQUE,
  `valor` TEXT NOT NULL,
  `tipo` ENUM('string', 'integer', 'boolean', 'json') DEFAULT 'string',
  `descricao` TEXT,
  `grupo` VARCHAR(50) DEFAULT 'geral',
  `data_atualizacao` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `chave` (`chave`),
  KEY `idx_config_grupo` (`grupo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Inserir configurações padrão
INSERT INTO `configuracoes_sistema` (`chave`, `valor`, `tipo`, `descricao`, `grupo`) VALUES
('sistema_nome', 'NetoNerd ITSM', 'string', 'Nome do sistema exibido', 'geral'),
('sistema_email', 'suporte@netonerd.com.br', 'string', 'Email principal do sistema', 'email'),
('email_notificacoes', '1', 'boolean', 'Ativar notificações por email', 'email'),
('chamado_protocolo_prefix', 'NN', 'string', 'Prefixo para protocolos de chamados', 'chamados'),
('tempo_sessao', '3600', 'integer', 'Tempo de sessão em segundos', 'seguranca'),
('upload_max_size', '10485760', 'integer', 'Tamanho máximo de upload em bytes (10MB)', 'uploads'),
('upload_allowed_types', '["jpg","jpeg","png","pdf","doc","docx","txt","zip"]', 'json', 'Tipos de arquivo permitidos para upload', 'uploads')
ON DUPLICATE KEY UPDATE chave=chave;

-- ============================================================
-- 10. TABELA DE TENTATIVAS DE LOGIN
-- ============================================================

CREATE TABLE IF NOT EXISTS `tentativas_login` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `tipo_usuario` ENUM('cliente', 'tecnico', 'admin') NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `sucesso` TINYINT(1) NOT NULL DEFAULT 0,
  `mensagem` VARCHAR(255),
  `data_tentativa` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tentativa_email` (`email`, `tipo_usuario`),
  KEY `idx_tentativa_ip` (`ip_address`),
  KEY `idx_tentativa_data` (`data_tentativa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================================
-- 11. LIMPEZA E OTIMIZAÇÕES
-- ============================================================

-- Otimizar todas as tabelas
OPTIMIZE TABLE `chamados`;
OPTIMIZE TABLE `clientes`;
OPTIMIZE TABLE `tecnicos`;
OPTIMIZE TABLE `usuarios`;
OPTIMIZE TABLE `equipamentos`;
OPTIMIZE TABLE `historico_chamados`;

COMMIT;

-- ============================================================
-- FIM DA MIGRAÇÃO
-- ============================================================

SELECT 'Migração concluída com sucesso!' AS status;
SELECT 'Tabelas criadas:' AS info;
SELECT
    'categorias_chamado' AS tabela,
    COUNT(*) AS registros
FROM categorias_chamado
UNION ALL
SELECT 'base_conhecimento', COUNT(*) FROM base_conhecimento;
