<?php
// A sessão agora é iniciada de forma segura no header.php
// por isso não precisamos mais de session_start() aqui.

// 1. INCLUI O HEADER PRIMEIRO (QUE JÁ INICIA A SESSÃO)
include "../includes/header.php";

// 2. VERIFICA O LOGIN DO USUÁRIO
if (!isset($_SESSION['user_id'])) { 
    // Usamos JavaScript para redirecionar de forma mais segura após o header ter sido enviado
    echo '<script>window.location.href = "../login.php";</script>';
    exit; 
}
$id_usuario_logado = $_SESSION['user_id'];

// 3. AGORA FAZ A CONEXÃO COM O BANCO
require_once "../includes/db.php";

// 4. EXECUTA A LÓGICA DA PÁGINA
$eventos = [];
$sql = "SELECT id, nome, data_evento, num_pessoas, status FROM eventos WHERE id_usuario = ? ORDER BY data_evento DESC";

// Verificação de segurança: Checa se a preparação da query funcionou
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    // Em um ambiente de produção, seria ideal logar este erro em vez de exibi-lo.
    // Por enquanto, vamos mostrar uma mensagem amigável.
    echo "<div class='container'><div class='alert alert-danger'>Erro no sistema. Por favor, tente mais tarde.</div></div>";
    include "../includes/footer.php";
    exit;
}

$stmt->bind_param("i", $id_usuario_logado);
$stmt->execute();
$result = $stmt->get_result();
while($row = $result->fetch_assoc()) { 
    $eventos[] = $row; 
}
$stmt->close();
$conn->close();
?>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Meus Eventos</h2>
        <a href="simulador.php" class="btn btn-primary">Planejar Novo Evento</a>
    </div>
    
    <?php if (isset($_GET['sucesso'])): ?>
        <div class="alert alert-success" role="alert"><?= htmlspecialchars($_GET['sucesso']) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['erro'])): ?>
        <div class="alert alert-danger" role="alert"><?= htmlspecialchars($_GET['erro']) ?></div>
    <?php endif; ?>

    <div class="glass-card">
        <h3 class="mt-0">Eventos Salvos</h3>
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Nome do Evento</th>
                        <th>Data</th>
                        <th>Nº Pessoas</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($eventos)): ?>
                        <tr><td colspan="5" class="text-center">Nenhum evento salvo ainda. Comece planejando um!</td></tr>
                    <?php endif; ?>
                    <?php foreach($eventos as $evento): ?>
                    <tr>
                        <td><?= htmlspecialchars($evento['nome']) ?></td>
                        <td><?= $evento['data_evento'] ? date('d/m/Y', strtotime($evento['data_evento'])) : 'A definir' ?></td>
                        <td><?= $evento['num_pessoas'] ?></td>
                        <td>
                           <?php 
                           $status_class = 'bg-secondary';
                           if ($evento['status'] == 'Planejamento') $status_class = 'bg-info text-dark';
                           if ($evento['status'] == 'Realizado') $status_class = 'bg-success';
                           ?>
                           <span class="badge <?= $status_class ?>"><?= htmlspecialchars($evento['status']) ?></span>
                        </td>
                        <td>
                            <a href="editar_evento.php?id=<?= $evento['id'] ?>" class="btn btn-sm btn-secondary">Ver Detalhes</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>