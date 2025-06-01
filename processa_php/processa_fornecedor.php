<?php
session_start(); // ESSENCIAL para acessar $_SESSION['user_id']
require_once('../includes/db.php'); //

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?erro=" . urlencode("Você precisa estar logado para cadastrar um fornecedor."));
    exit;
}
$id_usuario_logado = $_SESSION['user_id']; // Pega o ID do usuário da sessão

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome'] ?? '');
    $telefone = trim($_POST['telefone'] ?? null);
    $email = trim($_POST['email'] ?? null);
    $endereco = trim($_POST['endereco'] ?? null);

    if (empty($nome)) {
        header("Location: ../pages/fornecedores.php?erro=" . urlencode("O nome do fornecedor é obrigatório."));
        exit;
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../pages/fornecedores.php?erro=" . urlencode("Formato de e-mail inválido."));
        exit;
    }

    // Adicionamos 'id_usuario' na query e 'i' (integer) no bind_param
    $stmt = $conn->prepare("INSERT INTO fornecedores (nome, telefone, email, endereco, id_usuario) VALUES (?, ?, ?, ?, ?)");
    if ($stmt === false) {
        // Logar o erro em ambiente de produção
        // error_log("MySQLi prepare failed for fornecedores: (" . $conn->errno . ") " . $conn->error);
        header("Location: ../pages/fornecedores.php?erro=" . urlencode("Erro no sistema. Tente novamente. (DBP_F)"));
        exit;
    }
    // 's' para string, 'i' para integer. Agora são 5 placeholders.
    $stmt->bind_param("ssssi", $nome, $telefone, $email, $endereco, $id_usuario_logado);

    if ($stmt->execute()) {
        header("Location: ../pages/fornecedores.php?sucesso=1");
    } else {
        // Logar o erro em ambiente de produção
        // error_log("MySQLi execute failed for fornecedores: (" . $stmt->errno . ") " . $stmt->error);
        header("Location: ../pages/fornecedores.php?erro=" . urlencode("Erro ao cadastrar fornecedor."));
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: ../pages/fornecedores.php");
    exit;
}
?>