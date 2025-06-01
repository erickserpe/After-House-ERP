<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require_once "../includes/db.php";

// Adicionar produto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome'])) {
    $nome = $_POST['nome'];
    $fornecedor_id = $_POST['fornecedor_id'];
    $tipo = $_POST['tipo'];
    $unidade = $_POST['unidade'];
    $preco_compra = $_POST['preco_compra'];

    $stmt = $pdo->prepare("INSERT INTO produtos (nome, fornecedor_id, tipo, unidade, preco_compra) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$nome, $fornecedor_id, $tipo, $unidade, $preco_compra]);

    header("Location: produtos.php");
    exit;
}

// Buscar fornecedores para dropdown
$stmtF = $pdo->query("SELECT id, nome FROM fornecedores ORDER BY nome");
$fornecedores = $stmtF->fetchAll();

// Buscar produtos
$stmtP = $pdo->query("SELECT p.*, f.nome as fornecedor_nome FROM produtos p LEFT JOIN fornecedores f ON p.fornecedor_id = f.id ORDER BY p.id DESC");
$produtos = $stmtP->fetchAll();

include "../includes/header.php";
?>

<div class="container mt-4">
    <h2>Produtos</h2>

    <form method="post" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="nome" class="form-control" placeholder="Nome do Produto" required>
        </div>
        <div class="col-md-3">
            <select name="fornecedor_id" class="form-select" required>
                <option value="" disabled selected>Fornecedor</option>
                <?php foreach($fornecedores as $f): ?>
                    <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-2">
            <input type="text" name="tipo" class="form-control" placeholder="Tipo (ex: bebida, fruta)" required>
        </div>
        <div class="col-md-1">
            <input type="text" name="unidade" class="form-control" placeholder="Unidade" required>
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" min="0" name="preco_compra" class="form-control" placeholder="Preço de Compra" required>
        </div>
        <div class="col-md-12 col-lg-12 col-xl-12 col-xxl-12 d-grid">
            <button class="btn btn-primary" type="submit">Adicionar Produto</button>
        </div>
    </form>

    <table class="table table-bordered table-striped">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Fornecedor</th>
                <th>Tipo</th>
                <th>Unidade</th>
                <th>Preço de Compra</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($produtos as $p): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['nome']) ?></td>
                <td><?= htmlspecialchars($p['fornecedor_nome'] ?? 'Sem fornecedor') ?></td>
                <td><?= htmlspecialchars($p['tipo']) ?></td>
                <td><?= htmlspecialchars($p['unidade']) ?></td>
                <td>R$ <?= number_format($p['preco_compra'], 2, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include "../includes/footer.php"; ?>
