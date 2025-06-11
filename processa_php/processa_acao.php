<?php
session_start();
require_once('../includes/db.php');

if (!isset($_SESSION['user_id'])) { header("Location: ../login.php"); exit; }
$id_usuario_logado = $_SESSION['user_id'];

$acao = $_REQUEST['acao'] ?? '';
$tipo = $_REQUEST['tipo'] ?? '';
$id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;

switch ($acao) {
    case 'excluir':
        handle_excluir($conn, $tipo, $id, $id_usuario_logado);
        break;
    case 'editar':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') handle_editar($conn, $tipo, $_POST, $id_usuario_logado);
        break;
    case 'realizar_evento':
        handle_realizar_evento($conn, $id_usuario_logado, $id);
        break;
    case 'adicionar_estoque':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') handle_adicionar_estoque($conn, $_POST, $id_usuario_logado);
        break;
    default:
        header("Location: ../pages/dashboard.php?erro=" . urlencode("Ação desconhecida."));
        exit;
}

function handle_realizar_evento($conn, $id_usuario, $id_evento) {
    if ($id_evento === 0) { header("Location: ../pages/eventos.php?erro=ID do evento inválido."); exit; }
    $conn->begin_transaction();
    try {
        $produtos_do_evento = [];
        $stmt_get = $conn->prepare("SELECT produto_id, quantidade_total FROM evento_produtos WHERE evento_id = ?");
        $stmt_get->bind_param("i", $id_evento);
        $stmt_get->execute();
        $result = $stmt_get->get_result();
        while($row = $result->fetch_assoc()){ $produtos_do_evento[] = $row; }
        $stmt_get->close();
        if(empty($produtos_do_evento)) throw new Exception("Nenhum produto encontrado para este evento.");
        $stmt_update = $conn->prepare("UPDATE produtos SET quantidade_estoque = quantidade_estoque - ? WHERE id = ? AND id_usuario = ?");
        foreach ($produtos_do_evento as $produto) {
            $stmt_update->bind_param("dii", $produto['quantidade_total'], $produto['produto_id'], $id_usuario);
            if (!$stmt_update->execute()) throw new Exception("Falha ao dar baixa no estoque do produto ID: " . $produto['produto_id']);
        }
        $stmt_update->close();
        $stmt_status = $conn->prepare("UPDATE eventos SET status = 'Realizado' WHERE id = ? AND id_usuario = ?");
        $stmt_status->bind_param("ii", $id_evento, $id_usuario);
        $stmt_status->execute();
        $stmt_status->close();
        $conn->commit();
        header("Location: ../pages/eventos.php?sucesso=Evento realizado e baixa de estoque concluída!");
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: ../pages/editar_evento.php?id=".$id_evento."&erro=" . urlencode($e->getMessage()));
    }
    $conn->close(); exit;
}
function handle_adicionar_estoque($conn, $post_data, $id_usuario) {
    $produto_id = (int)($post_data['produto_id'] ?? 0);
    $quantidade_adicionar = (float)str_replace(',', '.', trim($post_data['quantidade_adicionar'] ?? '0'));
    if ($produto_id === 0 || $quantidade_adicionar <= 0) { header("Location: ../pages/estoque.php"); exit; }
    $stmt = $conn->prepare("UPDATE produtos SET quantidade_estoque = quantidade_estoque + ? WHERE id = ? AND id_usuario = ?");
    $stmt->bind_param("dii", $quantidade_adicionar, $produto_id, $id_usuario);
    if ($stmt->execute()) { header("Location: ../pages/estoque.php?sucesso=Estoque atualizado!"); } 
    else { header("Location: ../pages/estoque.php?erro=Erro ao atualizar."); }
    $stmt->close(); $conn->close(); exit;
}
function handle_excluir($conn, $tipo, $id, $id_usuario) {
    if ($id === 0) { header("Location: ../pages/dashboard.php?erro=" . urlencode("ID inválido.")); exit; }
    $config = get_config($tipo);
    if (!$config) { header("Location: ../pages/dashboard.php?erro=" . urlencode("Tipo desconhecido.")); exit; }
    $sql = "DELETE FROM {$config['tabela']} WHERE id = ? AND id_usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id, $id_usuario);
    if ($stmt->execute()) { header("Location: ../pages/{$config['pagina']}?sucesso=" . urlencode(ucfirst($tipo) . " excluído."));
    } else { header("Location: ../pages/{$config['pagina']}?erro=" . urlencode("Erro ao excluir.")); }
    $stmt->close(); $conn->close(); exit;
}
function handle_editar($conn, $tipo, $post_data, $id_usuario) {
    $id = isset($post_data['id']) ? (int)$post_data['id'] : 0;
    if ($id === 0) { header("Location: ../pages/dashboard.php?erro=" . urlencode("ID inválido.")); exit; }
    $config = get_config($tipo);
    if (!$config) { header("Location: ../pages/dashboard.php?erro=" . urlencode("Tipo desconhecido.")); exit; }
    $conn->begin_transaction();
    try {
        switch ($tipo) {
            case 'fornecedor':
                $sql = "UPDATE fornecedores SET nome = ?, telefone = ?, email = ?, endereco = ? WHERE id = ? AND id_usuario = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssii", $post_data['nome'], $post_data['telefone'], $post_data['email'], $post_data['endereco'], $id, $id_usuario);
                if (!$stmt->execute()) throw new Exception("Falha ao atualizar fornecedor.");
                break;
            case 'produto':
                $preco_compra = (float)str_replace(',', '.', $post_data['preco_compra'] ?? '0');
                $fornecedor_id = empty($post_data['fornecedor_id']) ? null : (int)$post_data['fornecedor_id'];
                $sql = "UPDATE produtos SET nome = ?, fornecedor_id = ?, tipo = ?, unidade = ?, preco_compra = ? WHERE id = ? AND id_usuario = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sissdii", $post_data['nome'], $fornecedor_id, $post_data['tipo'], $post_data['unidade'], $preco_compra, $id, $id_usuario);
                if (!$stmt->execute()) throw new Exception("Falha ao atualizar produto.");
                break;
            case 'receita':
                $margem_lucro = (float)str_replace(',', '.', $post_data['margem_lucro']);
                $stmt_receita = $conn->prepare("UPDATE receitas SET nome = ?, margem_lucro = ? WHERE id = ? AND id_usuario = ?");
                $stmt_receita->bind_param("sdii", $post_data['nome_receita'], $margem_lucro, $id, $id_usuario);
                if (!$stmt_receita->execute()) throw new Exception("Falha ao atualizar dados da receita.");
                $stmt_receita->close();
                $stmt_delete = $conn->prepare("DELETE FROM receita_ingredientes WHERE receita_id = ?");
                $stmt_delete->bind_param("i", $id);
                if (!$stmt_delete->execute()) throw new Exception("Falha ao limpar ingredientes antigos.");
                $stmt_delete->close();
                $produtos_ids = $post_data['ingredientes']['produto_id'] ?? [];
                $quantidades_str = $post_data['ingredientes']['quantidade'] ?? [];
                if (!empty($produtos_ids)) {
                    $stmt_insert = $conn->prepare("INSERT INTO receita_ingredientes (receita_id, produto_id, quantidade) VALUES (?, ?, ?)");
                    foreach ($produtos_ids as $index => $produto_id_str) {
                        $produto_id = (int)$produto_id_str;
                        $quantidade = (float)str_replace(',', '.', $quantidades_str[$index] ?? '0');
                        if ($produto_id > 0 && $quantidade > 0) {
                            $stmt_insert->bind_param("iid", $id, $produto_id, $quantidade);
                            if (!$stmt_insert->execute()) throw new Exception("Falha ao inserir novo ingrediente.");
                        }
                    }
                    $stmt_insert->close();
                }
                break;
            default: throw new Exception("Tipo de edição desconhecido.");
        }
        $conn->commit();
        header("Location: ../pages/{$config['pagina']}?sucesso=" . urlencode(ucfirst($tipo) . " atualizado."));
    } catch (Exception $e) {
        $conn->rollback();
        header("Location: ../pages/editar_{$tipo}.php?id={$id}&erro=" . urlencode($e->getMessage()));
    }
    $conn->close(); exit;
}
function get_config($tipo) {
    $configs = [ 'fornecedor' => ['tabela' => 'fornecedores', 'pagina' => 'fornecedores.php'], 'produto' => ['tabela' => 'produtos', 'pagina' => 'produtos.php'], 'receita' => ['tabela' => 'receitas', 'pagina' => 'receitas.php'], ];
    return $configs[$tipo] ?? null;
}
?>