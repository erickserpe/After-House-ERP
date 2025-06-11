<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['simulacao_resultado'])) {
    header("Location: simulador.php");
    exit;
}

$resultado = $_SESSION['simulacao_resultado'];
unset($_SESSION['simulacao_resultado']);

include "../includes/header.php";
?>

<div class="container">
    
    <div class="glass-card text-center">
        <h2>Resultado da Simulação</h2>
        <p class="lead text-secondary">
            Evento: <strong><?= htmlspecialchars($resultado['nome_evento']) ?></strong> para 
            <strong><?= $resultado['num_pessoas'] ?></strong> pessoas.
        </p>
    </div>

    <div class="glass-card">
        <h3 class="mt-0">Resumo Financeiro</h3>
        <div class="row text-center mt-4">
            <div class="col-md-4 mb-3">
                <div class="p-3" style="background: rgba(220, 53, 69, 0.2); border-radius: 1rem;">
                    <h5 class="text-secondary">CUSTO TOTAL</h5>
                    <h4 class="fw-bold" style="color: #ff8a80;">R$ <?= number_format($resultado['custo_total_evento'], 2, ',', '.') ?></h4>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="p-3" style="background: rgba(255, 193, 7, 0.2); border-radius: 1rem;">
                    <h5 class="text-secondary">FATURAMENTO SUGERIDO</h5>
                    <h4 class="fw-bold" style="color: #ffd180;">R$ <?= number_format($resultado['venda_total_sugerida'], 2, ',', '.') ?></h4>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="p-3" style="background: rgba(25, 135, 84, 0.2); border-radius: 1rem;">
                    <h5 class="text-secondary">LUCRO ESTIMADO</h5>
                    <h4 class="fw-bold" style="color: #b9f6ca;">R$ <?= number_format($resultado['lucro_estimado'], 2, ',', '.') ?></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="glass-card">
        <h3 class="mt-0 mb-3">Lista de Compras Necessária</h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th>Quantidade Total</th>
                        <th>Unidade</th>
                        <th>Custo Estimado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($resultado['lista_compras']) > 0): ?>
                        <?php foreach($resultado['lista_compras'] as $item): ?>
                            <tr>
                                <td><?= htmlspecialchars($item['nome_produto']) ?></td>
                                <td><?= number_format($item['quantidade_total'], 3, ',', '.') ?></td>
                                <td><?= htmlspecialchars($item['unidade']) ?></td>
                                <td>R$ <?= number_format($item['custo_total_item'], 2, ',', '.') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center">Nenhum item para calcular.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-grid gap-2 mt-3">
        <a href="simulador.php" class="btn btn-secondary btn-lg">Fazer Nova Simulação</a>
    </div>

</div>

<?php include "../includes/footer.php"; ?>