<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
$id_usuario_logado = $_SESSION['user_id'];
require_once "../includes/db.php";

// Buscar produtos do usuário com estoque
$produtos = [];
$sql = "SELECT id, nome, unidade, quantidade_estoque FROM produtos WHERE id_usuario = ? ORDER BY nome ASC";
$stmt = $conn->prepare($sql);
if($stmt){
    $stmt->bind_param("i", $id_usuario_logado);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $produtos[] = $row;
        }
    }
    $stmt->close();
}

include "../includes/header.php";
?>

<div class="container">
    <h2>Controle de Estoque</h2>

    <?php if (isset($_GET['sucesso'])): ?>
        <div class="alert alert-success" role="alert"><?= htmlspecialchars($_GET['sucesso']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($_GET['erro']) ?></div>
    <?php endif; ?>

    <div class="glass-card">
        <h3 class="mt-0 mb-3">Estoque Atual dos Produtos</h3>
        <p class="text-secondary">Use o campo "Adicionar" para registrar a entrada de novas compras no estoque.</p>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th class="text-center">Estoque Atual</th>
                        <th class="text-center">Unidade</th>
                        <th style="width: 250px;">Adicionar ao Estoque</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($produtos) > 0): ?>
                        <?php foreach($produtos as $p): ?>
                        <tr>
                            <td><?= htmlspecialchars($p['nome']) ?></td>
                            <td class="text-center fw-bold <?= ($p['quantidade_estoque'] <= 0) ? 'text-danger' : 'text-white' ?>">
                                <?= number_format($p['quantidade_estoque'], 3, ',', '.') ?>
                            </td>
                            <td class="text-center"><?= htmlspecialchars($p['unidade']) ?></td>
                            <td>
                                <form action="../processa_php/processa_acao.php" method="post" class="d-flex form-estoque">
                                    <input type="hidden" name="acao" value="adicionar_estoque">
                                    <input type="hidden" name="produto_id" value="<?= $p['id'] ?>">
                                    <input type="text" name="quantidade_adicionar" class="form-control form-control-sm me-2" placeholder="Ex: 12.5">
                                    <button type="submit" class="btn btn-primary btn-sm">OK</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">Nenhum produto cadastrado. <a href="produtos.php">Cadastre um produto primeiro.</a></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>