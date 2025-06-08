<?php
session_start(); // ESSENCIAL
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
$id_usuario_logado = $_SESSION['user_id'];

require_once "../includes/db.php";

// Buscar TODAS as receitas do usuário logado para a seleção no formulário
$receitas_disponiveis = [];
$sqlR = "SELECT id, nome FROM receitas WHERE id_usuario = ? ORDER BY nome ASC";
$stmtR = $conn->prepare($sqlR);

if($stmtR){
    $stmtR->bind_param("i", $id_usuario_logado);
    $stmtR->execute();
    $resultR = $stmtR->get_result();
    if ($resultR) {
        while ($rowR = $resultR->fetch_assoc()) {
            $receitas_disponiveis[] = $rowR;
        }
    }
    $stmtR->close();
}
$conn->close();

// Incluir o cabeçalho da página
include "../includes/header.php";
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            
            <h2>Simulador de Eventos</h2>
            <p class="lead">Preencha os dados abaixo para calcular os insumos e custos para o seu evento.</p>

            <?php if (isset($_SESSION['simulacao_erro'])): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($_SESSION['simulacao_erro']) ?></div>
                <?php unset($_SESSION['simulacao_erro']); // Limpa a mensagem após exibir ?>
            <?php endif; ?>

            <div class="card shadow-sm mt-4">
                <div class="card-body">
                    <h5 class="card-title mb-4">Novo Evento</h5>
                    
                    <form action="../processa_php/processa_simulacao.php" method="post" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="nome_evento" class="form-label">Nome do Evento</label>
                            <input type="text" name="nome_evento" id="nome_evento" class="form-control" placeholder="Ex: Festa de Aniversário" required>
                            <div class="invalid-feedback">Por favor, dê um nome para o evento.</div>
                        </div>

                        <div class="mb-3">
                            <label for="num_pessoas" class="form-label">Número de Pessoas</label>
                            <input type="number" name="num_pessoas" id="num_pessoas" class="form-control" placeholder="Ex: 50" required min="1">
                             <div class="invalid-feedback">Informe um número de pessoas válido.</div>
                        </div>

                        <hr class="my-4">

                        <h6>Selecione as Receitas do Evento</h6>
                        <p class="text-muted small">Marque todos os drinks que serão servidos.</p>

                        <?php if (count($receitas_disponiveis) > 0): ?>
                            <div class="row row-cols-2 row-cols-md-3 g-2">
                                <?php foreach ($receitas_disponiveis as $receita): ?>
                                    <div class="col">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="receitas_selecionadas[]" value="<?= $receita['id'] ?>" id="receita_<?= $receita['id'] ?>">
                                            <label class="form-check-label" for="receita_<?= $receita['id'] ?>">
                                                <?= htmlspecialchars($receita['nome']) ?>
                                            </label>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning">
                                Você não possui nenhuma receita cadastrada. <a href="receitas.php">Cadastre sua primeira receita</a> para usar o simulador.
                            </div>
                        <?php endif; ?>

                        <div class="d-grid mt-4">
                            <button class="btn btn-primary btn-lg" type="submit" <?= count($receitas_disponiveis) === 0 ? 'disabled' : '' ?>>
                                📊 Gerar Simulação
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>