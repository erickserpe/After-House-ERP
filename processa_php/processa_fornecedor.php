<?php
// processa_php/processa_fornecedor.php
session_start();
require_once('../includes/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome'] ?? '');
    $telefone = trim($_POST['telefone'] ?? null); // Pode ser nulo
    $email = trim($_POST['email'] ?? null);    // Pode ser nulo
    $endereco = trim($_POST['endereco'] ?? null); // Pode ser nulo

    if (empty($nome)) {
        header("Location: ../pages/fornecedores.php?erro=" . urlencode("O nome do fornecedor é obrigatório."));
        exit;
    }
    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../pages/fornecedores.php?erro=" . urlencode("Formato de e-mail inválido."));
        exit;
    }

    // Os campos telefone, email, endereco podem ser NULL no banco de dados conforme o schema original
    // Se fossem NOT NULL, precisaria de validação de empty() aqui também.

    $stmt = $conn->prepare("INSERT INTO fornecedores (nome, telefone, email, endereco) VALUES (?, ?, ?, ?)");
    if ($stmt === false) {
        header("Location: ../pages/fornecedores.php?erro=" . urlencode("Erro ao preparar query: " . $conn->error));
        exit;
    }
    // 's' para string. Se algum campo pudesse ser realmente NULL e não string vazia, precisaria de lógica adicional
    $stmt->bind_param("ssss", $nome, $telefone, $email, $endereco);

    if ($stmt->execute()) {
        header("Location: ../pages/fornecedores.php?sucesso=1");
    } else {
        header("Location: ../pages/fornecedores.php?erro=" . urlencode("Erro ao cadastrar fornecedor: " . $stmt->error));
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: ../pages/fornecedores.php");
    exit;
}
?>