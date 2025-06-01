<?php
session_start(); // ESSENCIAL
require_once('../includes/db.php'); //

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php?erro=" . urlencode("Você precisa estar logado para cadastrar uma receita."));
    exit;
}
$id_usuario_logado = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_receita = trim($_POST['nome_receita'] ?? '');
    $margem_lucro_str = trim($_POST['margem_lucro'] ?? '0');
    $margem_lucro = (float)str_replace(',', '.', $margem_lucro_str);
    $produtos_ids = $_POST['ingredientes']['produto_id'] ?? [];
    $quantidades_str = $_POST['ingredientes']['quantidade'] ?? [];
    
    if (empty($nome_receita) || !is_numeric(str_replace(',', '.', $margem_lucro_str)) || $margem_lucro < 0) {
        header("Location: ../pages/receitas.php?erro=" . urlencode("Nome da receita e margem de lucro são obrigatórios e válidos."));
        exit;
    }
    // Validação de ingredientes (pode ser mais robusta)
    $has_valid_ingredient = false;
    if (!empty($produtos_ids)) {
        foreach ($produtos_ids as $index => $produto_id_str) {
            if (!empty($produto_id_str) && isset($quantidades_str[$index]) && !empty($quantidades_str[$index]) && is_numeric(str_replace(',', '.', $quantidades_str[$index])) && (float)str_replace(',', '.', $quantidades_str[$index]) > 0) {
                $has_valid_ingredient = true;
                break;
            }
        }
    }
    if (!$has_valid_ingredient && !empty($produtos_ids)) { // Permite receitas sem ingredientes inicialmente, mas se ingredientes forem enviados, devem ser válidos
         // header("Location: ../pages/receitas.php?erro=" . urlencode("Adicione pelo menos um ingrediente válido com produto e quantidade positiva."));
         // exit;
         // Decida se uma receita pode ser criada sem ingredientes ou não. Se sim, remova essa verificação ou ajuste-a.
         // Se for permitido, certifique-se que o loop de ingredientes abaixo lide com arrays vazios.
    }


    $conn->begin_transaction(); 

    try {
        // Adicionamos 'id_usuario' na query e 'i' no bind_param
        $stmt_receita = $conn->prepare("INSERT INTO receitas (nome, margem_lucro, id_usuario) VALUES (?, ?, ?)");
        if ($stmt_receita === false) {
            throw new Exception("Erro ao preparar query da receita: " . $conn->error);
        }
        // 's' para string, 'd' para double, 'i' para integer
        $stmt_receita->bind_param("sdi", $nome_receita, $margem_lucro, $id_usuario_logado);
        if (!$stmt_receita->execute()) {
            throw new Exception("Erro ao salvar receita: " . $stmt_receita->error);
        }
        $receita_id = $conn->insert_id;
        $stmt_receita->close();

        // Inserir ingredientes vinculados (se houver)
        if ($has_valid_ingredient && !empty($produtos_ids)) { // Somente se ingredientes válidos foram passados
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
                        throw new Exception("Erro ao salvar ingrediente (Produto ID: $produto_id): " . $stmt_ingrediente->error);
                    }
                }
            }
            $stmt_ingrediente->close();
        }

        $conn->commit(); 
        header("Location: ../pages/receitas.php?sucesso=1");

    } catch (Exception $e) {
        $conn->rollback(); 
        // error_log("Erro ao salvar receita: " . $e->getMessage());
        header("Location: ../pages/receitas.php?erro=" . urlencode("Erro ao salvar receita. Detalhes: " . $e->getMessage()));
    } finally {
        if (isset($stmt_receita) && $stmt_receita) $stmt_receita->close();
        if (isset($stmt_ingrediente) && $stmt_ingrediente) $stmt_ingrediente->close();
        $conn->close();
    }
    exit;

} else {
    header("Location: ../pages/receitas.php");
    exit;
}
?>