<?php
session_start(); // ESSENCIAL
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
$id_usuario_logado = $_SESSION['user_id'];

require_once "../includes/db.php"; //

// Buscar receitas DO USUÁRIO LOGADO com custo calculado
$receitas = [];
$sqlR = "SELECT r.id, r.nome, r.margem_lucro, 
            (SELECT SUM(p.preco_compra * ri.quantidade) 
             FROM receita_ingredientes ri 
             JOIN produtos p ON ri.produto_id = p.id 
             WHERE ri.receita_id = r.id 
               AND p.id_usuario = ? -- Garante que os produtos usados no cálculo são do usuário
            ) AS custo_total
         FROM receitas r 
         WHERE r.id_usuario = ? -- Filtro principal para receitas do usuário
         ORDER BY r.nome ASC";
$stmtR = $conn->prepare($sqlR);
if($stmtR){
    // O primeiro 'i' é para p.id_usuario no subselect, o segundo 'i' é para r.id_usuario no WHERE principal.
    $stmtR->bind_param("ii", $id_usuario_logado, $id_usuario_logado);
    $stmtR->execute();
    $resultR = $stmtR->get_result();
    if ($resultR) {
        while ($rowR = $resultR->fetch_assoc()) {
            $receitas[] = $rowR;
        }
    }
    $stmtR->close();
}


// Buscar produtos DO USUÁRIO LOGADO para dropdown de ingredientes
$produtos_usuario = [];
$stmtP_usr = $conn->prepare("SELECT id, nome, unidade FROM produtos WHERE id_usuario = ? ORDER BY nome");
if($stmtP_usr){
    $stmtP_usr->bind_param("i", $id_usuario_logado);
    $stmtP_usr->execute();
    $resultP_usr = $stmtP_usr->get_result();
    while ($rowP_usr = $resultP_usr->fetch_assoc()) {
        $produtos_usuario[] = $rowP_usr;
    }
    $stmtP_usr->close();
}

// $conn->close(); // Opcional

include "../includes/header.php";
?>

<div class="container mt-4">
    <h2>Minhas Receitas</h2>

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
                <input type="text" name="margem_lucro" id="margem_lucro" class="form-control" placeholder="Ex: 20.00" required pattern="^\d*([,.]\d{1,2})?$">
                <div class="invalid-feedback">Informe a margem de lucro (ex: 20,00).</div>
            </div>
        </div>
        <hr>
        <h5>Ingredientes <small class="text-muted">(Seus produtos cadastrados)</small></h5>
        <div id="ingredientes-container">
            <div class="row g-2 align-items-center mb-2 ingrediente-item">
                <div class="col-md-5">
                    <label class="form-label visually-hidden">Produto</label>
                    <select name="ingredientes[produto_id][]" class="form-select produto-select" required>
                        <option value="" disabled selected>Selecione o Produto</option>
                        <?php foreach ($produtos_usuario as $p_usr): ?>
                            <option value="<?= $p_usr['id'] ?>" data-unidade="<?= htmlspecialchars($p_usr['unidade']) ?>"><?= htmlspecialchars($p_usr['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="invalid-feedback">Selecione um produto.</div>
                </div>
                <div class="col-md-3">
                     <label class="form-label visually-hidden">Quantidade</label>
                    <input type="text" name="ingredientes[quantidade][]" class="form-control quantidade-input" placeholder="Quantidade" required pattern="^\d*([,.]\d{1,3})?$">
                    <div class="invalid-feedback">Informe a quantidade (ex: 0,500).</div>
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
                <th data-label="Nome">Nome</th>
                <th data-label="Custo (R$)">Custo Total Estimado (R$)</th>
                <th data-label="Margem (%)">Margem Lucro (%)</th>
                <th data-label="Preço Venda (R$)">Preço Venda Sugerido (R$)</th>
                <th data-label="Ações">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($receitas) > 0): ?>
                <?php foreach ($receitas as $r):
                    $custo = $r['custo_total'] ?? 0;
                    $preco_sugerido = $custo * (1 + ($r['margem_lucro'] ?? 0) / 100);
                ?>
                <tr>
                    <td data-label="Nome"><?= htmlspecialchars($r['nome']) ?></td>
                    <td data-label="Custo (R$)"><?= number_format($custo, 2, ',', '.') ?></td>
                    <td data-label="Margem (%)"><?= number_format($r['margem_lucro'] ?? 0, 2, ',', '.') ?>%</td>
                    <td data-label="Preço Venda (R$)"><?= number_format($preco_sugerido, 2, ',', '.') ?></td>
                    <td data-label="Ações">
                        <?php /* <a href="ver_receita.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-info">Ver Detalhes</a> */ ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                 <tr><td colspan="5" class="text-center">Você ainda não cadastrou nenhuma receita.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('ingredientes-container');
    const produtoOptionsHTML = `
        <option value="" disabled selected>Selecione o Produto</option>
        <?php foreach ($produtos_usuario as $p_usr): // Usar a lista de produtos do usuário ?>
            <option value="<?= $p_usr['id'] ?>" data-unidade="<?= htmlspecialchars($p_usr['unidade']) ?>"><?= htmlspecialchars($p_usr['nome']) ?></option>
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
    
    const firstSelect = container.querySelector('.produto-select');
    if (firstSelect) {
        firstSelect.addEventListener('change', function() { updateUnidade(this); });
        if(firstSelect.value) updateUnidade(firstSelect);

        const firstRemoveButton = firstSelect.closest('.ingrediente-item').querySelector('.btn-remove-ingredient');
        if (container.children.length <= 1 && firstRemoveButton) {
            firstRemoveButton.disabled = true;
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
                <input type="text" name="ingredientes[quantidade][]" class="form-control quantidade-input" placeholder="Quantidade" required pattern="^\\d*([,.]\\d{1,3})?$">
                <div class="invalid-feedback">Informe a quantidade (ex: 0,500).</div>
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
        newSelect.addEventListener('change', function() { updateUnidade(this); });

        const allRemoveButtons = container.querySelectorAll('.btn-remove-ingredient');
        allRemoveButtons.forEach(btn => btn.disabled = false);
    });

    container.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('btn-remove-ingredient')) {
            if (container.children.length > 1) { 
                e.target.closest('.ingrediente-item').remove();
            }
            if (container.children.length === 1) {
                const lastRemoveButton = container.querySelector('.btn-remove-ingredient');
                if(lastRemoveButton) lastRemoveButton.disabled = true;
            }
        }
    });
});
</script>

<?php include "../includes/footer.php"; ?>