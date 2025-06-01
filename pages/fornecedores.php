<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require_once "../includes/db.php";

// Adicionar fornecedor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome'])) {
    $nome = $_POST['nome'];
    $tipo = $_POST['tipo'];
    $contato = $_POST['contato'];
    $produto_principal = $_POST['produto_principal'];
    $avaliacao = $_POST['avaliacao'];
    $lead_time = $_POST['lead_time'];
    $melhor_preco = $_POST['melhor_preco'];

    $stmt = $pdo->prepare("INSERT INTO fornecedores (nome, tipo, contato, produto_principal, avaliacao, lead_time, melhor_preco) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nome, $tipo, $contato, $produto_principal, $avaliacao, $lead_time, $melhor_preco]);

    header("Location: fornecedores.php");
    exit;
}

// Buscar fornecedores
$stmt = $pdo->query("SELECT * FROM fornecedores ORDER BY id DESC");
$fornecedores = $stmt->fetchAll();

include "../includes/header.php";
?>

<div class="container mt-4">
    <h2>Fornecedores</h2>

    <form method="post" class="row g-3 mb-4">
        <div class="col-md-4">
            <input type="text" name="nome" class="form-control" placeholder="Nome" required>
        </div>
        <div class="col-md-2">
            <select name="tipo" class="form-select" required>
                <option value="" disabled selected>Tipo</option>
                <option value="Bebidas">Bebidas</option>
                <option value="Frutas">Frutas</option>
                <option value="Desc.">Desc.</option>
            </select>
        </div>
        <div class="col-md-3">
            <input type="text" name="contato" class="form-control" placeholder="Contato (telefone/email)" required>
        </div>
        <div class="col-md-3">
            <input type="text" name="produto_principal" class="form-control" placeholder="Produto Principal" required>
        </div>
        <div class="col-md-1">
            <select name="avaliacao" class="form-select" required>
                <option value="1">★</option>
                <option value="2">★★</option>
                <option value="3" selected>★★★</option>
                <option value="4">★★★★</option>
                <option value="5">★★★★★</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="number" min="0" name="lead_time" class="form-control" placeholder="Lead Time (dias)" required>
        </div>
        <div class="col-md-2">
            <input type="number" step="0.01" min="0" name="melhor_preco" class="form-control" placeholder="Melhor Preço" required>
        </div>
        <div class="col-md-2">
            <button class="btn btn-success w-100" type="submit">Adicionar</button>
        </div>
    </form>

    <table class="table table-bordered table-striped">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Tipo</th>
                <th>Contato</th>
                <th>Produto Principal</th>
                <th>Avaliação</th>
                <th>Lead Time (dias)</th>
                <th>Melhor Preço</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($fornecedores as $f): ?>
            <tr>
                <td><?= $f['id'] ?></td>
                <td><?= htmlspecialchars($f['nome']) ?></td>
                <td><?= htmlspecialchars($f['tipo']) ?></td>
                <td><?= htmlspecialchars($f['contato']) ?></td>
                <td><?= htmlspecialchars($f['produto_principal']) ?></td>
                <td><?= str_repeat("★", $f['avaliacao']) ?></td>
                <td><?= $f['lead_time'] ?></td>
                <td>R$ <?= number_format($f['melhor_preco'], 2, ',', '.') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include "../includes/footer.php"; ?>
