<?php
session_start();
require_once('../includes/db.php');
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) { header("Location: ../login.php"); exit; }

$id_receita = (int)$_GET['id'];
$id_usuario = $_SESSION['user_id'];

// 1. Buscar os dados principais da receita
$stmt = $conn->prepare("SELECT * FROM receitas WHERE id = ? AND id_usuario = ?");
$stmt->bind_param("ii", $id_receita, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$receita = $result->fetch_assoc();
$stmt->close();

if (!$receita) { header("Location: receitas.php?erro=Receita não encontrada."); exit; }

// 2. Buscar os ingredientes atuais da receita
$ingredientes_atuais = [];
$stmt_ing = $conn->prepare("SELECT produto_id, quantidade FROM receita_ingredientes WHERE receita_id = ?");
$stmt_ing->bind_param("i", $id_receita);
$stmt_ing->execute();
$result_ing = $stmt_ing->get_result();
while($row = $result_ing->fetch_assoc()) {
    $ingredientes_atuais[] = $row;
}
$stmt_ing->close();

// 3. Buscar todos os produtos do usuário para os dropdowns
$produtos_usuario = [];
$stmt_prod = $conn->prepare("SELECT id, nome, unidade FROM produtos WHERE id_usuario = ? ORDER BY nome");
$stmt_prod->bind_param("i", $id_usuario);
$stmt_prod->execute();
$result_prod = $stmt_prod->get_result();
while ($row_prod = $result_prod->fetch_assoc()) {
    $produtos_usuario[] = $row_prod;
}
$stmt_prod->close();


include('../includes/header.php');
?>
<div class="container">
    <div class="glass-card">
        <h2 class="mt-0">Editar Receita</h2>
        <?php if (isset($_GET['erro'])): ?><div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div><?php endif; ?>

        <form action="../processa_php/processa_acao.php" method="post" class="needs-validation mt-4" novalidate>
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="tipo" value="receita">
            <input type="hidden" name="id" value="<?= $receita['id'] ?>">

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nome_receita" class="form-label">Nome da Receita</label>
                    <input type="text" name="nome_receita" class="form-control" value="<?= htmlspecialchars($receita['nome']) ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="margem_lucro" class="form-label">Margem de Lucro (%)</label>
                    <input type="text" name="margem_lucro" class="form-control" value="<?= htmlspecialchars($receita['margem_lucro']) ?>" required>
                </div>
            </div>

            <hr class="my-4" style="border-color: var(--border-color);">
            <h5>Ingredientes</h5>
            <div id="ingredientes-container">
                <?php foreach ($ingredientes_atuais as $ing): ?>
                <div class="row g-2 align-items-center mb-2 ingrediente-item">
                    <div class="col-md-5">
                        <select name="ingredientes[produto_id][]" class="form-select produto-select" required>
                            <option value="" disabled>Selecione</option>
                            <?php foreach ($produtos_usuario as $p_usr): ?>
                                <option value="<?= $p_usr['id'] ?>" data-unidade="<?= htmlspecialchars($p_usr['unidade']) ?>" <?= ($p_usr['id'] == $ing['produto_id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p_usr['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <input type="text" name="ingredientes[quantidade][]" class="form-control quantidade-input" placeholder="Quantidade" value="<?= htmlspecialchars($ing['quantidade']) ?>" required>
                    </div>
                    <div class="col-md-2">
                        <input type="text" class="form-control unidade-display" placeholder="Unidade" readonly>
                    </div>
                    <div class="col-md-2">
                        <button type="button" class="btn btn-danger btn-sm w-100 btn-remove-ingredient">Remover</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <button type="button" id="add-ingredient" class="btn btn-secondary mt-2">Adicionar Ingrediente</button>
            
            <div class="col-12 mt-4">
                <button class="btn btn-primary" type="submit">Salvar Alterações</button>
                <a href="receitas.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
// Script para adicionar/remover ingredientes dinamicamente
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('ingredientes-container');
    const produtoOptionsHTML = `
        <option value="" disabled selected>Selecione o Produto</option>
        <?php foreach ($produtos_usuario as $p_usr): ?>
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
    
    // Atualiza a unidade para os itens que já existem na página
    container.querySelectorAll('.produto-select').forEach(select => {
        updateUnidade(select);
        select.addEventListener('change', function() { updateUnidade(this); });
    });

    document.getElementById('add-ingredient').addEventListener('click', function() {
        const newRow = document.createElement('div');
        newRow.className = 'row g-2 align-items-center mb-2 ingrediente-item';
        newRow.innerHTML = `
            <div class="col-md-5"><select name="ingredientes[produto_id][]" class="form-select produto-select" required>${produtoOptionsHTML}</select></div>
            <div class="col-md-3"><input type="text" name="ingredientes[quantidade][]" class="form-control quantidade-input" placeholder="Quantidade" required></div>
            <div class="col-md-2"><input type="text" class="form-control unidade-display" placeholder="Unidade" readonly></div>
            <div class="col-md-2"><button type="button" class="btn btn-danger btn-sm w-100 btn-remove-ingredient">Remover</button></div>
        `;
        container.appendChild(newRow);
        newRow.querySelector('.produto-select').addEventListener('change', function() { updateUnidade(this); });
    });

    container.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('btn-remove-ingredient')) {
            e.target.closest('.ingrediente-item').remove();
        }
    });
});
</script>

<?php include('../includes/footer.php'); ?>