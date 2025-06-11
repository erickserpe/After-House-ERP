<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?erro=" . urlencode("Você precisa estar logado para mudar sua senha."));
    exit;
}
include('includes/header.php');
?>

<div class="container glass-card" style="max-width: 500px;">
    <h2 class="text-center">Mudar Senha</h2>
    <p class="text-center text-secondary mb-4">Mantenha sua conta segura</p>

    <?php if (isset($_GET['sucesso'])): ?>
        <div class="alert alert-success"><?= htmlspecialchars($_GET['sucesso']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
    <?php endif; ?>

    <form action="processa_php/processa_mudar_senha.php" method="POST" class="needs-validation" novalidate>
        <div class="mb-3">
            <label for="senha_atual" class="form-label">Senha Atual</label>
            <input type="password" class="form-control" id="senha_atual" name="senha_atual" required>
        </div>
        <div class="mb-3">
            <label for="nova_senha" class="form-label">Nova Senha</label>
            <input type="password" class="form-control" id="nova_senha" name="nova_senha" required minlength="6">
        </div>
        <div class="mb-3">
            <label for="confirma_nova_senha" class="form-label">Confirme a Nova Senha</label>
            <input type="password" class="form-control" id="confirma_nova_senha" name="confirma_nova_senha" required>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Mudar Senha</button>
        </div>
    </form>
</div>

<?php include('includes/footer.php'); ?>