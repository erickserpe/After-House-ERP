<?php
session_start();
require_once('../includes/db.php'); // Conexão com o banco ($conn)

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    // Idealmente, redirecionar para login com mensagem
    // Mas como o form só deve ser acessível logado, podemos redirecionar para o form de mudar senha com erro.
    header("Location: ../mudar_senha.php?erro=" . urlencode("Sessão inválida ou expirada."));
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = $_SESSION['user_id'];
    $senha_atual_submetida = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirma_nova_senha = $_POST['confirma_nova_senha'] ?? '';

    // Validações básicas
    if (empty($senha_atual_submetida) || empty($nova_senha) || empty($confirma_nova_senha)) {
        header("Location: ../mudar_senha.php?erro=" . urlencode("Todos os campos são obrigatórios."));
        exit;
    }

    if (strlen($nova_senha) < 6) {
        header("Location: ../mudar_senha.php?erro=" . urlencode("A nova senha deve ter pelo menos 6 caracteres."));
        exit;
    }

    if ($nova_senha !== $confirma_nova_senha) {
        header("Location: ../mudar_senha.php?erro=" . urlencode("A nova senha e a confirmação não coincidem."));
        exit;
    }

    // Buscar a senha atual (hash) do usuário no banco
    $stmt = $conn->prepare("SELECT senha FROM usuarios WHERE id = ?"); // Tabela 'usuarios', coluna 'senha'
    if ($stmt === false) {
        header("Location: ../mudar_senha.php?erro=" . urlencode("Erro no sistema (DB Select Prepare)."));
        exit;
    }
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        header("Location: ../mudar_senha.php?erro=" . urlencode("Usuário não encontrado."));
        exit;
    }
    
    $usuario = $result->fetch_assoc();
    $hash_senha_atual_db = $usuario['senha'];
    $stmt->close();

    // Verificar se a senha atual submetida corresponde à do banco
    if (!password_verify($senha_atual_submetida, $hash_senha_atual_db)) {
        header("Location: ../mudar_senha.php?erro=" . urlencode("Senha atual incorreta."));
        exit;
    }

    // Verificar se a nova senha é diferente da senha atual (opcional, mas recomendado)
    if (password_verify($nova_senha, $hash_senha_atual_db)) {
        header("Location: ../mudar_senha.php?erro=" . urlencode("A nova senha não pode ser igual à senha atual."));
        exit;
    }

    // Hash da nova senha
    $hash_nova_senha = password_hash($nova_senha, PASSWORD_DEFAULT);
    if ($hash_nova_senha === false) {
        header("Location: ../mudar_senha.php?erro=" . urlencode("Erro ao processar a nova senha."));
        exit;
    }

    // Atualizar a senha no banco de dados
    $stmt_update = $conn->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
    if ($stmt_update === false) {
        header("Location: ../mudar_senha.php?erro=" . urlencode("Erro no sistema (DB Update Prepare)."));
        exit;
    }
    $stmt_update->bind_param("si", $hash_nova_senha, $id_usuario);

    if ($stmt_update->execute()) {
        // Invalidar outras sessões ativas (opcional, mas bom para segurança)
        // session_regenerate_id(true); // Isso regenera o ID da sessão atual.
                                    // Para invalidar *outras* sessões, seria mais complexo.

        header("Location: ../mudar_senha.php?sucesso=" . urlencode("Senha alterada com sucesso!"));
    } else {
        header("Location: ../mudar_senha.php?erro=" . urlencode("Erro ao atualizar a senha: " . $stmt_update->error));
    }
    $stmt_update->close();
    $conn->close();

} else {
    // Redirecionar se não for POST
    header("Location: ../mudar_senha.php");
    exit;
}
?> 