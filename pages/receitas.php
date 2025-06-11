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

include "../includes/header.php";
?>

<div class="container">
    <h2>Minhas Receitas</h2>

    <?php if (isset($_GET['sucesso'])): ?>
        <div class="alert alert-success" role="alert"><?= htmlspecialchars($_GET['sucesso']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($_GET['erro']) ?></div>
    <?php endif; ?>

    <form action="../processa_php/processa_receita.php" method="post" class="needs-validation glass-card" novalidate>
        <h3 class="mt-0 mb-4">Adicionar Nova Receita</h3>
        <div class="row g-3">
            <div class="col-md-5">
                <label for="nome_receita" class="form-label">Nome da Receita</label>
                <input type="text" name="nome_receita" id="nome_receita" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label for="margem_lucro" class="form-label">Margem de Lucro (%)</label>
                <input type="text" name="margem_lucro" id="margem_lucro" class="form-control" placeholder="Ex: 20.00" required>
            </div>
        </div>
        <hr class="my-4" style="border-color: var(--border-color);">
        <h5>Ingredientes</h5>
        <div id="ingredientes-container">
            <div class="row g-2 align-items-center mb-2 ingrediente-item">
                <div class="col-md-5">
                    <select name="ingredientes[produto_id][]" class="form-select produto-select" required>
                        <option value="" disabled selected>Selecione o Produto</option>
                        <?php foreach ($produtos_usuario as $p_usr): ?>
                            <option value="<?= $p_usr['id'] ?>" data-unidade="<?= htmlspecialchars($p_usr['unidade']) ?>"><?= htmlspecialchars($p_usr['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="text" name="ingredientes[quantidade][]" class="form-control quantidade-input" placeholder="Quantidade" required>
                </div>
                <div class="col-md-2">
                    <input type="text" class="form-control unidade-display" placeholder="Unidade" readonly>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-danger btn-sm w-100 btn-remove-ingredient">Remover</button>
                </div>
            </div>
        </div>
        <button type="button" id="add-ingredient" class="btn btn-secondary mt-2">Adicionar Ingrediente</button>
        <br>
        <div class="mt-4">
            <button class="btn btn-primary" type="submit">Salvar Receita</button>
        </div>
    </form>

    <div class="glass-card">
        <h3 class="mt-0 mb-3">Receitas Cadastradas</h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Custo Total (R$)</th>
                        <th>Margem Lucro (%)</th>
                        <th>Preço Venda (R$)</th>
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
                                <a href="editar_receita.php?id=<?= $r['id'] ?>" class="action-icon" title="Editar"><i class="bi bi-pencil-fill"></i></a>
                                <a href="../processa_php/processa_acao.php?acao=excluir&tipo=receita&id=<?= $r['id'] ?>" class="action-icon" title="Excluir" onclick="return confirm('Tem certeza que deseja excluir esta receita?')"><i class="bi bi-trash-fill"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                         <tr><td colspan="5" class="text-center">Você ainda não cadastrou nenhuma receita.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
// Script para adicionar/remover ingredientes dinamicamente (mantido)
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

<?php include "../includes/footer.php"; ?>