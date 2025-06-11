<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) { header("Location: ../login.php"); exit; }
$id_usuario_logado = $_SESSION['user_id'];
$id_evento = (int)$_GET['id'];
require_once "../includes/db.php";

// Busca dados do evento
$stmt = $conn->prepare("SELECT * FROM eventos WHERE id = ? AND id_usuario = ?");
$stmt->bind_param("ii", $id_evento, $id_usuario_logado);
$stmt->execute();
$evento = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$evento) { header("Location: eventos.php?erro=Evento não encontrado."); exit; }

// Busca produtos salvos para este evento
$produtos_evento = [];
$sql_prods = "SELECT p.nome, p.unidade, ep.quantidade_total, ep.custo_total_item
              FROM evento_produtos ep
              JOIN produtos p ON ep.produto_id = p.id
              WHERE ep.evento_id = ?";
$stmt_prods = $conn->prepare($sql_prods);
$stmt_prods->bind_param("i", $id_evento);
$stmt_prods->execute();
$result_prods = $stmt_prods->get_result();
while($row = $result_prods->fetch_assoc()) { $produtos_evento[] = $row; }
$stmt_prods->close();
$conn->close();

include "../includes/header.php";
?>
<div class="container">
    <div class="glass-card">
        <div class="d-flex justify-content-between align-items-center">
            <h2 class="mt-0"><?= htmlspecialchars($evento['nome']) ?></h2>
            <a href="eventos.php" class="btn btn-secondary">Voltar para a Lista</a>
        </div>
        <hr style="border-color: var(--border-color);">
        <p><strong>Data:</strong> <?= $evento['data_evento'] ? date('d/m/Y', strtotime($evento['data_evento'])) : 'Não informada' ?></p>
        <p><strong>Local:</strong> <?= htmlspecialchars($evento['local_evento'] ?: 'Não informado') ?></p>
        <p><strong>Nº de Pessoas:</strong> <?= $evento['num_pessoas'] ?></p>
        <p><strong>Status:</strong> <span class="badge bg-info text-dark"><?= htmlspecialchars($evento['status']) ?></span></p>

        <h4 class="mt-4">Insumos Planejados</h4>
        <div class="table-responsive">
            </div>
        
        <hr style="border-color: var(--border-color);">

        <div class="text-center mt-4">
            <?php switch($evento['status']):
                
                case 'Planejamento': ?>
                    <a href="../processa_php/processa_acao.php?acao=mudar_status&status=Confirmado&id=<?= $evento['id'] ?>" class="btn btn-success btn-lg">
                        Confirmar Evento
                    </a>
                    <p class="text-secondary mt-2 small">Ao confirmar, você poderá dar baixa no estoque.</p>
                <?php break; ?>

                <?php case 'Confirmado': ?>
                    <a href="../processa_php/processa_acao.php?acao=realizar_evento&id=<?= $evento['id'] ?>" class="btn btn-primary btn-lg" onclick="return confirm('Tem certeza? Esta ação dará baixa nos itens do estoque e marcará o evento como Realizado. Esta ação não pode ser desfeita.')">
                        Dar Baixa no Estoque e Finalizar Evento
                    </a>
                    <p class="text-secondary mt-2 small">Esta ação é irreversível.</p>
                <?php break; ?>
                
                <?php case 'Realizado': ?>
                    <p class="text-success fw-bold fs-5"><i class="bi bi-check-circle-fill"></i> Evento Concluído</p>
                    <p class="text-secondary">O estoque para este evento já foi atualizado.</p>
                <?php break; ?>

            <?php endswitch; ?>
        </div>
    </div>
</div>
<?php include "../includes/footer.php"; ?>