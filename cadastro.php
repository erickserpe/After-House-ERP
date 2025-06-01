<?php include 'includes/header.php'; ?>
<div class="container">
  <h2 class="mb-4">Cadastrar Novo Usuário</h2>
  <form action="processa_cadastro.php" method="POST">
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
      <input type="password" class="form-control" id="senha" name="senha" required>
    </div>
    <button type="submit" class="btn btn-primary">Cadastrar</button>
  </form>
</div>
<?php include 'includes/footer.php'; ?>
