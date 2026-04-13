-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Autor: Emilio Eduardo Maciel
-- Host: localhost
-- Tempo de geração: 14-Abr-2026 às 00:12
-- Versão do servidor: 10.4.32-MariaDB
-- versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `agendanexus`
--

CREATE DATABASE IF NOT EXISTS agendanexus
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE agendanexus;

-- --------------------------------------------------------

--
-- Estrutura da tabela `agendamentos`
--

CREATE TABLE `agendamentos` (
  `id` int(10) UNSIGNED NOT NULL,
  `profissional_id` int(10) UNSIGNED NOT NULL COMMENT 'FK → usuarios (perfil = profissional)',
  `cliente_id` int(10) UNSIGNED NOT NULL COMMENT 'FK → usuarios (perfil = cliente)',
  `tipo_servico` enum('consulta','retorno','procedimento','avaliacao') NOT NULL DEFAULT 'consulta',
  `sala` varchar(60) DEFAULT NULL COMMENT 'Ex: Sala 01 - Consultório A',
  `data_hora` datetime NOT NULL COMMENT 'Data e horário de início do atendimento',
  `duracao_min` smallint(5) UNSIGNED NOT NULL DEFAULT 60 COMMENT 'Duração em minutos',
  `status` enum('pendente','confirmado','cancelado','concluido') NOT NULL DEFAULT 'pendente',
  `observacoes` text DEFAULT NULL COMMENT 'Informações adicionais sobre o agendamento',
  `criado_por` int(10) UNSIGNED DEFAULT NULL COMMENT 'FK → usuarios (quem criou o registro)',
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Agendamentos: vínculo entre profissional e cliente';

--
-- Estrutura da tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `senha_hash` varchar(255) NOT NULL COMMENT 'Armazenar hash bcrypt, nunca texto puro',
  `perfil` enum('administrador','profissional','cliente') NOT NULL DEFAULT 'cliente',
  `especialidade` varchar(100) DEFAULT NULL COMMENT 'Ex: Dentista, Clínico Geral, Advogado',
  `registro_prof` varchar(50) DEFAULT NULL COMMENT 'Ex: CRO-SP 12345, OAB-SP 67890',
  `ativo` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1 = ativo, 0 = inativo',
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Usuários do sistema: administradores, profissionais e clientes';

--
-- Extraindo dados da tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `telefone`, `senha_hash`, `perfil`, `especialidade`, `registro_prof`, `ativo`, `criado_em`, `atualizado_em`) VALUES
(1, 'Administrador', 'admin@agendanexus.com', '(11) 9 0000-0001', '$2y$12$rQGJQ1y6MFgRq6enPA4H2uUCONN/1GgREOANPvw1G0WHTgQ87ccfK', 'administrador', NULL, NULL, 1, '2026-04-07 21:07:00', '2026-04-08 15:37:23');

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_profissional` (`profissional_id`),
  ADD KEY `idx_cliente` (`cliente_id`),
  ADD KEY `idx_data_hora` (`data_hora`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_tipo_servico` (`tipo_servico`),
  ADD KEY `fk_agend_criado_por` (`criado_por`);

--
-- Índices para tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_email` (`email`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `agendamentos`
--
ALTER TABLE `agendamentos`
  ADD CONSTRAINT `fk_agend_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_agend_criado_por` FOREIGN KEY (`criado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_agend_profissional` FOREIGN KEY (`profissional_id`) REFERENCES `usuarios` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
