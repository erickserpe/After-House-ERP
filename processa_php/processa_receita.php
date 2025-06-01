<?php
// processa_php/processa_receita.php
session_start();
require_once('../includes/db.php');

if (!isset($_SESSION['user_id'])) {
    // Apenas usuários logados podem adicionar receitas
    header("Location: ../login.php?erro=" . urlencode("Você precisa estar logado."));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_receita = trim($_POST['nome_receita'] ?? '');
    $margem_lucro_str = trim($_POST['margem_lucro'] ?? '0');
    $margem_lucro = (float)str_replace(',', '.', $margem_lucro_str);

    $produtos_ids = $_POST['ingredientes']['produto_id'] ?? [];
    $quantidades_str = $_POST['ingredientes']['quantidade'] ?? [];
    
    // Validações
    if (empty($nome_receita) || !is_numeric($margem_lucro_str) || $margem_lucro < 0) {
        header("Location: ../pages/receitas.php?erro=" . urlencode("Nome da receita e margem de lucro são obrigatórios e válidos."));
        exit;
    }
    if (empty($produtos_ids) || count($produtos_ids) !== count($quantidades_str)) {
        header("Location: ../pages/receitas.php?erro=" . urlencode("Adicione pelo menos um ingrediente com produto e quantidade."));
        exit;
    }

    $conn->begin_transaction(); // Iniciar transação

    try {
        // Inserir nova receita
        $stmt_receita = $conn->prepare("INSERT INTO receitas (nome, margem_lucro) VALUES (?, ?)");
        if ($stmt_receita === false) {
            throw new Exception("Erro ao preparar query da receita: " . $conn->error);
        }
        $stmt_receita->bind_param("sd", $nome_receita, $margem_lucro);
        if (!$stmt_receita->execute()) {
            throw new Exception("Erro ao salvar receita: " . $stmt_receita->error);
        }
        $receita_id = $conn->insert_id; // Obter ID da receita inserida
        $stmt_receita->close();

        // Inserir ingredientes vinculados
        $stmt_ingrediente = $conn->prepare("INSERT INTO receita_ingredientes (receita_id, produto_id, quantidade) VALUES (?, ?, ?)");
        if ($stmt_ingrediente === false) {
            throw new Exception("Erro ao preparar query dos ingredientes: " . $conn->error);
        }

        foreach ($produtos_ids as $index => $produto_id_str) {
            $produto_id = (int)$produto_id_str;
            $quantidade_ingred_str = $quantidades_str[$index] ?? '0';
            $quantidade_ingred = (float)str_replace(',', '.', $quantidade_ingred_str);

            if ($produto_id > 0 && $quantidade_ingred > 0) {
                $stmt_ingrediente->bind_param("iid", $receita_id, $produto_id, $quantidade_ingred);
                if (!$stmt_ingrediente->execute()) {
                    throw new Exception("Erro ao salvar ingrediente: " . $stmt_ingrediente->error);
                }
            } else {
                 // Opcional: pode ser um erro se algum ingrediente for inválido
                 // throw new Exception("Ingrediente inválido fornecido.");
            }
        }
        $stmt_ingrediente->close();

        $conn->commit(); // Confirmar transação
        header("Location: ../pages/receitas.php?sucesso=1");

    } catch (Exception $e) {
        $conn->rollback(); // Reverter transação em caso de erro
        header("Location: ../pages/receitas.php?erro=" . urlencode("Erro ao salvar receita: " . $e->getMessage()));
    } finally {
        $conn->close();
    }
    exit;

} else {
    header("Location: ../pages/receitas.php");
    exit;
}
?>