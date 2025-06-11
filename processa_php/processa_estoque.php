<?php
session_start();
require_once('../includes/db.php');

// Segurança: Verificar se o usuário está logado e se o método é POST
if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit;
}
$id_usuario_logado = $_SESSION['user_id'];

// Obter e validar os dados do formulário
$produto_id = (int)($_POST['produto_id'] ?? 0);
$quantidade_adicionar_str = trim($_POST['quantidade_adicionar'] ?? '0');
$quantidade_adicionar = (float)str_replace(',', '.', $quantidade_adicionar_str);

// Validações
if ($produto_id === 0 || empty($quantidade_adicionar_str)) {
    header("Location: ../pages/estoque.php"); // Redireciona silenciosamente se nada for digitado
    exit;
}
if ($quantidade_adicionar <= 0) {
    header("Location: ../pages/estoque.php?erro=" . urlencode("A quantidade a adicionar deve ser maior que zero."));
    exit;
}

// Prepara e executa a query de ATUALIZAÇÃO de estoque
// Usamos "quantidade_estoque = quantidade_estoque + ?" para adicionar ao valor existente
// A cláusula "id_usuario" garante que o usuário só pode alterar seus próprios produtos
$stmt = $conn->prepare(
    "UPDATE produtos SET quantidade_estoque = quantidade_estoque + ? 
     WHERE id = ? AND id_usuario = ?"
);
$stmt->bind_param("dii", $quantidade_adicionar, $produto_id, $id_usuario_logado);

if ($stmt->execute()) {
    header("Location: ../pages/estoque.php?sucesso=" . urlencode("Estoque atualizado com sucesso!"));
} else {
    header("Location: ../pages/estoque.php?erro=" . urlencode("Erro ao atualizar o estoque."));
}

$stmt->close();
$conn->close();
exit;
?>