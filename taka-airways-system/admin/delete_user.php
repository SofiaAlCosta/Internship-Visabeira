<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION["utilizador_tipo"]) || $_SESSION["utilizador_tipo"] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: manage_users.php");
    exit();
}

$id = intval($_GET["id"]);

if ($_SESSION["utilizador_id"] == $id) {
    header("Location: manage_users.php");
    exit();
}

$stmt = $conn->prepare("DELETE FROM utilizadores WHERE id = ?");
$stmt->bind_param("i", $id);

$stmt->execute();

header("Location: manage_users.php");
exit();
?>