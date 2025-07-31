<?php
include 'includes/auth.php';
include 'includes/db.php';

if (!isset($_GET['id'])) {
    die("ID inválido.");
}

$utilizador_id = $_SESSION['utilizador_id'];
$id = intval($_GET['id']);

$stmt = $conn->prepare("DELETE FROM tarefas WHERE id = ? AND utilizador_id = ?");
$stmt->bind_param("ii", $id, $utilizador_id);
$stmt->execute();

$redirect = $_SERVER['HTTP_REFERER'] ?? 'my_tasks.php';
header("Location: $redirect");

exit();