# After House ERP 🍹

**Sistema inteligente de gestão de open bar para eventos, com um design moderno.**

## 📋 Sobre o Projeto

O **After House** é um sistema de ERP (Enterprise Resource Planning) focado em simplificar a gestão de bares para eventos. Ele foi desenvolvido para ajudar organizadores e donos de bares a controlar insumos, calcular custos, criar receitas e simular o consumo de eventos de forma eficiente, garantindo o controle financeiro e a otimização de compras.

O projeto foi totalmente redesenhado com uma interface "Modern Glass" para proporcionar uma experiência de usuário única, agradável.

---

## ✨ Funcionalidades Principais

* **👤 Gestão de Usuários:** Sistema completo com cadastro, login, logout e alteração de senha segura.
* **📈 Dashboard Inteligente:** Painel visual com os principais indicadores (KPIs) do seu negócio, como total de produtos, fornecedores e receitas, além de um gráfico de produtos por fornecedor.
* **🚚 Gestão de Fornecedores:** Cadastre e organize todos os seus fornecedores em um só lugar.
* **📦 Gestão de Produtos:** Controle total sobre os produtos (insumos), seus preços de compra, unidades e fornecedores associados.
* **🍸 Gestão de Receitas:** Crie receitas detalhadas, associando produtos como ingredientes. O sistema calcula automaticamente o custo total da receita e sugere um preço de venda com base na margem de lucro que você definir.
* **📊 Simulador de Eventos:** A ferramenta mais poderosa do sistema. Simule um evento com base no número de pessoas e nas receitas escolhidas para obter uma lista de compras detalhada e uma projeção financeira completa (custo, faturamento e lucro).
* **🎨 Design Moderno:** Interface com efeito "glassmorphism", fundo gradiente e cores neon, totalmente responsiva para dispositivos móveis.

---

## 🛠️ Tecnologias Utilizadas

Este projeto foi construído com as seguintes tecnologias:

* **Backend:** PHP
* **Banco de Dados:** MySQL
* **Frontend:**
    * HTML5
    * CSS3 (com Variáveis, Flexbox e Grid)
    * JavaScript (Vanilla JS)
    * Bootstrap 5
    * Chart.js (para os gráficos do dashboard)

---

## 🚀 Instalação e Configuração

Siga os passos abaixo para rodar o projeto em um ambiente de desenvolvimento local.

### Pré-requisitos

* Um servidor web local (XAMPP, WAMP, MAMP, ou similar) que rode **Apache**, **PHP** e **MySQL**.
* Um cliente de banco de dados (phpMyAdmin, DBeaver, etc.).

### Guia de Instalação

1.  **Configure o Banco de Dados:**
    * Abra seu cliente de banco de dados (ex: phpMyAdmin, acessível por `http://localhost/phpmyadmin`).
    * Crie um novo banco de dados chamado `after_house`.
    * Selecione o banco de dados recém-criado e vá para a aba "Importar".
    * Clique em "Escolher arquivo" e selecione o arquivo `database.sql` que está na raiz do projeto.
    * Execute a importação. Isso criará todas as tabelas e adicionará um usuário administrador padrão.

2.  **Configure a Conexão com o Banco de Dados:**
    * Abra o arquivo `includes/db.php` no seu editor de código.
    * Altere as variáveis de conexão (`$host`, `$user`, `$password`, `$dbname`) para corresponder às configurações do seu ambiente MySQL local.
        ```php
        <?php
        $host = 'localhost';
        $user = 'root'; // Geralmente 'root' em ambientes locais
        $password = ''; // Geralmente vazio ou 'root' em ambientes locais
        $dbname = 'after_house';
        
        // ... resto do código
        ```

3.  **Acesse o Sistema:**
    * Abra seu navegador e acesse `http://localhost/nome-da-pasta-do-projeto`.
    * Você pode usar as credenciais do usuário administrador para o primeiro acesso:
        * **Email:** `admin@afterhouse.com`
        * **Senha:** `admin123`

