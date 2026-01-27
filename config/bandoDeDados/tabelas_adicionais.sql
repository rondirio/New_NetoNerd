-- Tabelas adicionais para o sistema NetoNerd
-- Execute este script no banco netonerd_chamados

-- --------------------------------------------------------
-- Tabela para anexos/arquivos dos chamados
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `anexos_chamado` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chamado_id` int(11) NOT NULL,
  `nome_arquivo` varchar(255) NOT NULL,
  `nome_original` varchar(255) NOT NULL,
  `caminho_arquivo` varchar(500) NOT NULL,
  `tipo_arquivo` varchar(100) DEFAULT NULL,
  `tamanho` int(11) DEFAULT NULL,
  `data_upload` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `chamado_id` (`chamado_id`),
  CONSTRAINT `anexos_chamado_ibfk_1` FOREIGN KEY (`chamado_id`) REFERENCES `chamados` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Tabela para respostas/comentários dos chamados
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS `respostas_chamado` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `chamado_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `tipo_autor` enum('cliente','tecnico','admin') NOT NULL,
  `mensagem` text NOT NULL,
  `data_resposta` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `chamado_id` (`chamado_id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `cliente_id` (`cliente_id`),
  CONSTRAINT `respostas_chamado_ibfk_1` FOREIGN KEY (`chamado_id`) REFERENCES `chamados` (`id`) ON DELETE CASCADE,
  CONSTRAINT `respostas_chamado_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `respostas_chamado_ibfk_3` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Modificar tabela chamados para suportar clientes não registrados
-- --------------------------------------------------------

ALTER TABLE `chamados`
  MODIFY `cliente_id` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `cliente_nome` varchar(100) DEFAULT NULL AFTER `cliente_id`,
  ADD COLUMN IF NOT EXISTS `cliente_email` varchar(100) DEFAULT NULL AFTER `cliente_nome`,
  ADD COLUMN IF NOT EXISTS `cliente_telefone` varchar(20) DEFAULT NULL AFTER `cliente_email`,
  ADD COLUMN IF NOT EXISTS `criado_por_admin` int(11) DEFAULT NULL AFTER `cliente_telefone`;

-- Remover constraint NOT NULL do cliente_id (se existir)
-- Nota: pode ser necessário executar manualmente se der erro
-- ALTER TABLE `chamados` DROP FOREIGN KEY `chamados_ibfk_1`;
-- ALTER TABLE `chamados` ADD CONSTRAINT `chamados_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE SET NULL;
