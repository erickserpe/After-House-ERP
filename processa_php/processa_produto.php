<?php
session_start(); // ESSENCIAL
require_once('../includes/db.php'); //

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?erro=" . urlencode("Você precisa estar logado para cadastrar um produto."));
    exit;
}
$id_usuario_logado = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome'] ?? '');
    $fornecedor_id_str = trim($_POST['fornecedor_id'] ?? '');
    $fornecedor_id = empty($fornecedor_id_str) ? null : (int)$fornecedor_id_str;
    $tipo = trim($_POST['tipo'] ?? null);
    $unidade = trim($_POST['unidade'] ?? '');
    $preco_compra_str = trim($_POST['preco_compra'] ?? '0');
    $preco_compra = (float)str_replace(',', '.', $preco_compra_str);

    if (empty($nome) || empty($unidade) || !is_numeric(str_replace(',', '.', $preco_compra_str)) || $preco_compra < 0) {
        header("Location: ../pages/produtos.php?erro=" . urlencode("Dados inválidos para o produto. Verifique nome, unidade e preço."));
        exit;
    }

    // Adicionamos 'id_usuario' na query e 'i' no bind_param
    $stmt = $conn->prepare("INSERT INTO produtos (nome, fornecedor_id, tipo, unidade, preco_compra, id_usuario) VALUES (?, ?, ?, ?, ?, ?)");
    if ($stmt === false) {
        // error_log("MySQLi prepare failed for produtos: (" . $conn->errno . ") " . $conn->error);
        header("Location: ../pages/produtos.php?erro=" . urlencode("Erro no sistema. Tente novamente. (DBP_P)"));
        exit;
    }
    // Tipos: s = string, i = integer, d = double. O último 'i' é para id_usuario.
    // Se fornecedor_id for NULL, o tipo 'i' ainda funciona, mas certifique-se que a coluna no DB aceita NULL.
    $stmt->bind_param("sisidi", $nome, $fornecedor_id, $tipo, $unidade, $preco_compra, $id_usuario_logado);

    if ($stmt->execute()) {
        header("Location: ../pages/produtos.php?sucesso=1");
    } else {
        // error_log("MySQLi execute failed for produtos: (" . $stmt->errno . ") " . $stmt->error);
        header("Location: ../pages/produtos.php?erro=" . urlencode("Erro ao cadastrar produto."));
    }
    $stmt->close();
    $conn->close();
} else {
    header("Location: ../pages/produtos.php");
    exit;
}
?>