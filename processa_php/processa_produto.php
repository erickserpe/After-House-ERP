<?php
// processa_php/processa_produto.php
session_start();
require_once('../includes/db.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome'] ?? '');
    // fornecedor_id é opcional no formulário, mas a FK no DB pode ser SET NULL.
    // Se o formulário envia string vazia para fornecedor_id, converter para NULL.
    $fornecedor_id = trim($_POST['fornecedor_id'] ?? '');
    $fornecedor_id = empty($fornecedor_id) ? null : (int)$fornecedor_id;

    $tipo = trim($_POST['tipo'] ?? null);
    $unidade = trim($_POST['unidade'] ?? '');
    $preco_compra_str = trim($_POST['preco_compra'] ?? '0');
    $preco_compra = (float)str_replace(',', '.', $preco_compra_str); // Converter vírgula para ponto se necessário

    if (empty($nome) || empty($unidade) || !is_numeric($preco_compra_str) || $preco_compra < 0) {
        header("Location: ../pages/produtos.php?erro=" . urlencode("Dados inválidos para o produto. Verifique nome, unidade e preço."));
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO produtos (nome, fornecedor_id, tipo, unidade, preco_compra) VALUES (?, ?, ?, ?, ?)");
    if ($stmt === false) {
        header("Location: ../pages/produtos.php?erro=" . urlencode("Erro ao preparar query: " . $conn->error));
        exit;
    }
    // Tipos: s = string, i = integer, d = double, b = blob
    $stmt->bind_param("sisid", $nome, $fornecedor_id, $tipo, $unidade, $preco_compra);

    if ($stmt->execute()) {
        header("Location: ../pages/produtos.php?sucesso=1");
    } else {
        header("Location: ../pages/produtos.php?erro=" . urlencode("Erro ao cadastrar produto: " . $stmt->error));
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: ../pages/produtos.php");
    exit;
}
?>