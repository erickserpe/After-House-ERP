<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['simulacao_resultado'])) {
    header("Location: simulador.php");
    exit;
}

$resultado = $_SESSION['simulacao_resultado'];

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
        <h3 class="mt-0">Resumo Financeiro (Estimativa)</h3>
        <div class="row text-center mt-4">
            <div class="col-md-4 mb-3">
                <div class="p-3" style="background: rgba(220, 53, 69, 0.2); border-radius: 1rem;">
                    <h5 class="text-secondary">CUSTO DE COMPRA</h5>
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
                <div class="p-3" style="background: rgba(0, 255, 195, 0.2); border-radius: 1rem;">
                    <h5 class="text-secondary">LUCRO ESTIMADO</h5>
                    <h4 class="fw-bold" style="color: var(--primary-color);">R$ <?= number_format($resultado['lucro_estimado'], 2, ',', '.') ?></h4>
                </div>
            </div>
        </div>
    </div>

    <div class="glass-card">
        <h3 class="mt-0 mb-3">Lista de Compras Inteligente</h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Produto</th>
                        <th class="text-center">Total Necessário</th>
                        <th class="text-center">Estoque Atual</th>
                        <th class="text-center">Precisa Comprar</th>
                        <th class="text-center">Unidade</th>
                        <th>Custo da Compra</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($resultado['lista_compras'] as $item): ?>
                        <tr>
                            <td><?= htmlspecialchars($item['nome_produto']) ?></td>
                            <td class="text-center"><?= number_format($item['quantidade_total'], 3, ',', '.') ?></td>
                            <td class="text-center"><?= number_format($item['estoque_atual'], 3, ',', '.') ?></td>
                            <td class="text-center fw-bold" style="color: var(--primary-color);"><?= number_format($item['necessario_comprar'], 3, ',', '.') ?></td>
                            <td class="text-center"><?= htmlspecialchars($item['unidade']) ?></td>
                            <td>R$ <?= number_format($item['custo_compra_necessaria'], 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-grid gap-2 mt-3">
        <a href="../processa_php/processa_acao.php?acao=realizar_evento" class="btn btn-primary btn-lg" onclick="return confirm('Tem certeza que deseja confirmar este evento? Esta ação dará baixa nos itens do seu estoque.')">
            Realizar Evento e Dar Baixa no Estoque
        </a>
        <a href="simulador.php" class="btn btn-secondary btn-lg">Fazer Nova Simulação</a>
    </div>

</div>

<?php include "../includes/footer.php"; ?>