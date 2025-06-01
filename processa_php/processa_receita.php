<?php
include('../includes/conexao.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'] ?? '';
    $ingredientes = $_POST['ingredientes'] ?? '';
    $custo = $_POST['custo'] ?? 0;
    $preco_venda = $_POST['preco_venda'] ?? 0;

    $stmt = $conn->prepare("INSERT INTO receitas (nome, ingredientes, custo, preco_venda) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssdd", $nome, $ingredientes, $custo, $preco_venda);

    if ($stmt->execute()) {
        header("Location: ../pages/receitas.php?sucesso=1");
    } else {
        echo "Erro ao cadastrar receita: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>
