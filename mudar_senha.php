<?php
session_start();
// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?erro=" . urlencode("Você precisa estar logado para mudar sua senha."));
    exit;
}

include('includes/header.php'); // Se estiver na raiz
// Se estiver em pages/, use: include('../includes/header.php');
?>

<div class="container mt-5">
    <h2>Mudar Senha</h2>

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
            <div class="invalid-feedback">Por favor, informe sua senha atual.</div>
        </div>
        <div class="mb-3">
            <label for="nova_senha" class="form-label">Nova Senha</label>
            <input type="password" class="form-control" id="nova_senha" name="nova_senha" required minlength="6">
            <div class="invalid-feedback">A nova senha deve ter pelo menos 6 caracteres.</div>
        </div>
        <div class="mb-3">
            <label for="confirma_nova_senha" class="form-label">Confirme a Nova Senha</label>
            <input type="password" class="form-control" id="confirma_nova_senha" name="confirma_nova_senha" required>
            <div class="invalid-feedback">Por favor, confirme sua nova senha.</div>
        </div>
        <button type="submit" class="btn btn-primary">Mudar Senha</button>
    </form>
</div>

<?php
include('includes/footer.php'); // Se estiver na raiz
// Se estiver em pages/, use: include('../includes/footer.php');
?>