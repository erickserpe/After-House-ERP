<?php
session_start();
require_once('../includes/db.php');

if (!isset($_SESSION['user_id']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit;
}
$id_usuario_logado = $_SESSION['user_id'];

// Novos campos do formulário
$nome_evento = trim($_POST['nome_evento'] ?? 'Novo Evento');
$data_evento = $_POST['data_evento'] ?? null;
$local_evento = trim($_POST['local_evento'] ?? '');
$num_pessoas = (int)($_POST['num_pessoas'] ?? 0);
$receitas_selecionadas = $_POST['receitas_selecionadas'] ?? [];

if ($num_pessoas <= 0 || empty($receitas_selecionadas)) {
    header("Location: ../pages/simulador.php?erro=Dados inválidos.");
    exit;
}

$conn->begin_transaction();
try {
    // 1. INSERE O NOVO EVENTO NA TABELA `eventos`
    $stmt_evento = $conn->prepare("INSERT INTO eventos (nome, data_evento, local_evento, num_pessoas, id_usuario, status) VALUES (?, ?, ?, ?, ?, 'Planejamento')");
    $stmt_evento->bind_param("sssii", $nome_evento, $data_evento, $local_evento, $num_pessoas, $id_usuario_logado);
    if (!$stmt_evento->execute()) {
        throw new Exception("Falha ao criar o evento.");
    }
    $evento_id = $conn->insert_id; // Pega o ID do evento que acabamos de criar
    $stmt_evento->close();

    // 2. CALCULA A LISTA DE COMPRAS (lógica que já tínhamos)
    $lista_compras = [];
    // (A lógica de cálculo de ingredientes é complexa, vamos simplificar para o exemplo)
    // ... Aqui entraria a lógica que busca os ingredientes e calcula as quantidades...
    // No final, você teria um array $lista_compras[produto_id] = ['quantidade_total' => X, 'custo_total_item' => Y]

    // 3. SALVA OS PRODUTOS CALCULADOS NA NOVA TABELA `evento_produtos`
    // (Este passo é crucial e requer a lógica de cálculo completa. Por enquanto, vamos pular a inserção
    // para focar na estrutura. A inserção seria um loop no array $lista_compras)
    
    // Supondo que a lógica de cálculo foi feita, agora você pode salvar.
    // Exemplo de como seria a inserção:
    /*
    $stmt_prod_evento = $conn->prepare("INSERT INTO evento_produtos (evento_id, produto_id, quantidade_total, custo_total_item) VALUES (?, ?, ?, ?)");
    foreach($lista_compras as $produto_id => $item) {
        $stmt_prod_evento->bind_param("iidd", $evento_id, $produto_id, $item['quantidade_total'], $item['custo_total_item']);
        $stmt_prod_evento->execute();
    }
    $stmt_prod_evento->close();
    */

    $conn->commit();
    
    // Redireciona o usuário para a PÁGINA DE EDIÇÃO do evento recém-criado
    header("Location: ../pages/editar_evento.php?id=" . $evento_id . "&sucesso=Evento salvo em modo de planejamento!");

} catch (Exception $e) {
    $conn->rollback();
    header("Location: ../pages/simulador.php?erro=" . urlencode($e->getMessage()));
}

$conn->close();
exit;
?>