<?php
// pages/produtos.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require_once "../includes/db.php"; // Usa $conn

// Buscar fornecedores para dropdown
$fornecedores = [];
$resultF = $conn->query("SELECT id, nome FROM fornecedores ORDER BY nome");
if ($resultF) {
    while ($row = $resultF->fetch_assoc()) {
        $fornecedores[] = $row;
    }
    $resultF->free();
}

// Buscar produtos
$produtos = [];
$sqlP = "SELECT p.id, p.nome, p.tipo, p.unidade, p.preco_compra, p.fornecedor_id, f.nome as fornecedor_nome 
         FROM produtos p 
         LEFT JOIN fornecedores f ON p.fornecedor_id = f.id 
         ORDER BY p.nome ASC";
$resultP = $conn->query($sqlP);
if ($resultP) {
    while ($row = $resultP->fetch_assoc()) {
        $produtos[] = $row;
    }
    $resultP->free();
}

include "../includes/header.php";
?>

<div class="container mt-4">
    <h2>Produtos</h2>

    <?php if (isset($_GET['sucesso'])): ?>
        <div class="alert alert-success">Produto salvo com sucesso!</div>
    <?php endif; ?>
    <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
    <?php endif; ?>

    <h3 class="mt-4 mb-3">Adicionar Novo Produto</h3>
    <form action="../processa_php/processa_produto.php" method="post" class="row g-3 mb-4 needs-validation" novalidate>
        <div class="col-md-4">
            <label for="nome" class="form-label">Nome do Produto</label>
            <input type="text" name="nome" id="nome" class="form-control" placeholder="Nome do Produto" required>
            <div class="invalid-feedback">Por favor, informe o nome do produto.</div>
        </div>
        <div class="col-md-3">
            <label for="fornecedor_id" class="form-label">Fornecedor</label>
            <select name="fornecedor_id" id="fornecedor_id" class="form-select">
                <option value="">Selecione (Opcional)</option>
                <?php foreach($fornecedores as $f): ?>
                    <option value="<?= $f['id'] ?>"><?= htmlspecialchars($f['nome']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label for="tipo" class="form-label">Tipo</label>
            <input type="text" name="tipo" id="tipo" class="form-control" placeholder="Ex: Bebida, Fruta, Insumo">
            <div class="invalid-feedback">Por favor, informe o tipo.</div>
        </div>
        <div class="col-md-2">
            <label for="unidade" class="form-label">Unidade</label>
            <input type="text" name="unidade" id="unidade" class="form-control" placeholder="Ex: Litro, Kg, Unidade" required>
            <div class="invalid-feedback">Por favor, informe a unidade.</div>
        </div>
        <div class="col-md-3">
            <label for="preco_compra" class="form-label">Preço de Compra (R$)</label>
            <input type="number" step="0.01" min="0" name="preco_compra" id="preco_compra" class="form-control" placeholder="0.00" required>
            <div class="invalid-feedback">Por favor, informe um preço válido.</div>
        </div>
        <div class="col-12 d-grid">
            <button class="btn btn-primary" type="submit">Adicionar Produto</button>
        </div>
    </form>

    <h3 class="mt-5 mb-3">Produtos Cadastrados</h3>
    <table class="table table-bordered table-striped">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Fornecedor</th>
                <th>Tipo</th>
                <th>Unidade</th>
                <th>Preço de Compra (R$)</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($produtos) > 0): ?>
                <?php foreach($produtos as $p): ?>
                <tr>
                    <td><?= $p['id'] ?></td>
                    <td><?= htmlspecialchars($p['nome']) ?></td>
                    <td><?= htmlspecialchars($p['fornecedor_nome'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($p['tipo'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($p['unidade']) ?></td>
                    <td>R$ <?= number_format($p['preco_compra'], 2, ',', '.') ?></td>
                     <td>
                        <?php /*
                        <a href="editar_produto.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                        <a href="excluir_produto.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')">Excluir</a>
                        */?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">Nenhum produto cadastrado.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include "../includes/footer.php"; ?>