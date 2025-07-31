<?php
session_start();
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {

    header('Location: admin/login.php');
    exit;
}

include 'includes/db.php';

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];

    $stmt = $conn->prepare("DELETE FROM mensagens WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

header('Location: index.php');
exit;
?>