<?php
// processa_php/processa_usuario.php
session_start(); // Iniciar sessão para mensagens de feedback, se desejar
require_once('../includes/db.php'); // Corrigido o caminho e nome do arquivo

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha_plain = $_POST['senha'] ?? ''; // Senha em texto plano

    // Validações básicas
    if (empty($nome) || empty($email) || empty($senha_plain)) {
        header("Location: ../cadastro.php?erro=" . urlencode("Todos os campos são obrigatórios."));
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../cadastro.php?erro=" . urlencode("Formato de e-mail inválido."));
        exit;
    }
    if (strlen($senha_plain) < 6) { // Exemplo de requisito de tamanho mínimo
        header("Location: ../cadastro.php?erro=" . urlencode("A senha deve ter pelo menos 6 caracteres."));
        exit;
    }

    // Verificar se o e-mail já existe
    $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $stmt_check->store_result();
    if ($stmt_check->num_rows > 0) {
        $stmt_check->close();
        header("Location: ../cadastro.php?erro=" . urlencode("Este e-mail já está cadastrado."));
        exit;
    }
    $stmt_check->close();

    // Hash da senha
    $senha_hashed = password_hash($senha_plain, PASSWORD_DEFAULT);
    if ($senha_hashed === false) {
        // Logar erro em produção
        header("Location: ../cadastro.php?erro=" . urlencode("Erro ao processar a senha."));
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
    if ($stmt === false) {
        // Logar erro em produção
        header("Location: ../cadastro.php?erro=" . urlencode("Erro no sistema. Tente #1"));
        exit;
    }
    $stmt->bind_param("sss", $nome, $email, $senha_hashed);

    if ($stmt->execute()) {
        // Sucesso - redirecionar para login ou uma página de sucesso
        header("Location: ../cadastro.php?sucesso=1"); // Ou para login.php?cadastro=1
    } else {
        // Erro ao inserir (logar $stmt->error em produção)
        header("Location: ../cadastro.php?erro=" . urlencode("Erro ao cadastrar usuário. Tente #2"));
    }
    $stmt->close();
    $conn->close();
} else {
    // Redirecionar se não for POST
    header("Location: ../cadastro.php");
    exit;
}
?>