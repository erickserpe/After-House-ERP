<?php include 'includes/header.php'; ?>
<div class="container glass-card" style="max-width: 500px;">
  <h2 class="text-center">Cadastrar Novo Usuário</h2>
  <p class="text-center text-secondary mb-4">Crie sua conta gratuitamente</p>
  
  <?php if (isset($_GET['erro'])): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
  <?php endif; ?>
  <?php if (isset($_GET['sucesso'])): ?>
    <div class="alert alert-success">Cadastro realizado com sucesso! Você já pode fazer o <a href="login.php" class="fw-bold">login</a>.</div>
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
    <div class="d-grid">
        <button type="submit" class="btn btn-primary">Cadastrar</button>
    </div>
  </form>
</div>
<?php include 'includes/footer.php'; ?>