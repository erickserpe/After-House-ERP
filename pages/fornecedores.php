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
        <div class="alert alert-success">Fornecedor salvo com sucesso!</div>
    <?php endif; ?>
    <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
    <?php endif; ?>

    <form action="../processa_php/processa_fornecedor.php" method="post" class="row g-3 needs-validation glass-card" novalidate>
        <h3 class="mt-0 mb-3">Adicionar Novo Fornecedor</h3>
        <div class="col-md-6">
            <label for="nome" class="form-label">Nome do Fornecedor</label>
            <input type="text" name="nome" id="nome" class="form-control" placeholder="Nome do Fornecedor" required>
            <div class="invalid-feedback">Por favor, informe o nome.</div>
        </div>
        <div class="col-md-6">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="email@exemplo.com">
             <div class="invalid-feedback">Por favor, informe um e-mail válido (opcional).</div>
        </div>
        <div class="col-md-6">
            <label for="telefone" class="form-label">Telefone</label>
            <input type="text" name="telefone" id="telefone" class="form-control" placeholder="(XX) XXXXX-XXXX">
        </div>
        <div class="col-md-6">
            <label for="endereco" class="form-label">Endereço</label>
            <input type="text" name="endereco" id="endereco" class="form-control" placeholder="Rua, Número, Bairro, Cidade">
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit">Adicionar Fornecedor</button>
        </div>
    </form>

    <div class="glass-card">
        <h3 class="mt-0 mb-3">Fornecedores Cadastrados</h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th data-label="ID">ID</th>
                        <th data-label="Nome">Nome</th>
                        <th data-label="Telefone">Telefone</th>
                        <th data-label="E-mail">E-mail</th>
                        <th data-label="Endereço">Endereço</th>
                        <th data-label="Ações">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($fornecedores) > 0): ?>
                        <?php foreach($fornecedores as $f): ?>
                        <tr>
                            <td data-label="ID"><?= $f['id'] ?></td>
                            <td data-label="Nome"><?= htmlspecialchars($f['nome']) ?></td>
                            <td data-label="Telefone"><?= htmlspecialchars($f['telefone'] ?? 'N/A') ?></td>
                            <td data-label="E-mail"><?= htmlspecialchars($f['email'] ?? 'N/A') ?></td>
                            <td data-label="Endereço"><?= htmlspecialchars($f['endereco'] ?? 'N/A') ?></td>
                            <td data-label="Ações" class="actions-cell">
                                <a href="editar_fornecedor.php?id=<?= $f['id'] ?>" class="action-icon" title="Editar">
                                    <i class="bi bi-pencil-fill"></i>
                                </a>
                                <a href="../processa_php/excluir_fornecedor.php?id=<?= $f['id'] ?>" class="action-icon action-danger" title="Excluir" onclick="return confirm('Tem certeza?')">
                                    <i class="bi bi-trash-fill"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">Você ainda não cadastrou nenhum fornecedor.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>