<?php
include('../includes/conexao.php'); // ou ajuste o caminho

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'] ?? '';
    $contato = $_POST['contato'] ?? '';
    $email = $_POST['email'] ?? '';
    $telefone = $_POST['telefone'] ?? '';

    $stmt = $conn->prepare("INSERT INTO fornecedores (nome, contato, email, telefone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nome, $contato, $email, $telefone);

    if ($stmt->execute()) {
        header("Location: ../pages/fornecedores.php?sucesso=1");
    } else {
        echo "Erro ao cadastrar fornecedor: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>
