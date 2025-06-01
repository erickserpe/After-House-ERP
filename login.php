<?php
// login.php
session_start(); // ESSENCIAL: Deve ser a primeira coisa no script, antes de qualquer saída HTML.

require_once "includes/db.php"; // Conexão com o banco de dados ($conn)

$error = ""; // Variável para armazenar mensagens de erro

// Se o usuário já está logado, redireciona para a página inicial
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Verifica se o formulário foi submetido
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['email'], $_POST['password'])) {
        $email = trim($_POST['email']); // Remove espaços em branco do início e fim do email
        $password_input = $_POST['password']; // Senha como digitada pelo usuário

        if (empty($email) || empty($password_input)) {
            $error = "Por favor, preencha todos os campos.";
        } else {
            // Preparar a consulta SQL para buscar o usuário pelo email
            $stmt = $conn->prepare("SELECT id, nome, email, senha FROM usuarios WHERE email = ?");
            if ($stmt === false) {
                // Em ambiente de produção, logar o erro em vez de exibi-lo diretamente
                // error_log("MySQLi prepare failed: (" . $conn->errno . ") " . $conn->error);
                $error = "Erro no sistema. Por favor, tente novamente mais tarde. (DBP)";
            } else {
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();
                $user = $result->fetch_assoc(); // Tenta buscar o usuário

                // Verifica se o usuário foi encontrado e se a senha está correta
                if ($user && password_verify($password_input, $user['senha'])) {
                    // Login bem-sucedido
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['nome']; // Armazena o nome do usuário na sessão também, se útil

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
// $conn->close(); // Geralmente não é necessário fechar aqui se o footer não fizer mais queries.
                // O PHP fecha conexões automaticamente no final do script.

// Inclui o cabeçalho da página (APÓS toda a lógica de processamento e redirecionamento)
include "includes/header.php"; //
?>

<div class="container mt-5" style="max-width:400px;">
    <h2>Login - After House</h2>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger" role="alert">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['cadastro']) && $_GET['cadastro'] == '1'): ?>
        <div class="alert alert-success" role="alert">
            Cadastro realizado com sucesso! Faça o login para continuar.
        </div>
    <?php endif; ?>

    <form method="post" action="login.php" class="needs-validation" novalidate>
        <div class="mb-3">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required autofocus>
            <div class="invalid-feedback">
                Por favor, informe seu e-mail.
            </div>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Senha</label>
            <input type="password" class="form-control" id="password" name="password" required>
            <div class="invalid-feedback">
                Por favor, informe sua senha.
            </div>
        </div>
        <button class="btn btn-primary w-100" type="submit">Entrar</button>
    </form>
    <p class="mt-3 text-center">Não tem uma conta? <a href="cadastro.php">Cadastre-se aqui</a></p>
</div>

<?php
// Inclui o rodapé da página
include "includes/footer.php"; //
?>