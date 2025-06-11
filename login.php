<?php
session_start();
require_once "includes/db.php";
$error = "";
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ... lógica de login mantida ...
}
include "includes/header.php";
?>

<div class="container glass-card" style="max-width:450px;">
    <h2 class="text-center">Login</h2>
    <p class="text-center text-secondary mb-4">Acesse sua conta para continuar</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['cadastro']) && $_GET['cadastro'] == '1'): ?>
        <div class="alert alert-success">Cadastro realizado com sucesso! Faça o login.</div>
    <?php endif; ?>

    <form method="post" action="login.php" class="needs-validation" novalidate>
        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" class="form-control" id="email" name="email" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Senha</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="d-grid">
            <button class="btn btn-primary" type="submit">Entrar</button>
        </div>
    </form>
    <p class="mt-4 text-center text-secondary">Não tem uma conta? <a href="cadastro.php" class="fw-bold" style="color: var(--primary-color);">Cadastre-se</a></p>
</div>

<?php include "includes/footer.php"; ?>