<?php
include 'includes/auth.php';
include 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $titulo = trim($_POST["titulo"]);
    $descricao = trim($_POST["descricao"]);
    $utilizador_id = $_SESSION["utilizador_id"];
    $data_limite = $_POST['data_limite'];

    $stmt = $conn->prepare("INSERT INTO tarefas (titulo, descricao, utilizador_id, data_limite) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssis", $titulo, $descricao, $utilizador_id, $data_limite);
    $stmt->execute();

    header("Location: my_tasks.php");
    exit();
}

include 'includes/header.php';
?>

<div class="form-container">
    <div class="form-box">
        <h2>Nova Tarefa</h2>
        <form method="post">
            <input type="text" name="titulo" placeholder="Título" required>
            <input type="text" name="descricao" placeholder="Descrição" required>
            <div class="form-group">
                <label for="prioridade">Prioridade</label>
                <select name="prioridade" id="prioridade">
                    <option value="Alta">Alta</option>
                    <option value="Média">Média</option>
                    <option value="Baixa">Baixa</option>
                </select><br>
                <label for="data_limite">Data Limite</label>
                <input type="date" name="data_limite" id="data_limite">
            </div>
            <button type="submit">Criar Tarefa</button>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>