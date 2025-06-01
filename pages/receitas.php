<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require_once "../includes/db.php";

// Adicionar receita
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome_receita'])) {
    $nome_receita = $_POST['nome_receita'];
    $margem_lucro = $_POST['margem_lucro'];

    // Inserir nova receita
    $stmt = $pdo->prepare("INSERT INTO receitas (nome, margem_lucro) VALUES (?, ?)");
    $stmt->execute([$nome_receita, $margem_lucro]);
    $receita_id = $pdo->lastInsertId();

    // Inserir ingredientes vinculados
    if (!empty($_POST['ingredientes']) && !empty($_POST['quantidades'])) {
        foreach ($_POST['ingredientes'] as $index => $produto_id) {
            $quantidade = $_POST['quantidades'][$index];
            if ($quantidade > 0) {
                $stmtIng = $pdo->prepare("INSERT INTO receita_ingredientes (receita_id, produto_id, quantidade) VALUES (?, ?, ?)");
                $stmtIng->execute([$receita_id, $produto_id, $quantidade]);
            }
        }
    }

    header("Location: receitas.php");
    exit;
}

// Buscar receitas com custo calculado
$stmtR = $pdo->query("SELECT r.*, 
    (SELECT SUM(p.preco_compra * ri.quantidade) FROM receita_ingredientes ri JOIN produtos p ON ri.produto_id = p.id WHERE ri.receita_id = r.id) AS custo_total
    FROM receitas r ORDER BY r.id DESC");
$receitas = $stmtR->fetchAll();

// Buscar produtos para dropdown
$stmtP = $pdo->query("SELECT id, nome FROM produtos ORDER BY nome");
$produtos = $stmtP->fetchAll();

include "../includes/header.php";
?>

<div class="container mt-4">
    <h2>Receitas</h2>

    <form method="post" class="mb-5">
        <div class="row g-3">
            <div class="col-md-4">
                <input type="text" name="nome_receita" class="form-control" placeholder="Nome da Receita" required>
            </div>
            <div class="col-md-2">
                <input type="number" step="0.01" min="0" name="margem_lucro" class="form-control" placeholder="Margem de Lucro (%)" required>
            </div>
        </div>
        <hr>
        <h5>Ingredientes</h5>
        <div id="ingredientes-container">
            <div class="row g-2 align-items-center mb-2">
                <div class="col-md-6">
                    <select name="ingredientes[]" class="form-select" required>
                        <option value="" disabled selected>Selecione o Produto</option>
                        <?php foreach ($produtos as $p): ?>
                            <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <input type="number" step="0.01" min="0" name="quantidades[]" class="form-control" placeholder="Quantidade" required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-remove-ingredient">Remover</button>
                </div>
            </div>
        </div>
        <button type="button" id="add-ingredient" class="btn btn-secondary mb-3">Adicionar Ingrediente</button>
        <br>
        <button class="btn btn-primary" type="submit">Salvar Receita</button>
    </form>

    <h3>Receitas Cadastradas</h3>
    <table class="table table-bordered table-striped">
        <thead class="table-primary">
            <tr>
                <th>Nome</th>
                <th>Custo Total (R$)</th>
                <th>Preço Sugerido (R$)</th>
                <th>Margem Lucro (%)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($receitas as $r): 
                $custo = $r['custo_total'] ?? 0;
                $preco_sugerido = $custo * (1 + $r['margem_lucro'] / 100);
            ?>
            <tr>
                <td><?= htmlspecialchars($r['nome']) ?></td>
                <td><?= number_format($custo, 2, ',', '.') ?></td>
                <td><?= number_format($preco_sugerido, 2, ',', '.') ?></td>
                <td><?= number_format($r['margem_lucro'], 2) ?>%</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
document.getElementById('add-ingredient').addEventListener('click', function(){
    const container = document.getElementById('ingredientes-container');
    const newIngredient = document.createElement('div');
    newIngredient.classList.add('row', 'g-2', 'align-items-center', 'mb-2');
    newIngredient.innerHTML = `
        <div class="col-md-6">
            <select name="ingredientes[]" class="form-select" required>
                <option value="" disabled selected>Selecione o Produto</option>
                <?php foreach ($produtos as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4">
            <input type="number" step="0.01" min="0" name="quantidades[]" class="form-control" placeholder="Quantidade" required>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-danger btn-remove-ingredient">Remover</button>
        </div>
    `;
    container.appendChild(newIngredient);
});

document.addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('btn-remove-ingredient')) {
        e.target.closest('.row').remove();
    }
});
</script>

<?php include "../includes/footer.php"; ?>
