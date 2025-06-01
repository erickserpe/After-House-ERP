<?php
session_start(); // ESSENCIAL
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
$id_usuario_logado = $_SESSION['user_id'];

require_once "../includes/db.php"; //

// Buscar fornecedores DO USUÁRIO LOGADO para dropdown
$fornecedores_usuario = [];
$stmtF = $conn->prepare("SELECT id, nome FROM fornecedores WHERE id_usuario = ? ORDER BY nome");
if($stmtF){
    $stmtF->bind_param("i", $id_usuario_logado);
    $stmtF->execute();
    $resultF = $stmtF->get_result();
    while ($rowF = $resultF->fetch_assoc()) {
        $fornecedores_usuario[] = $rowF;
    }
    $stmtF->close();
}


// Buscar produtos DO USUÁRIO LOGADO
$produtos = [];
$sqlP = "SELECT p.id, p.nome, p.tipo, p.unidade, p.preco_compra, p.fornecedor_id, f.nome as fornecedor_nome 
         FROM produtos p 
         LEFT JOIN fornecedores f ON p.fornecedor_id = f.id AND f.id_usuario = ? -- Garante que o join só pegue fornecedores do mesmo usuário
         WHERE p.id_usuario = ? -- Filtro principal para produtos do usuário
         ORDER BY p.nome ASC";
$stmtP = $conn->prepare($sqlP);

if($stmtP){
    // O primeiro 'i' é para f.id_usuario no JOIN, o segundo 'i' é para p.id_usuario no WHERE.
    $stmtP->bind_param("ii", $id_usuario_logado, $id_usuario_logado);
    $stmtP->execute();
    $resultP = $stmtP->get_result();
    if ($resultP) {
        while ($rowP = $resultP->fetch_assoc()) {
            $produtos[] = $rowP;
        }
    }
    $stmtP->close();
}

// $conn->close(); // Opcional

include "../includes/header.php";
?>

<div class="container mt-4">
    <h2>Meus Produtos</h2>

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
            <label for="fornecedor_id" class="form-label">Fornecedor (Opcional)</label>
            <select name="fornecedor_id" id="fornecedor_id" class="form-select">
                <option value="">Selecione um dos seus fornecedores</option>
                <?php foreach($fornecedores_usuario as $f_usr): ?>
                    <option value="<?= $f_usr['id'] ?>"><?= htmlspecialchars($f_usr['nome']) ?></option>
                <?php endforeach; ?>
            </select>
             <small class="form-text text-muted">Apenas seus fornecedores cadastrados aparecerão aqui.</small>
        </div>
        <div class="col-md-3">
            <label for="tipo" class="form-label">Tipo</label>
            <input type="text" name="tipo" id="tipo" class="form-control" placeholder="Ex: Bebida, Fruta, Insumo">
        </div>
        <div class="col-md-2">
            <label for="unidade" class="form-label">Unidade</label>
            <input type="text" name="unidade" id="unidade" class="form-control" placeholder="Ex: Litro, Kg, Un" required>
             <div class="invalid-feedback">Por favor, informe a unidade.</div>
        </div>
        <div class="col-md-3">
            <label for="preco_compra" class="form-label">Preço de Compra (R$)</label>
            <input type="text" name="preco_compra" id="preco_compra" class="form-control" placeholder="0.00" required pattern="^\d*([,.]\d{1,2})?$">
            <div class="invalid-feedback">Por favor, informe um preço válido (ex: 10,50).</div>
        </div>
        <div class="col-12 d-grid">
            <button class="btn btn-primary" type="submit">Adicionar Produto</button>
        </div>
    </form>

    <h3 class="mt-5 mb-3">Produtos Cadastrados</h3>
    <table class="table table-bordered table-striped">
        <thead class="table-primary">
            <tr>
                <th data-label="ID">ID</th>
                <th data-label="Nome">Nome</th>
                <th data-label="Fornecedor">Fornecedor</th>
                <th data-label="Tipo">Tipo</th>
                <th data-label="Unidade">Unidade</th>
                <th data-label="Preço Compra (R$)">Preço Compra</th>
                <th data-label="Ações">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($produtos) > 0): ?>
                <?php foreach($produtos as $p): ?>
                <tr>
                    <td data-label="ID"><?= $p['id'] ?></td>
                    <td data-label="Nome"><?= htmlspecialchars($p['nome']) ?></td>
                    <td data-label="Fornecedor"><?= htmlspecialchars($p['fornecedor_nome'] ?? 'N/A') ?></td>
                    <td data-label="Tipo"><?= htmlspecialchars($p['tipo'] ?? 'N/A') ?></td>
                    <td data-label="Unidade"><?= htmlspecialchars($p['unidade']) ?></td>
                    <td data-label="Preço Compra (R$)">R$ <?= number_format($p['preco_compra'], 2, ',', '.') ?></td>
                     <td data-label="Ações">
                        <?php /*
                        <a href="editar_produto.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                        <a href="../processa_php/excluir_produto.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')">Excluir</a>
                        */?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="7" class="text-center">Você ainda não cadastrou nenhum produto.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include "../includes/footer.php"; ?>