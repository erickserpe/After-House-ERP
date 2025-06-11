-- After House ERP - Schema Final e Completo para Distribuição
-- Versão: 1.2
-- Este script cria a estrutura completa do banco de dados, incluindo todas as
-- funcionalidades de gestão, estoque e planejamento de eventos.

-- Configurações iniciais para garantir compatibilidade e evitar erros.
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- Apaga as tabelas antigas se elas existirem para permitir uma instalação limpa.
DROP TABLE IF EXISTS `evento_produtos`;
DROP TABLE IF EXISTS `receita_ingredientes`;
DROP TABLE IF EXISTS `eventos`;
DROP TABLE IF EXISTS `receitas`;
DROP TABLE IF EXISTS `produtos`;
DROP TABLE IF EXISTS `fornecedores`;
DROP TABLE IF EXISTS `usuarios`;


-- Estrutura da tabela `usuarios`
-- Armazena os dados de login dos usuários do sistema.
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Estrutura da tabela `fornecedores`
-- Armazena os fornecedores de cada usuário.
CREATE TABLE `fornecedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefone` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_fornecedor_usuario` (`id_usuario`),
  CONSTRAINT `fk_fornecedor_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Estrutura da tabela `produtos`
-- Armazena os produtos (insumos) de cada usuário, incluindo o estoque.
CREATE TABLE `produtos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `fornecedor_id` int(11) DEFAULT NULL,
  `tipo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unidade` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `preco_compra` decimal(10,2) NOT NULL DEFAULT 0.00,
  `quantidade_estoque` decimal(10,3) NOT NULL DEFAULT 0.000,
  `id_usuario` int(11) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_produto_usuario` (`id_usuario`),
  CONSTRAINT `fk_produto_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`fornecedor_id`) REFERENCES `fornecedores`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Estrutura da tabela `receitas`
-- Armazena as receitas (drinks) criadas por cada usuário.
CREATE TABLE `receitas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `margem_lucro` decimal(5,2) NOT NULL DEFAULT 0.00,
  `id_usuario` int(11) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_receita_usuario` (`id_usuario`),
  CONSTRAINT `fk_receita_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Estrutura da tabela `receita_ingredientes`
-- Tabela de ligação entre receitas e produtos (ingredientes).
CREATE TABLE `receita_ingredientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `receita_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` decimal(10,3) NOT NULL DEFAULT 0.000,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`receita_id`) REFERENCES `receitas` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Estrutura da tabela `eventos`
-- Armazena os eventos planejados por cada usuário.
CREATE TABLE `eventos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `data_evento` date DEFAULT NULL,
  `local_evento` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `num_pessoas` int(11) NOT NULL,
  `observacoes` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Planejamento',
  `id_usuario` int(11) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_evento_usuario` (`id_usuario`),
  CONSTRAINT `fk_evento_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Estrutura da tabela `evento_produtos`
-- Guarda a "foto" da lista de compras de um evento no momento em que ele foi salvo.
CREATE TABLE `evento_produtos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `evento_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade_total` decimal(10,3) NOT NULL,
  `custo_total_item` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`evento_id`) REFERENCES `eventos` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Inserção de dados iniciais (usuário administrador)
INSERT INTO `usuarios` (`nome`, `email`, `senha`) VALUES
('Admin After House', 'admin@afterhouse.com', '$2y$10$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36hLhkU2e8YK0l/vxJcXO.G');