<?php
include('../includes/conexao.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'] ?? '';
    $quantidade = $_POST['quantidade'] ?? 0;
    $preco = $_POST['preco'] ?? 0;
    $fornecedor_id = $_POST['fornecedor_id'] ?? 0;

    $stmt = $conn->prepare("INSERT INTO produtos (nome, quantidade, preco, fornecedor_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sddi", $nome, $quantidade, $preco, $fornecedor_id);

    if ($stmt->execute()) {
        header("Location: ../pages/produtos.php?sucesso=1");
    } else {
        echo "Erro ao cadastrar produto: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>
