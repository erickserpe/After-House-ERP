<?php
// pages/fornecedores.php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}

require_once "../includes/db.php"; // Usa $conn

// Buscar fornecedores
$fornecedores = [];
$result = $conn->query("SELECT id, nome, telefone, email, endereco FROM fornecedores ORDER BY nome ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $fornecedores[] = $row;
    }
    $result->free();
} else {
    // Tratar erro de consulta, se necessário
    // echo "Erro ao buscar fornecedores: " . $conn->error;
}

include "../includes/header.php";
?>

<div class="container mt-4">
    <h2>Fornecedores</h2>

    <?php if (isset($_GET['sucesso'])): ?>
        <div class="alert alert-success">Fornecedor salvo com sucesso!</div>
    <?php endif; ?>
    <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
    <?php endif; ?>

    <h3 class="mt-4 mb-3">Adicionar Novo Fornecedor</h3>
    <form action="../processa_php/processa_fornecedor.php" method="post" class="row g-3 mb-4 needs-validation" novalidate>
        <div class="col-md-6">
            <label for="nome" class="form-label">Nome do Fornecedor</label>
            <input type="text" name="nome" id="nome" class="form-control" placeholder="Nome do Fornecedor" required>
            <div class="invalid-feedback">Por favor, informe o nome.</div>
        </div>
        <div class="col-md-6">
            <label for="email" class="form-label">E-mail</label>
            <input type="email" name="email" id="email" class="form-control" placeholder="email@exemplo.com">
             <div class="invalid-feedback">Por favor, informe um e-mail válido.</div>
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
            <button class="btn btn-success" type="submit">Adicionar Fornecedor</button>
        </div>
    </form>

    <h3 class="mt-5 mb-3">Fornecedores Cadastrados</h3>
    <table class="table table-bordered table-striped">
        <thead class="table-primary">
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Telefone</th>
                <th>E-mail</th>
                <th>Endereço</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($fornecedores) > 0): ?>
                <?php foreach($fornecedores as $f): ?>
                <tr>
                    <td><?= $f['id'] ?></td>
                    <td><?= htmlspecialchars($f['nome']) ?></td>
                    <td><?= htmlspecialchars($f['telefone'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($f['email'] ?? 'N/A') ?></td>
                    <td><?= htmlspecialchars($f['endereco'] ?? 'N/A') ?></td>
                    <td>
                        <?php /* Exemplo de botões de ação
                        <a href="editar_fornecedor.php?id=<?= $f['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                        <a href="excluir_fornecedor.php?id=<?= $f['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Tem certeza?')">Excluir</a>
                        */ ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center">Nenhum fornecedor cadastrado.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include "../includes/footer.php"; ?>