<?php
// login.php - Versão Final e Limpa
session_start(); 

require_once "includes/db.php";

$error = "";

// Se o usuário já está logado, redireciona para a página inicial
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Verifica se o formulário foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email'], $_POST['password'])) {
        $email = trim($_POST['email']);
        $password_input = $_POST['password'];

        if (empty($email) || empty($password_input)) {
            $error = "Por favor, preencha todos os campos.";
        } else {
            // Preparar a consulta SQL para buscar o usuário pelo email
            $stmt = $conn->prepare("SELECT id, nome, email, senha FROM usuarios WHERE email = ?");
            if ($stmt === false) {
                // Em ambiente de produção, logar o erro em vez de exibi-lo
                $error = "Erro no sistema. Por favor, tente novamente mais tarde.";
            } else {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();

                // Verifica se o usuário foi encontrado e se a senha está correta
                if ($user && password_verify($password_input, $user['senha'])) {
                    // SUCESSO! Login bem-sucedido.
                    // Cria a sessão para o usuário
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['nome'];

                    // Redireciona para a página principal do sistema
                    header("Location: index.php");
                    exit;
                } else {
                    // Usuário não encontrado ou senha incorreta
                    $error = "Email ou senha inválidos.";
                }
                $stmt->close();
            }
        }
    } else {
        $error = "Por favor, preencha todos os campos.";
    }
}

include "includes/header.php";
?>

<div class="container glass-card" style="max-width:450px;">
    <h2 class="text-center">Login</h2>
    <p class="text-center text-secondary mb-4">Acesse sua conta para continuar</p>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['cadastro']) && $_GET['cadastro'] == '1'): ?>
        <div class="alert alert-success" role="alert">
            Cadastro realizado com sucesso! Faça o login para continuar.
        </div>
    <?php endif; ?>

    <form method="post" action="login.php" class="needs-validation" novalidate>
        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
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