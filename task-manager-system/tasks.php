<?php
include 'includes/auth.php';
include 'includes/db.php';
include 'includes/header.php';

$utilizador_id = $_SESSION['utilizador_id'];

$estado = $_GET['estado'] ?? '';
$prioridade = $_GET['prioridade'] ?? '';

$sql = "SELECT * FROM tarefas WHERE utilizador_id = ?";
$params = [$utilizador_id];
$types = "i";

if ($estado !== '') {
    $sql .= " AND estado = ?";
    $types .= "s";
    $params[] = $estado;
}

if ($prioridade !== '') {
    $sql .= " AND prioridade = ?";
    $types .= "s";
    $params[] = $prioridade;
}

$sql .= " ORDER BY data_criacao DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<h2>Todas as Tarefas</h2>

<form method="get">
    <label>Estado:</label>
    <select name="estado">
        <option value="">Todos</option>
        <option value="pendente" <?= $estado === 'pendente' ? 'selected' : '' ?>>Pendente</option>
        <option value="concluida" <?= $estado === 'concluida' ? 'selected' : '' ?>>Concluída</option>
    </select>

    <label>Prioridade:</label>
    <select name="prioridade">
        <option value="">Todas</option>
        <option value="baixa" <?= $prioridade === 'baixa' ? 'selected' : '' ?>>Baixa</option>
        <option value="media" <?= $prioridade === 'media' ? 'selected' : '' ?>>Média</option>
        <option value="alta" <?= $prioridade === 'alta' ? 'selected' : '' ?>>Alta</option>
    </select>

    <button type="submit">Filtrar</button>
</form>

<?php if ($resultado->num_rows > 0): ?>
    <table>
        <tr>
            <th>Título</th>
            <th>Descrição</th>
            <th>Prioridade</th>
            <th>Estado</th>
            <th>Ações</th>
        </tr>
        <?php while ($tarefa = $resultado->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($tarefa['titulo']) ?></td>
            <td><?= htmlspecialchars($tarefa['descricao']) ?></td>
            <td><?= $tarefa['prioridade'] ?? '-' ?></td>
            <td><?= $tarefa['estado'] ?></td>
            <td>
                <?php if ($tarefa['estado'] === 'pendente'): ?>
                    <a href="complete_task.php?id=<?= $tarefa['id'] ?>">Concluir</a> |
                <?php endif; ?>
                <a href="edit_task.php?id=<?= $tarefa['id'] ?>">Editar</a> |
                <a href="delete_task.php?id=<?= $tarefa['id'] ?>" onclick="return confirm('Tens a certeza?')">Apagar</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
<?php else: ?>
    <p>Sem tarefas para mostrar.</p>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>