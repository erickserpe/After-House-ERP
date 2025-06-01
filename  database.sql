-- After House - Database schema

CREATE DATABASE IF NOT EXISTS after_house CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE after_house;

-- Usuários para login
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Fornecedor
CREATE TABLE fornecedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    telefone VARCHAR(30),
    email VARCHAR(100),
    endereco VARCHAR(255),
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Produtos
CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    fornecedor_id INT,
    tipo VARCHAR(100),
    unidade VARCHAR(50),
    preco_compra DECIMAL(10,2) NOT NULL DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id) ON DELETE SET NULL
);

-- Receitas
CREATE TABLE receitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    margem_lucro DECIMAL(5,2) NOT NULL DEFAULT 0, -- percentual
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Ingredientes das receitas (relaciona produtos e receitas)
CREATE TABLE receita_ingredientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receita_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade DECIMAL(10,3) NOT NULL DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (receita_id) REFERENCES receitas(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);

-- Eventos / Simulador de consumo
CREATE TABLE eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    data_evento DATE,
    num_pessoas INT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Exemplo de usuário admin
INSERT INTO usuarios (nome, email, senha) VALUES (
    'Admin After House',
    'admin@afterhouse.com',
    -- senha 'admin123' hash gerado pelo password_hash PHP com BCRYPT
    '$2y$10$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36hLhkU2e8YK0l/vxJcXO.G'
);

