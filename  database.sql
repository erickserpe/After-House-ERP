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
    id_usuario INT, /* Adicionada coluna para o ID do usuário */
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_fornecedor_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Produtos
CREATE TABLE produtos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    fornecedor_id INT,
    tipo VARCHAR(100),
    unidade VARCHAR(50),
    preco_compra DECIMAL(10,2) NOT NULL DEFAULT 0,
    id_usuario INT, /* Adicionada coluna para o ID do usuário */
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id) ON DELETE SET NULL, /* Mantém esta FK, mas agora o fornecedor também terá um id_usuario */
    CONSTRAINT fk_produto_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Receitas
CREATE TABLE receitas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    margem_lucro DECIMAL(5,2) NOT NULL DEFAULT 0, -- percentual
    id_usuario INT, /* Adicionada coluna para o ID do usuário */
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_receita_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Ingredientes das receitas (relaciona produtos e receitas)
-- Esta tabela não precisa de id_usuario direto, pois já está ligada à receita que tem o id_usuario.
-- E os produtos usados também serão filtrados pelo id_usuario na lógica da aplicação.
CREATE TABLE receita_ingredientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    receita_id INT NOT NULL,
    produto_id INT NOT NULL,
    quantidade DECIMAL(10,3) NOT NULL DEFAULT 0,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (receita_id) REFERENCES receitas(id) ON DELETE CASCADE,
    FOREIGN KEY (produto_id) REFERENCES produtos(id) ON DELETE CASCADE
);

-- Eventos / Simulador de consumo (Se for ter dados de eventos específicos por usuário, adicione id_usuario aqui também)
CREATE TABLE eventos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    data_evento DATE,
    num_pessoas INT NOT NULL,
    id_usuario INT, -- Adicionar se eventos são por usuário
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_evento_usuario FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE SET NULL -- Exemplo, pode ser CASCADE
);

-- Exemplo de usuário admin (NÃO adicione id_usuario aqui, pois esta é a tabela de usuários)
INSERT INTO usuarios (nome, email, senha) VALUES (
    'Admin After House',
    'admin@afterhouse.com',
    -- senha 'admin123' hash gerado pelo password_hash PHP com BCRYPT
    '$2y$10$EixZaYVK1fsbw1ZfbX3OXePaWxn96p36hLhkU2e8YK0l/vxJcXO.G'
);


-- (Opcional, mas recomendado) Criar índices para as novas colunas id_usuario
CREATE INDEX idx_fornecedor_usuario ON fornecedores(id_usuario);
CREATE INDEX idx_produto_usuario ON produtos(id_usuario);
CREATE INDEX idx_receita_usuario ON receitas(id_usuario);
-- CREATE INDEX idx_evento_usuario ON eventos(id_usuario); -- Se adicionou id_usuario em eventos