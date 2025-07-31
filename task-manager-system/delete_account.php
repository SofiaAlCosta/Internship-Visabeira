<?php
include 'includes/auth.php';
include 'includes/db.php';

$utilizador_id = $_SESSION['utilizador_id'];

$conn->prepare("DELETE FROM tarefas WHERE utilizador_id = ?")->execute([$utilizador_id]);

$stmt = $conn->prepare("DELETE FROM utilizadores WHERE id = ?");
$stmt->bind_param("i", $utilizador_id);
$stmt->execute();

session_destroy();
header("Location: register.php");
exit();
?>