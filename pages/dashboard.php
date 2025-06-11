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

<div class="container">
    <h2 class="mb-4">Dashboard</h2>

    <div class="row">
        <div class="col-md-4">
            <div class="text-center glass-card">
                <h5 class="card-title text-muted">Total de Produtos</h5>
                <p class="display-4" style="color: var(--primary-color);"><?= $total_produtos ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="text-center glass-card">
                <h5 class="card-title text-muted">Total de Fornecedores</h5>
                <p class="display-4" style="color: var(--primary-color);"><?= $total_fornecedores ?></p>
            </div>
        </div>
        <div class="col-md-4">
            <div class="text-center glass-card">
                <h5 class="card-title text-muted">Total de Receitas</h5>
                <p class="display-4" style="color: var(--primary-color);"><?= $total_receitas ?></p>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-10 offset-md-1">
            <div class="glass-card">
                <h4 class="mb-4">Produtos por Fornecedor</h4>
                <div>
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
            type: 'doughnut', 
            data: {
                labels: <?php echo $labels_fornecedor; ?>,
                datasets: [{
                    label: 'Nº de Produtos',
                    data: <?php echo $data_fornecedor; ?>,
                    
                    // --- CORES ATUALIZADAS PARA LARANJA NEON ---
                    // Para mudar para outra cor (ex: Rosa), altere os valores RGBA aqui.
                    // Rosa: 240, 44, 142
                    // Azul: 0, 209, 255
                    backgroundColor: [ 
                        'rgba(255, 165, 0, 0.8)', // Laranja Neon
                        'rgba(255, 165, 0, 0.6)',
                        'rgba(255, 165, 0, 0.4)',
                        'rgba(255, 255, 255, 0.3)', // Cores neutras para o resto
                        'rgba(255, 255, 255, 0.2)',
                        'rgba(255, 255, 255, 0.1)'
                    ],
                    borderColor: 'rgba(255, 165, 0, 1)', // Borda Laranja Neon
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            // Cor da legenda atualizada para combinar com o tema
                            color: '#f0f0f0' 
                        }
                    }
                }
            }
        });
    }
});
</script>

<?php include "../includes/footer.php"; ?>