<?php
// Esta verificação garante que a sessão só seja iniciada se ainda não estiver ativa.
// Isso resolve o aviso e é a forma mais segura de gerenciar sessões.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>After House</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  
  <link rel="stylesheet" href="/css/style.css" />

</head>
<body>
  <div class="site-container"> 
    <nav class="navbar navbar-expand-lg navbar-dark glass-card mx-auto mb-4" style="max-width: 95%; width: 1200px;">
      <div class="container-fluid">
        <a class="navbar-brand d-flex align-items-center" href="/index.php">
          <img src="/assets/logo.jpg" alt="After House" width="40" class="me-2" /> After House 
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="/pages/dashboard.php">Dashboard</a></li> 
            <li class="nav-item"><a class="nav-link" href="/pages/eventos.php">Eventos</a></li>
            <li class="nav-item"><a class="nav-link" href="/pages/estoque.php">Estoque</a></li>
            
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" href="#" id="navbarCadastrosDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Cadastros
                </a>
                <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarCadastrosDropdown">
                    <li><a class="dropdown-item" href="/pages/produtos.php">Produtos</a></li>
                    <li><a class="dropdown-item" href="/pages/receitas.php">Receitas</a></li>
                    <li><a class="dropdown-item" href="/pages/fornecedores.php">Fornecedores</a></li>
                </ul>
            </li>

            <?php if (isset($_SESSION['user_id'])): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarUserDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($_SESSION['user_name'] ?? 'Usuário') ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark" aria-labelledby="navbarUserDropdown">
                        <li><a class="dropdown-item" href="/mudar_senha.php">Mudar Senha</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/logout.php">Logout</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li class="nav-item"><a class="nav-link" href="/login.php">Login</a></li>
                <li class="nav-item"><a class="nav-link" href="/cadastro.php">Cadastro</a></li>
            <?php endif; ?>
          </ul>
        </div>
      </div>
    </nav>

    <header class="text-center py-4">
      <div class="logo">After House 🍹</div>
    </header>

    <main class="container">