<?php
require_once 'includes/auth.php';
require_once 'includes/db.php';
require_once 'includes/header.php';

$utilizador_id = $_SESSION['utilizador_id'];

$sql = "SELECT * FROM tarefas WHERE utilizador_id = ? AND estado = 'Pendente'";
$params = [$utilizador_id];
$types = "i";

if (!empty($_GET['prioridade'])) {
    $sql .= " AND prioridade = ?";
    $params[] = $_GET['prioridade'];
    $types .= "s";
}

if (!empty($_GET['data_inicio'])) {
    $sql .= " AND data_limite >= ?";
    $params[] = $_GET['data_inicio'];
    $types .= "s";
}
if (!empty($_GET['data_fim'])) {
    $sql .= " AND data_limite <= ?";
    $params[] = $_GET['data_fim'];
    $types .= "s";
}

$sql .= " ORDER BY data_criacao DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<main>
    <div class="task-wrapper">
        <h2>As Minhas Tarefas</h2>
        <a href="add_task.php" class="btn-exportar">+ Nova Tarefa</a>

        <form method="GET" action="my_tasks.php" style="margin: 20px 0; padding: 15px; background: #f9f9f9; border-radius: 8px;">
            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <select name="prioridade" style="padding: 8px; border-radius: 4px; margin-top: 10px">
                    <option value="">Todas as prioridades</option>
                    <option value="Alta" <?= ($_GET['prioridade'] ?? '') === 'Alta' ? 'selected' : '' ?>>Alta</option>
                    <option value="Média" <?= ($_GET['prioridade'] ?? '') === 'Média' ? 'selected' : '' ?>>Média</option>
                    <option value="Baixa" <?= ($_GET['prioridade'] ?? '') === 'Baixa' ? 'selected' : '' ?>>Baixa</option>
                </select>

                <label>De:
                    <input type="date" name="data_inicio" value="<?= htmlspecialchars($_GET['data_inicio'] ?? '') ?>" style="padding: 8px; border-radius: 4px;">
                </label>
                <label>Até:
                    <input type="date" name="data_fim" value="<?= htmlspecialchars($_GET['data_fim'] ?? '') ?>" style="padding: 8px; border-radius: 4px;">
                </label>

                <button type="submit" style="padding: 8px 12px; background-color: #007bff; color: white; border: none; border-radius: 4px;">Filtrar</button>
                <a href="my_tasks.php" style="padding: 8px 12px; background-color: #6c757d; color: white; text-decoration: none; border-radius: 4px;">Limpar</a>
            </div>
        </form>

        <?php if ($result->num_rows > 0): ?>
        <table class="task-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background-color: #f0f0f0;">
                    <th style="padding: 10px; text-align: left;">Título</th>
                    <th style="padding: 10px; text-align: left;">Prioridade</th>
                    <th style="padding: 10px; text-align: left;">Estado</th>
                    <th style="padding: 10px; text-align: left;">Data Limite</th>
                    <th style="padding: 10px; text-align: left;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                <tr style="border-bottom: 1px solid #ccc;">
                    <td style="padding: 8px;"><a style="text-decoration: none; color: black;" href="subtasks.php?task_id=<?= $row['id'] ?>"><?= htmlspecialchars($row['titulo']) ?></a></td>
                    <td style="padding: 8px;"><?= htmlspecialchars($row['prioridade']) ?></td>
                    <td style="padding: 8px;"><?= htmlspecialchars($row['estado']) ?></td>
                    <td style="padding: 8px;"><?= htmlspecialchars($row['data_limite']) ?></td>
                    <td style="padding: 8px;">
                        <a href="edit_task.php?id=<?= $row['id'] ?>">Editar</a> |
                        <a href="complete_task.php?id=<?= $row['id'] ?>">Concluir</a> |
                        <a href="delete_task.php?id=<?= $row['id'] ?>" onclick="return confirm('Tens a certeza?')">Eliminar</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>Não foram encontradas tarefas.</p>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>