<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
$id_usuario_logado = $_SESSION['user_id'];
require_once "../includes/db.php";

$receitas_disponiveis = [];
$sqlR = "SELECT id, nome FROM receitas WHERE id_usuario = ? ORDER BY nome ASC";
$stmtR = $conn->prepare($sqlR);
if($stmtR){
    $stmtR->bind_param("i", $id_usuario_logado);
    $stmtR->execute();
    $resultR = $stmtR->get_result();
    while ($rowR = $resultR->fetch_assoc()) {
        $receitas_disponiveis[] = $rowR;
    }
    $stmtR->close();
}
$conn->close();

include "../includes/header.php";
?>
<div class="container">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="glass-card">
                <h2 class="mt-0">Planejamento de Novo Evento</h2>
                <p class="text-secondary">Preencha os dados abaixo para criar um novo planejamento de evento.</p>
                <?php if (isset($_GET['erro'])): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($_GET['erro']) ?></div>
                <?php endif; ?>
                
                <form action="../processa_php/processa_simulacao.php" method="post" class="needs-validation mt-4" novalidate>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="nome_evento" class="form-label">Nome do Evento</label>
                            <input type="text" name="nome_evento" id="nome_evento" class="form-control" placeholder="Ex: Festa de Aniversário" required>
                        </div>
                        <div class="col-md-6">
                            <label for="num_pessoas" class="form-label">Número de Pessoas</label>
                            <input type="number" name="num_pessoas" id="num_pessoas" class="form-control" placeholder="Ex: 50" required min="1">
                        </div>
                        <div class="col-md-6">
                            <label for="data_evento" class="form-label">Data do Evento</label>
                            <input type="date" name="data_evento" id="data_evento" class="form-control" required>
                        </div>
                         <div class="col-md-6">
                            <label for="local_evento" class="form-label">Local (Opcional)</label>
                            <input type="text" name="local_evento" id="local_evento" class="form-control" placeholder="Ex: Chácara do Lago">
                        </div>
                    </div>

                    <hr class="my-4" style="border-color: var(--border-color);">
                    <h5>Selecione as Receitas do Evento</h5>
                    
                    <?php if (count($receitas_disponiveis) > 0): ?>
                        <div class="row row-cols-2 row-cols-md-3 g-2">
                            <?php foreach ($receitas_disponiveis as $receita): ?>
                                <div class="col">
                                    <div class="form-check"><input class="form-check-input" type="checkbox" name="receitas_selecionadas[]" value="<?= $receita['id'] ?>" id="receita_<?= $receita['id'] ?>"><label class="form-check-label" for="receita_<?= $receita['id'] ?>"><?= htmlspecialchars($receita['nome']) ?></label></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning">Você não possui nenhuma receita cadastrada. <a href="receitas.php">Cadastre uma receita</a> para continuar.</div>
                    <?php endif; ?>

                    <div class="d-grid mt-4">
                        <button class="btn btn-primary btn-lg" type="submit" <?= count($receitas_disponiveis) === 0 ? 'disabled' : '' ?>>
                            Calcular Insumos e Salvar Planejamento
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php include "../includes/footer.php"; ?>