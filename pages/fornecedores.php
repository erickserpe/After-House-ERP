<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
$id_usuario_logado = $_SESSION['user_id'];
require_once "../includes/db.php";

$fornecedores = [];
$sql = "SELECT id, nome, telefone, email, endereco FROM fornecedores WHERE id_usuario = ? ORDER BY nome ASC";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("i", $id_usuario_logado);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $fornecedores[] = $row;
        }
    }
    $stmt->close();
}
include "../includes/header.php";
?>
<div class="container">
    <h2>Meus Fornecedores</h2>
    
    <?php if (isset($_GET['sucesso'])): ?>
        <div class="alert alert-success" role="alert"><?= htmlspecialchars($_GET['sucesso']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($_GET['erro']) ?></div>
    <?php endif; ?>

    <form action="../processa_php/processa_fornecedor.php" method="post" class="row g-3 needs-validation glass-card" novalidate>
        <h3 class="mt-0 mb-3">Adicionar Novo Fornecedor</h3>
        <div class="col-md-6"><label for="nome" class="form-label">Nome</label><input type="text" name="nome" id="nome" class="form-control" required></div>
        <div class="col-md-6"><label for="email" class="form-label">E-mail</label><input type="email" name="email" id="email" class="form-control"></div>
        <div class="col-md-6"><label for="telefone" class="form-label">Telefone</label><input type="text" name="telefone" id="telefone" class="form-control"></div>
        <div class="col-md-6"><label for="endereco" class="form-label">Endereço</label><input type="text" name="endereco" id="endereco" class="form-control"></div>
        <div class="col-12"><button class="btn btn-primary" type="submit">Adicionar Fornecedor</button></div>
    </form>

    <div class="glass-card">
        <h3 class="mt-0 mb-3">Fornecedores Cadastrados</h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th><th>Nome</th><th>Telefone</th><th>E-mail</th><th>Endereço</th><th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($fornecedores as $f): ?>
                    <tr>
                        <td><?= $f['id'] ?></td>
                        <td><?= htmlspecialchars($f['nome']) ?></td>
                        <td><?= htmlspecialchars($f['telefone'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($f['email'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($f['endereco'] ?? 'N/A') ?></td>
                        <td>
                            <a href="editar_fornecedor.php?id=<?= $f['id'] ?>" class="action-icon" title="Editar"><i class="bi bi-pencil-fill"></i></a>
                            
                            <a href="../processa_php/processa_acao.php?acao=excluir&tipo=fornecedor&id=<?= $f['id'] ?>" class="action-icon" title="Excluir" onclick="return confirm('Tem certeza?')"><i class="bi bi-trash-fill"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include "../includes/footer.php"; ?>