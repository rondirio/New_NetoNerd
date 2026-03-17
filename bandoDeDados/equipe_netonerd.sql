-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 09/07/2025 às 18:48
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `netonerd_adm_super`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `equipe_netonerd`
--

CREATE TABLE `equipe_netonerd` (
  `id` int(11) NOT NULL,
  `nome_completo` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `matricula` varchar(50) NOT NULL,
  `senha_hash` varchar(255) NOT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `equipe_netonerd`
--

INSERT INTO `equipe_netonerd` (`id`, `nome_completo`, `email`, `matricula`, `senha_hash`, `cargo`, `ativo`) VALUES
(3, 'Rondineli Couto', 'rondi.rio@hotmail.com', 'ADM202501', '$2y$10$YTErPN5kgZ0nionNHWxeludLlC4c5boS2n/y4B3.kyGHpZDLe8xki', 'CEO', 1);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `equipe_netonerd`
--
ALTER TABLE `equipe_netonerd`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `matricula` (`matricula`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `equipe_netonerd`
--
ALTER TABLE `equipe_netonerd`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
