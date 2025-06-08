<?php
session_start();
require_once('../includes/db.php');

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit;
}
$id_usuario_logado = $_SESSION['user_id'];

// Garante que o acesso seja via POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../pages/simulador.php");
    exit;
}

// 1. RECEBER E VALIDAR OS DADOS DO FORMULÁRIO
$nome_evento = trim($_POST['nome_evento'] ?? 'Evento Sem Nome');
$num_pessoas = (int)($_POST['num_pessoas'] ?? 0);
$receitas_selecionadas_ids = $_POST['receitas_selecionadas'] ?? [];

if ($num_pessoas <= 0) {
    $_SESSION['simulacao_erro'] = "O número de pessoas deve ser maior que zero.";
    header("Location: ../pages/simulador.php");
    exit;
}
if (empty($receitas_selecionadas_ids)) {
    $_SESSION['simulacao_erro'] = "Você precisa selecionar pelo menos uma receita para o evento.";
    header("Location: ../pages/simulador.php");
    exit;
}

// 2. BUSCAR TODOS OS DADOS NECESSÁRIOS DO BANCO
// Prepara a query para buscar os ingredientes das receitas selecionadas
$placeholders = implode(',', array_fill(0, count($receitas_selecionadas_ids), '?'));
$types = str_repeat('i', count($receitas_selecionadas_ids));

$sql = "SELECT 
            r.id as receita_id, r.nome as nome_receita, r.margem_lucro,
            p.id as produto_id, p.nome as nome_produto, p.unidade, p.preco_compra,
            ri.quantidade as qtd_por_receita
        FROM receita_ingredientes ri
        JOIN produtos p ON ri.produto_id = p.id
        JOIN receitas r ON ri.receita_id = r.id
        WHERE ri.receita_id IN ($placeholders)
          AND r.id_usuario = ? 
          AND p.id_usuario = ?"; // Garante que tanto a receita quanto o produto são do usuário

$stmt = $conn->prepare($sql);
$params = array_merge($receitas_selecionadas_ids, [$id_usuario_logado, $id_usuario_logado]);
$stmt->bind_param($types . 'ii', ...$params);
$stmt->execute();
$ingredientes_brutos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$conn->close();

// 3. PROCESSAR OS DADOS E FAZER OS CÁLCULOS
$lista_compras = [];
$custos_por_receita = [];

foreach($ingredientes_brutos as $ing) {
    // Calcula o custo base de cada receita individualmente
    if (!isset($custos_por_receita[$ing['receita_id']])) {
        $custos_por_receita[$ing['receita_id']] = [
            'nome' => $ing['nome_receita'],
            'custo_unitario' => 0,
            'margem_lucro' => (float)$ing['margem_lucro']
        ];
    }
    $custos_por_receita[$ing['receita_id']]['custo_unitario'] += (float)$ing['qtd_por_receita'] * (float)$ing['preco_compra'];

    // Agrega a quantidade total de cada produto para a lista de compras
    $produto_id = $ing['produto_id'];
    $qtd_total_produto = (float)$ing['qtd_por_receita'] * $num_pessoas;

    if (isset($lista_compras[$produto_id])) {
        $lista_compras[$produto_id]['quantidade_total'] += $qtd_total_produto;
    } else {
        $lista_compras[$produto_id] = [
            'nome_produto' => $ing['nome_produto'],
            'unidade' => $ing['unidade'],
            'preco_compra_unitario' => (float)$ing['preco_compra'],
            'quantidade_total' => $qtd_total_produto
        ];
    }
}

// 4. CALCULAR TOTAIS FINANCEIROS
$custo_total_evento = 0;
foreach($lista_compras as &$item) { // O '&' permite modificar o array diretamente
    $item['custo_total_item'] = $item['quantidade_total'] * $item['preco_compra_unitario'];
    $custo_total_evento += $item['custo_total_item'];
}
unset($item); // Importante remover a referência após o loop

$venda_total_sugerida = 0;
foreach($custos_por_receita as $rec) {
    $preco_venda_unitario = $rec['custo_unitario'] * (1 + $rec['margem_lucro'] / 100);
    // Multiplica o preço de venda de uma unidade pelo número de pessoas
    $venda_total_sugerida += $preco_venda_unitario * $num_pessoas;
}

// 5. SALVAR RESULTADOS NA SESSÃO E REDIRECIONAR
$_SESSION['simulacao_resultado'] = [
    'nome_evento' => $nome_evento,
    'num_pessoas' => $num_pessoas,
    'lista_compras' => $lista_compras,
    'custo_total_evento' => $custo_total_evento,
    'venda_total_sugerida' => $venda_total_sugerida,
    'lucro_estimado' => $venda_total_sugerida - $custo_total_evento
];

header("Location: ../pages/resultado_simulacao.php");
exit();
?>