<?php
session_start();
require_once('../includes/db.php');
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) { header("Location: ../login.php"); exit; }

$id = (int)$_GET['id'];
$id_usuario = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM fornecedores WHERE id = ? AND id_usuario = ?");
$stmt->bind_param("ii", $id, $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$item = $result->fetch_assoc();
$stmt->close();

if (!$item) { header("Location: fornecedores.php?erro=Fornecedor não encontrado."); exit; }

include('../includes/header.php');
?>
<div class="container">
    <div class="glass-card">
        <h2 class="mt-0">Editar Fornecedor</h2>
        <?php if (isset($_GET['erro'])): ?><div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div><?php endif; ?>
        
        <form action="../processa_php/processa_acao.php" method="post" class="row g-3 mt-3">
            
            <input type="hidden" name="acao" value="editar">
            <input type="hidden" name="tipo" value="fornecedor">
            <input type="hidden" name="id" value="<?= $item['id'] ?>">
            
            <div class="col-md-6"><label for="nome" class="form-label">Nome</label><input type="text" name="nome" id="nome" class="form-control" value="<?= htmlspecialchars($item['nome']) ?>" required></div>
            <div class="col-md-6"><label for="email" class="form-label">E-mail</label><input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($item['email']) ?>"></div>
            <div class="col-md-6"><label for="telefone" class="form-label">Telefone</label><input type="text" name="telefone" id="telefone" class="form-control" value="<?= htmlspecialchars($item['telefone']) ?>"></div>
            <div class="col-md-6"><label for="endereco" class="form-label">Endereço</label><input type="text" name="endereco" id="endereco" class="form-control" value="<?= htmlspecialchars($item['endereco']) ?>"></div>
            
            <div class="col-12 mt-4">
                <button class="btn btn-primary" type="submit">Salvar Alterações</button>
                <a href="fornecedores.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>
<?php include('../includes/footer.php'); ?>