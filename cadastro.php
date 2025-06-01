<?php
// cadastro.php
// Se precisar de sessão, inicie-a.
// session_start();

// Se o usuário já estiver logado, redirecione para a página inicial, por exemplo.
// if (isset($_SESSION['user_id'])) {
//     header("Location: index.php");
//     exit;
// }

include 'includes/header.php'; // Se cadastro.php está na raiz
?>
<div class="container">
  <h2 class="mb-4">Cadastrar Novo Usuário</h2>
  <?php if (isset($_GET['erro'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
  <?php endif; ?>
  <?php if (isset($_GET['sucesso'])): ?>
    <div class="alert alert-success">Cadastro realizado com sucesso! Você já pode fazer o <a href="login.php">login</a>.</div>
  <?php endif; ?>

  <form action="processa_php/processa_usuario.php" method="POST">
    <div class="mb-3">
      <label for="nome" class="form-label">Nome completo</label>
      <input type="text" class="form-control" id="nome" name="nome" required>
    </div>
    <div class="mb-3">
      <label for="email" class="form-label">E-mail</label>
      <input type="email" class="form-control" id="email" name="email" required>
    </div>
    <div class="mb-3">
      <label for="senha" class="form-label">Senha</label>
      <input type="password" class="form-control" id="senha" name="senha" required minlength="6">
    </div>
    <button type="submit" class="btn btn-primary">Cadastrar</button>
  </form>
</div>
<?php include 'includes/footer.php'; // Se cadastro.php está na raiz ?>