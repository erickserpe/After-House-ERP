<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require_once "../includes/db.php";

$produtos = [];
$stmtP = $pdo->query("SELECT id, nome, unidade, preco_compra FROM produtos ORDER BY nome");
$produtos = $stmtP->fetchAll();

$resultado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantidades = $_POST['quantidades'] ?? [];
    $total_custo = 0;

    foreach ($quantidades as $produto_id => $quantidade) {
        if ($quantidade > 0) {
            // Buscar preço do produto
            foreach ($produtos as $p) {
                if ($p['id'] == $produto_id) {
                    $total_custo += $p['preco_compra'] * $quantidade;
                    break;
                }
            }
        }
    }

    $resultado = $total_custo;
}

include "../includes/header.php";
?>

<div class="container mt-4">
    <h2>Simulador de Custo de Ingredientes</h2>

    <form method="post">
        <table class="table table-bordered">
            <thead class="table-primary">
                <tr>
                    <th>Produto</th>
                    <th>Unidade</th>
                    <th>Preço Compra (R$)</th>
                    <th>Quantidade</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($produtos as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['nome']) ?></td>
                    <td><?= htmlspecialchars($p['unidade']) ?></td>
                    <td><?= number_format($p['preco_compra'], 2, ',', '.') ?></td>
                    <td>
                        <input type="number" step="0.01" min="0" name="quantidades[<?= $p['id'] ?>]" class="form-control" value="0">
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <button class="btn btn-success" type="submit">Calcular Custo Total</button>
    </form>

    <?php if ($resultado !== null): ?>
        <div class="alert alert-info mt-4">
            <strong>Custo total dos ingredientes:</strong> R$ <?= number_format($resultado, 2, ',', '.') ?>
        </div>
    <?php endif; ?>
</div>

<?php include "../includes/footer.php"; ?>
