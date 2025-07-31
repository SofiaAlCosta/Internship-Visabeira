<?php
include 'includes/auth.php';
include 'includes/db.php';

if (!isset($_GET['id'])) {
    die("ID inválido.");
}

$utilizador_id = $_SESSION['utilizador_id'];
$id = intval($_GET['id']);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titulo = trim($_POST["titulo"]);
    $descricao = trim($_POST["descricao"]);
    $prioridade = $_POST['prioridade'];
    $data_limite = $_POST['data_limite'];

    $stmt = $conn->prepare("UPDATE tarefas SET titulo = ?, descricao = ?, prioridade = ?, data_limite = ? WHERE id = ? AND utilizador_id = ?");
    $stmt->bind_param("ssssii", $titulo, $descricao, $prioridade, $data_limite, $id, $utilizador_id);
    $stmt->execute();

    header("Location: my_tasks.php");
    exit();
}

$stmt = $conn->prepare("SELECT * FROM tarefas WHERE id = ? AND utilizador_id = ?");
$stmt->bind_param("ii", $id, $utilizador_id);
$stmt->execute();
$tarefa = $stmt->get_result()->fetch_assoc();

include 'includes/header.php';
?>

<div class="form-container">
    <div class="form-box">
        <h2>Editar Tarefa</h2>
        <form method="post">
            <input type="text" name="titulo" value="<?= htmlspecialchars($tarefa['titulo']) ?>" required>
            <input type="text" name="descricao" value="<?= htmlspecialchars($tarefa['descricao']) ?>" required>
            <div class="form-group">
                <label for="prioridade">Prioridade</label>
                <select name="prioridade" id="prioridade">
                    <option value="Alta" <?= $tarefa['prioridade'] === 'Alta' ? 'selected' : '' ?>>Alta</option>
                    <option value="Média" <?= $tarefa['prioridade'] === 'Média' ? 'selected' : '' ?>>Média</option>
                    <option value="Baixa" <?= $tarefa['prioridade'] === 'Baixa' ? 'selected' : '' ?>>Baixa</option>
                </select><br>
                <label for="data_limite">Data Limite</label>
                <input type="date" name="data_limite" id="data_limite" value="<?= htmlspecialchars($tarefa['data_limite']) ?>">
            </div>
            <button type="submit">Guardar Alterações</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>