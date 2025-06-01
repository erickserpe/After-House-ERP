<?php
// pages/simulador.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require_once "../includes/db.php"; // Usa $conn

$produtos = [];
$resultP = $conn->query("SELECT id, nome, unidade, preco_compra FROM produtos ORDER BY nome");
if ($resultP) {
    while ($row = $resultP->fetch_assoc()) {
        $produtos[] = $row;
    }
    $resultP->free();
}

$resultado_custo_total = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantidades_selecionadas = $_POST['quantidades'] ?? [];
    $total_custo = 0;

    foreach ($quantidades_selecionadas as $produto_id => $quantidade_str) {
        $quantidade = (float)str_replace(',', '.', $quantidade_str);
        if ($quantidade > 0) {
            // Buscar preço do produto
            // É mais eficiente buscar todos os produtos uma vez e iterar sobre o array $produtos
            // do que fazer uma query para cada produto dentro do loop.
            foreach ($produtos as $p) {
                if ($p['id'] == $produto_id) {
                    $total_custo += $p['preco_compra'] * $quantidade;
                    break; 
                }
            }
        }
    }
    $resultado_custo_total = $total_custo;
}

include "../includes/header.php";
?>

<div class="container mt-4">
    <h2>Simulador de Custo de Ingredientes</h2>
    <p>Selecione as quantidades desejadas dos produtos para calcular o custo total dos ingredientes.</p>

    <form method="post" class="needs-validation" novalidate>
        <table class="table table-bordered table-hover">
            <thead class="table-primary">
                <tr>
                    <th>Produto</th>
                    <th>Unidade Medida</th>
                    <th>Preço Compra Unit. (R$)</th>
                    <th>Quantidade Desejada</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($produtos) > 0): ?>
                    <?php foreach ($produtos as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['nome']) ?></td>
                        <td><?= htmlspecialchars($p['unidade']) ?></td>
                        <td>R$ <?= number_format($p['preco_compra'], 2, ',', '.') ?></td>
                        <td>
                            <input type="number" step="0.001" min="0" name="quantidades[<?= $p['id'] ?>]" class="form-control" 
                                   value="<?= isset($_POST['quantidades'][$p['id']]) ? htmlspecialchars($_POST['quantidades'][$p['id']]) : '0' ?>">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center">Nenhum produto cadastrado para simulação.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php if (count($produtos) > 0): ?>
            <button class="btn btn-success" type="submit">Calcular Custo Total dos Ingredientes</button>
        <?php endif; ?>
    </form>

    <?php if ($resultado_custo_total !== null): ?>
        <div class="alert alert-info mt-4">
            <h4>Resultado da Simulação:</h4>
            <strong>Custo total dos ingredientes selecionados:</strong> R$ <?= number_format($resultado_custo_total, 2, ',', '.') ?>
        </div>
    <?php endif; ?>
</div>

<?php include "../includes/footer.php"; ?>