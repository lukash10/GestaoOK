-- phpMyAdmin SQL Dump
-- version 4.8.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 21-Mar-2019 às 02:56
-- Versão do servidor: 10.1.37-MariaDB
-- versão do PHP: 7.3.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `banco_teste`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `cliente`
--

CREATE TABLE `cliente` (
  `id` int(11) NOT NULL,
  `nome` varchar(25) DEFAULT NULL,
  `telefone` varchar(11) DEFAULT NULL,
  `rua` varchar(20) DEFAULT NULL,
  `bairro` varchar(20) DEFAULT NULL,
  `cep` varchar(11) DEFAULT NULL,
  `cpf` varchar(11) DEFAULT NULL,
  `cidade` varchar(20) DEFAULT NULL,
  `tipo` varchar(20) DEFAULT NULL,
  `radio` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `cliente`
--

INSERT INTO `cliente` (`id`, `nome`, `telefone`, `rua`, `bairro`, `cep`, `cpf`, `cidade`, `tipo`, `radio`) VALUES
(1, 'TEST', '(06)6', '155151', 'awa', '56454', '11112', 'Abbbbbb', 'Fornecedor', NULL);

-- --------------------------------------------------------

--
-- Estrutura da tabela `contapagar`
--

CREATE TABLE `contapagar` (
  `id` int(11) NOT NULL,
  `descricao` varchar(11) DEFAULT NULL,
  `datavencimento` date DEFAULT NULL,
  `valor` decimal(7,2) DEFAULT NULL,
  `statuspagamento` varchar(20) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `plano_id` varchar(20) DEFAULT NULL,
  `dataemissao` date DEFAULT NULL,
  `tipodespesa` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `contapagar`
--

INSERT INTO `contapagar` (`id`, `descricao`, `datavencimento`, `valor`, `statuspagamento`, `cliente_id`, `plano_id`, `dataemissao`, `tipodespesa`) VALUES
(1, 'Teste25', '2019-03-20', '-15.00', 'Pendente', 1, '2', '2019-03-20', 'Variavel'),
(2, 'Teste', '2019-04-20', '15.00', 'Pago', 1, '2', '2019-03-20', 'Fixo'),
(3, 'Teste', '2019-05-20', '-15.00', 'Pago', 1, '2', '2019-03-20', 'Variavel'),
(4, 'Teste 3', '2019-03-21', '25.00', 'Pago', 1, '2', '2019-03-20', 'Variavel'),
(5, 'Teste 3', '2019-04-21', '25.00', 'Pago', 1, '2', '2019-03-20', 'Variavel');

-- --------------------------------------------------------

--
-- Estrutura da tabela `contareceber`
--

CREATE TABLE `contareceber` (
  `id` int(11) NOT NULL,
  `descricao` varchar(50) DEFAULT NULL,
  `datavencimento` date DEFAULT NULL,
  `valor` decimal(7,2) DEFAULT NULL,
  `statuspagamento` varchar(20) DEFAULT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `plano_id` varchar(11) DEFAULT NULL,
  `dataemissao` date DEFAULT NULL,
  `tipodespesa` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estrutura da tabela `plano`
--

CREATE TABLE `plano` (
  `id` int(11) NOT NULL,
  `tipo` varchar(20) DEFAULT NULL,
  `categoria` varchar(20) DEFAULT NULL,
  `tipodespesa` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Extraindo dados da tabela `plano`
--

INSERT INTO `plano` (`id`, `tipo`, `categoria`, `tipodespesa`) VALUES
(1, 'Receita', 'Teste 1', ''),
(2, 'Despesa', 'Teste 2', ''),
(3, 'Receita', 'Aluguel', ''),
(4, 'Receita', 'Aluguel 2', 'Variavel'),
(5, 'Despesa', 'Aluguel', 'Fixo');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
