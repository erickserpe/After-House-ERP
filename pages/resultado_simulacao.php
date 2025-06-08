<?php
session_start();
// Verifica se o usuário está logado e se existe um resultado de simulação
if (!isset($_SESSION['user_id']) || !isset($_SESSION['simulacao_resultado'])) {
    header("Location: simulador.php");
    exit;
}

// Pega os resultados da sessão e depois os remove para não serem exibidos novamente
$resultado = $_SESSION['simulacao_resultado'];
unset($_SESSION['simulacao_resultado']);

// Inclui o cabeçalho
include "../includes/header.php";
?>

<div class="container mt-4">
    
    <div class="p-4 mb-4 text-white bg-primary rounded-3">
        <h2>Resultado da Simulação</h2>
        <p class="lead">
            Evento: <strong><?= htmlspecialchars($resultado['nome_evento']) ?></strong> para 
            <strong><?= $resultado['num_pessoas'] ?></strong> pessoas.
        </p>
    </div>

    <h3 class="mt-5">Resumo Financeiro</h3>
    <div class="row text-center">
        <div class="col-md-4">
            <div class="card text-white bg-danger mb-3">
                <div class="card-header">CUSTO TOTAL DO EVENTO</div>
                <div class="card-body">
                    <h4 class="card-title">R$ <?= number_format($resultado['custo_total_evento'], 2, ',', '.') ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-dark bg-warning mb-3">
                <div class="card-header">FATURAMENTO BRUTO SUGERIDO</div>
                <div class="card-body">
                    <h4 class="card-title">R$ <?= number_format($resultado['venda_total_sugerida'], 2, ',', '.') ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-header">LUCRO LÍQUIDO ESTIMADO</div>
                <div class="card-body">
                    <h4 class="card-title">R$ <?= number_format($resultado['lucro_estimado'], 2, ',', '.') ?></h4>
                </div>
            </div>
        </div>
    </div>


    <h3 class="mt-5 mb-3">Lista de Compras Necessária</h3>
    <div class="table-responsive">
        <table class="table table-bordered table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Produto</th>
                    <th>Quantidade Total</th>
                    <th>Unidade</th>
                    <th>Custo Estimado do Item</th>
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

    <div class="d-grid gap-2 mt-5">
        <a href="simulador.php" class="btn btn-secondary btn-lg">Fazer Nova Simulação</a>
    </div>

</div>

<?php include "../includes/footer.php"; ?>