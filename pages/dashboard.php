<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
$id_usuario_logado = $_SESSION['user_id'];

require_once "../includes/db.php";

// --- BUSCAR DADOS PARA OS INDICADORES (KPIs) ---

// Total de Produtos
$stmt_produtos = $conn->prepare("SELECT COUNT(id) as total FROM produtos WHERE id_usuario = ?");
$stmt_produtos->bind_param("i", $id_usuario_logado);
$stmt_produtos->execute();
$total_produtos = $stmt_produtos->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_produtos->close();

// Total de Fornecedores
$stmt_fornecedores = $conn->prepare("SELECT COUNT(id) as total FROM fornecedores WHERE id_usuario = ?");
$stmt_fornecedores->bind_param("i", $id_usuario_logado);
$stmt_fornecedores->execute();
$total_fornecedores = $stmt_fornecedores->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_fornecedores->close();

// Total de Receitas
$stmt_receitas = $conn->prepare("SELECT COUNT(id) as total FROM receitas WHERE id_usuario = ?");
$stmt_receitas->bind_param("i", $id_usuario_logado);
$stmt_receitas->execute();
$total_receitas = $stmt_receitas->get_result()->fetch_assoc()['total'] ?? 0;
$stmt_receitas->close();


// --- BUSCAR DADOS PARA O GRÁFICO: PRODUTOS POR FORNECEDOR ---
$dados_grafico_fornecedor = [];
$sql_grafico = "SELECT f.nome as fornecedor_nome, COUNT(p.id) as total_produtos 
                FROM produtos p 
                LEFT JOIN fornecedores f ON p.fornecedor_id = f.id 
                WHERE p.id_usuario = ? 
                GROUP BY f.nome 
                ORDER BY total_produtos DESC";
$stmt_grafico = $conn->prepare($sql_grafico);
$stmt_grafico->bind_param("i", $id_usuario_logado);
$stmt_grafico->execute();
$result_grafico = $stmt_grafico->get_result();
while ($row = $result_grafico->fetch_assoc()) {
    $row['fornecedor_nome'] = $row['fornecedor_nome'] ?? 'Sem Fornecedor';
    $dados_grafico_fornecedor[] = $row;
}
$stmt_grafico->close();
$conn->close();

// Prepara os dados para o JavaScript
$labels_fornecedor = json_encode(array_column($dados_grafico_fornecedor, 'fornecedor_nome'));
$data_fornecedor = json_encode(array_column($dados_grafico_fornecedor, 'total_produtos'));

include "../includes/header.php";
?>

<div class="container mt-4">
    <h2 class="mb-4">Dashboard</h2>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm text-center mb-3">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total de Produtos</h5>
                    <p class="display-4" style="color: var(--primary-color);"><?= $total_produtos ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm text-center mb-3">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total de Fornecedores</h5>
                    <p class="display-4" style="color: var(--primary-color);"><?= $total_fornecedores ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card shadow-sm text-center mb-3">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total de Receitas</h5>
                    <p class="display-4" style="color: var(--primary-color);"><?= $total_receitas ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow-sm">
                <div class="card-header">
                    Produtos por Fornecedor
                </div>
                <div class="card-body">
                    <canvas id="graficoFornecedores"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configuração do Gráfico de Fornecedores
    const ctxFornecedores = document.getElementById('graficoFornecedores');
    if (ctxFornecedores) {
        new Chart(ctxFornecedores, {
            type: 'doughnut', // Mudei para 'doughnut' para um visual mais moderno
            data: {
                labels: <?php echo $labels_fornecedor; ?>,
                datasets: [{
                    label: 'Nº de Produtos',
                    data: <?php echo $data_fornecedor; ?>,
                    // PALETA DE CORES NOVA, USANDO TONS DE LARANJA E CINZA
                    backgroundColor: [
                        'rgba(255, 165, 0, 0.9)', // Laranja Principal
                        'rgba(255, 165, 0, 0.7)',
                        'rgba(255, 165, 0, 0.5)',
                        'rgba(108, 117, 125, 0.7)', // Cinza
                        'rgba(108, 117, 125, 0.5)',
                        'rgba(108, 117, 125, 0.3)'
                    ],
                    borderColor: '#FFFFFF', // Borda branca para separar as fatias
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    }
});
</script>

<?php include "../includes/footer.php"; ?>