<?php
include 'includes/auth.php';
include 'includes/db.php';

if (!isset($_GET['id'])) {
    die("ID inválido.");
}

$utilizador_id = $_SESSION['utilizador_id'];
$id = intval($_GET['id']);

$stmt = $conn->prepare("UPDATE tarefas SET estado = 'Concluída' WHERE id = ? AND utilizador_id = ?");
$stmt->bind_param("ii", $id, $utilizador_id);
$stmt->execute();

header("Location: my_tasks.php");
exit();
?>