<?php
// pages/receitas.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require_once "../includes/db.php"; // Usa $conn

// Buscar receitas com custo calculado
$receitas = [];
$sqlR = "SELECT r.id, r.nome, r.margem_lucro, 
            (SELECT SUM(p.preco_compra * ri.quantidade) 
             FROM receita_ingredientes ri 
             JOIN produtos p ON ri.produto_id = p.id 
             WHERE ri.receita_id = r.id) AS custo_total
         FROM receitas r 
         ORDER BY r.nome ASC";
$resultR = $conn->query($sqlR);
if ($resultR) {
    while ($row = $resultR->fetch_assoc()) {
        $receitas[] = $row;
    }
    $resultR->free();
}

// Buscar produtos para dropdown
$produtos = [];
$resultP = $conn->query("SELECT id, nome, unidade FROM produtos ORDER BY nome");
if ($resultP) {
    while ($row = $resultP->fetch_assoc()) {
        $produtos[] = $row;
    }
    $resultP->free();
}

include "../includes/header.php";
?>

<div class="container mt-4">
    <h2>Receitas</h2>

    <?php if (isset($_GET['sucesso'])): ?>
        <div class="alert alert-success">Receita salva com sucesso!</div>
    <?php endif; ?>
    <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
    <?php endif; ?>

    <h3 class="mt-4 mb-3">Adicionar Nova Receita</h3>
    <form action="../processa_php/processa_receita.php" method="post" class="mb-5 needs-validation" novalidate>
        <div class="row g-3">
            <div class="col-md-5">
                <label for="nome_receita" class="form-label">Nome da Receita</label>
                <input type="text" name="nome_receita" id="nome_receita" class="form-control" placeholder="Nome da Receita" required>
                <div class="invalid-feedback">Por favor, informe o nome da receita.</div>
            </div>
            <div class="col-md-3">
                <label for="margem_lucro" class="form-label">Margem de Lucro (%)</label>
                <input type="number" step="0.01" min="0" name="margem_lucro" id="margem_lucro" class="form-control" placeholder="Ex: 20" required>
                <div class="invalid-feedback">Por favor, informe a margem de lucro.</div>
            </div>
        </div>
        <hr>
        <h5>Ingredientes</h5>
        <div id="ingredientes-container">
            <div class="row g-2 align-items-center mb-2 ingrediente-item">
                <div class="col-md-5">
                    <label class="form-label visually-hidden">Produto</label>
                    <select name="ingredientes[produto_id][]" class="form-select produto-select" required>
                        <option value="" disabled selected>Selecione o Produto</option>
                        <?php foreach ($produtos as $p): ?>
                            <option value="<?= $p['id'] ?>" data-unidade="<?= htmlspecialchars($p['unidade']) ?>"><?= htmlspecialchars($p['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Selecione um produto.</div>
                </div>
                <div class="col-md-3">
                     <label class="form-label visually-hidden">Quantidade</label>
                    <input type="number" step="0.001" min="0.001" name="ingredientes[quantidade][]" class="form-control quantidade-input" placeholder="Quantidade" required>
                    <div class="invalid-feedback">Informe a quantidade.</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label visually-hidden">Unidade</label>
                    <input type="text" name="ingredientes[unidade_display][]" class="form-control unidade-display" placeholder="Unidade" readonly>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-remove-ingredient w-100">Remover</button>
                </div>
            </div>
        </div>
        <button type="button" id="add-ingredient" class="btn btn-secondary mb-3 mt-2">Adicionar Ingrediente</button>
        <br>
        <button class="btn btn-primary" type="submit">Salvar Receita</button>
    </form>

    <h3 class="mt-5 mb-3">Receitas Cadastradas</h3>
    <table class="table table-bordered table-striped">
        <thead class="table-primary">
            <tr>
                <th>Nome</th>
                <th>Custo Total Estimado (R$)</th>
                <th>Margem Lucro (%)</th>
                <th>Preço Venda Sugerido (R$)</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($receitas) > 0): ?>
                <?php foreach ($receitas as $r):
                    $custo = $r['custo_total'] ?? 0;
                    $preco_sugerido = $custo * (1 + ($r['margem_lucro'] ?? 0) / 100);
                ?>
                <tr>
                    <td><?= htmlspecialchars($r['nome']) ?></td>
                    <td><?= number_format($custo, 2, ',', '.') ?></td>
                    <td><?= number_format($r['margem_lucro'] ?? 0, 2, ',', '.') ?>%</td>
                    <td><?= number_format($preco_sugerido, 2, ',', '.') ?></td>
                    <td>
                        <?php /* <a href="ver_receita.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-info">Ver Detalhes</a> */ ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                 <tr><td colspan="5" class="text-center">Nenhuma receita cadastrada.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('ingredientes-container');
    const produtoOptionsHTML = `
        <option value="" disabled selected>Selecione o Produto</option>
        <?php foreach ($produtos as $p): ?>
            <option value="<?= $p['id'] ?>" data-unidade="<?= htmlspecialchars($p['unidade']) ?>"><?= htmlspecialchars($p['nome']) ?></option>
        <?php endforeach; ?>
    `;

    function updateUnidade(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const unidade = selectedOption.dataset.unidade || '';
        const row = selectElement.closest('.ingrediente-item');
        const unidadeDisplay = row.querySelector('.unidade-display');
        if (unidadeDisplay) {
            unidadeDisplay.value = unidade;
        }
    }
    
    // Atualiza unidade para a primeira linha (se houver)
    const firstSelect = container.querySelector('.produto-select');
    if (firstSelect) {
        firstSelect.addEventListener('change', function() {
            updateUnidade(this);
        });
        // Inicializa caso um valor já esteja selecionado (ex: ao voltar na página)
        if(firstSelect.value) updateUnidade(firstSelect);

        const firstRemoveButton = firstSelect.closest('.ingrediente-item').querySelector('.btn-remove-ingredient');
        if (container.children.length <= 1) { // Se só tem um item, desabilita remover
             if(firstRemoveButton) firstRemoveButton.disabled = true;
        }
    }


    document.getElementById('add-ingredient').addEventListener('click', function() {
        const newIngredientRow = document.createElement('div');
        newIngredientRow.classList.add('row', 'g-2', 'align-items-center', 'mb-2', 'ingrediente-item');
        newIngredientRow.innerHTML = `
            <div class="col-md-5">
                <label class="form-label visually-hidden">Produto</label>
                <select name="ingredientes[produto_id][]" class="form-select produto-select" required>
                    ${produtoOptionsHTML}
                </select>
                <div class="invalid-feedback">Selecione um produto.</div>
            </div>
            <div class="col-md-3">
                <label class="form-label visually-hidden">Quantidade</label>
                <input type="number" step="0.001" min="0.001" name="ingredientes[quantidade][]" class="form-control quantidade-input" placeholder="Quantidade" required>
                <div class="invalid-feedback">Informe a quantidade.</div>
            </div>
            <div class="col-md-2">
                <label class="form-label visually-hidden">Unidade</label>
                <input type="text" name="ingredientes[unidade_display][]" class="form-control unidade-display" placeholder="Unidade" readonly>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-danger btn-remove-ingredient w-100">Remover</button>
            </div>
        `;
        container.appendChild(newIngredientRow);
        
        const newSelect = newIngredientRow.querySelector('.produto-select');
        newSelect.addEventListener('change', function() {
            updateUnidade(this);
        });

        // Habilita todos os botões de remover se houver mais de um item
        const allRemoveButtons = container.querySelectorAll('.btn-remove-ingredient');
        allRemoveButtons.forEach(btn => btn.disabled = false);
    });

    container.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('btn-remove-ingredient')) {
            if (container.children.length > 1) { // Não permite remover o último item
                e.target.closest('.ingrediente-item').remove();
            }
            // Se restar apenas um item, desabilita seu botão de remover
            if (container.children.length === 1) {
                const lastRemoveButton = container.querySelector('.btn-remove-ingredient');
                if(lastRemoveButton) lastRemoveButton.disabled = true;
            }
        }
    });
});
</script>

<?php include "../includes/footer.php"; ?>