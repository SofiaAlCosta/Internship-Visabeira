<?php
include 'includes/auth.php';
include 'includes/db.php';
include 'includes/header.php';

$utilizador_id = $_SESSION['utilizador_id'];
$stmt = $conn->prepare("SELECT * FROM tarefas WHERE utilizador_id = ? AND estado = 'Concluída' ORDER BY data_criacao DESC");
$stmt->bind_param("i", $utilizador_id);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<main class="task-wrapper">
    <h2>Histórico de Tarefas</h2>
    <?php if ($resultado->num_rows > 0): ?>
        <table class="task-table">
            <thead>
                <tr>
                    <th>Título</th>
                    <th>Descrição</th>
                    <th>Prioridade</th>
                    <th>Data Limite</th>
                    <th>Data de Criação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($tarefa = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($tarefa['titulo']) ?></td>
                        <td><?= htmlspecialchars($tarefa['descricao']) ?></td>
                        <td><?= htmlspecialchars($tarefa['prioridade']) ?></td>
                        <td><?= htmlspecialchars($tarefa['data_limite']) ?></td>
                        <td><?= htmlspecialchars($tarefa['data_criacao']) ?></td>
                        <td>
                            <a style="text-decoration: none" href="delete_task.php?id=<?= $tarefa['id'] ?>" onclick="return confirm('Tens a certeza que queres eliminar esta tarefa do histórico?')">❌</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Não há tarefas concluídas no momento.</p>
    <?php endif; ?>
</main>

<?php include 'includes/footer.php'; ?>