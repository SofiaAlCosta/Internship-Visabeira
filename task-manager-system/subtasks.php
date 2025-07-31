<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

if (!isset($_GET['task_id']) || empty($_GET['task_id'])) {
    header('Location: my_tasks.php');
    exit;
}

$task_id = intval($_GET['task_id']);
$utilizador_id = $_SESSION['utilizador_id'];

$stmt = $conn->prepare("SELECT * FROM tarefas WHERE id = ? AND utilizador_id = ?");
$stmt->bind_param("ii", $task_id, $utilizador_id);
$stmt->execute();
$result = $stmt->get_result();
$tarefa = $result->fetch_assoc();

if (!$tarefa) {
    echo "<p>Tarefa não encontrada.</p>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nome'])) {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);
    if (!empty($nome)) {
        $stmt = $conn->prepare("INSERT INTO subtarefas (tarefa_id, nome, descricao) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $task_id, $nome, $descricao);
        $stmt->execute();
        header("Location: subtasks.php?task_id=$task_id");
        exit;
    }
}

if (isset($_GET['complete'])) {
    $sub_id = intval($_GET['complete']);
    $stmt = $conn->prepare("UPDATE subtarefas SET estado = 'Concluída' WHERE id = ? AND tarefa_id = ?");
    $stmt->bind_param("ii", $sub_id, $task_id);
    $stmt->execute();
    header("Location: subtasks.php?task_id=$task_id");
    exit;
}

if (isset($_GET['uncomplete'])) {
    $sub_id = intval($_GET['uncomplete']);
    $stmt = $conn->prepare("UPDATE subtarefas SET estado = 'Pendente' WHERE id = ? AND tarefa_id = ?");
    $stmt->bind_param("ii", $sub_id, $task_id);
    $stmt->execute();
    header("Location: subtasks.php?task_id=$task_id");
    exit;
}

if (isset($_GET['delete'])) {
    $sub_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM subtarefas WHERE id = ? AND tarefa_id = ?");
    $stmt->bind_param("ii", $sub_id, $task_id);
    $stmt->execute();
    header("Location: subtasks.php?task_id=$task_id");
    exit;
}

$stmt = $conn->prepare("SELECT * FROM subtarefas WHERE tarefa_id = ? ORDER BY data_criacao DESC");
$stmt->bind_param("i", $task_id);
$stmt->execute();
$subtarefas = $stmt->get_result();
?>

<?php include 'includes/header.php'; ?>

<main class="task-wrapper">
    <h2><?= htmlspecialchars($tarefa['titulo']) ?></h2><br>
    <p><?= nl2br(htmlspecialchars($tarefa['descricao'])) ?></p><br>
    <hr><br>

    <?php
    $total = $subtarefas->num_rows;
    $concluidas = 0;
    $subtarefas->data_seek(0);

    while ($sub = $subtarefas->fetch_assoc()) {
        if ($sub['estado'] === 'Concluída') {
            $concluidas++;
        }
    }
    $percentagem = $total > 0 ? round(($concluidas / $total) * 100) : 0;
    $subtarefas->data_seek(0);
    ?>

    <div style="margin-bottom: 20px; padding: 12px; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 6px;">
        <strong>Progresso:</strong>
        <?= $concluidas ?> de <?= $total ?> subtarefas concluídas (<?= $percentagem ?>%)

        <div style="margin-top: 10px; background-color: #e9ecef; border-radius: 5px; height: 20px; width: 100%;">
            <div style="height: 100%; width: <?= $percentagem ?>%; background-color: #28a745; border-radius: 5px; text-align: center; color: white; font-size: 12px;">
                <?= $percentagem ?>%
            </div>
        </div>
    </div>

    <h3>Subtarefas</h3>
    <form method="post" style="margin-bottom: 20px;">
        <input type="text" name="nome" placeholder="Nome da subtarefa" required>
        <input type="text" name="descricao" placeholder="Descrição (opcional)">
        <button type="submit">Adicionar</button>
    </form>

    <table class="task-table" style="width: 1000px; border-collapse: collapse;">
        <thead>
            <tr style="background-color: #f0f0f0;">
                <th style="padding: 10px;">Nome</th>
                <th style="padding: 10px;">Descrição</th>
                <th style="padding: 10px;">Estado</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($sub = $subtarefas->fetch_assoc()): ?>
            <tr style="border-bottom: 1px solid #ccc;">
                <td style="padding: 8px;"><?= htmlspecialchars($sub['nome']) ?></td>
                <td style="padding: 8px;"><?= htmlspecialchars($sub['descricao']) ?></td>
                <td style="padding: 8px;">
                    <?php if ($sub['estado'] === 'Concluída'): ?>
                        ✅
                        <a href="?task_id=<?= $task_id ?>&uncomplete=<?= $sub['id'] ?>" style="margin-left: 8px;">❌</a>
                    <?php else: ?>
                        <a style="text-decoration: none;" href="?task_id=<?= $task_id ?>&complete=<?= $sub['id'] ?>">✔️</a>
                        <a style="text-decoration: none;" href="?task_id=<?= $task_id ?>&delete=<?= $sub['id'] ?>" onclick="return confirm('Eliminar subtarefa?')">❌</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</main>

<?php include 'includes/footer.php'; ?>