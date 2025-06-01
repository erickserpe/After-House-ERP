<?php
// Adicionar session_start() aqui pode ser uma opção,
// mas é geralmente melhor iniciar sessões explicitamente nas páginas que as utilizam.
// if (session_status() == PHP_SESSION_NONE) {
//     session_start();
// }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>After House</title>

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet" />

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

  <link rel="stylesheet" href="/css/style.css" />
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
    <a class="navbar-brand d-flex align-items-center" href="/index.php">
      <img src="/assets/logo.jpg" alt="After House" width="40" class="me-2" /> After House
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="/pages/fornecedores.php">Fornecedores</a></li>
        <li class="nav-item"><a class="nav-link" href="/pages/produtos.php">Produtos</a></li>
        <li class="nav-item"><a class="nav-link" href="/pages/receitas.php">Receitas</a></li>
        <li class="nav-item"><a class="nav-link" href="/pages/simulador.php">Simulador</a></li>
        <?php // Links de Login/Logout/Cadastro podem ser condicionais baseados na sessão ?>
        <?php if (isset($_SESSION['user_id'])): ?>
            <li class="nav-item"><a class="nav-link" href="/mudar_senha.php">Mudar Senha</a></li> 
            <li class="nav-item"><a class="nav-link" href="/logout.php">Logout</a></li>
        <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="/login.php">Login</a></li>
            <li class="nav-item"><a class="nav-link" href="/cadastro.php">Cadastro</a></li>
        <?php endif; ?>

      </ul>
    </div>
  </nav>

  <header class="text-center py-4">
    <div class="logo">After House 🍹</div>
  </header>

  <main class="container">
    <?php // O container principal é aberto aqui, o footer o fecha ?>
    