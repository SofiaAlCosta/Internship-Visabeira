<?php
session_start();

if (!isset($_SESSION["utilizador_tipo"]) || $_SESSION["utilizador_tipo"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';

if (!isset($_GET["id"])) {
    header("Location: manage_flights.php");
    exit();
}

$id_voo = $_GET["id"];

$stmt = $conn->prepare("DELETE FROM voos WHERE id = ?");
$stmt->bind_param("i", $id_voo);

if ($stmt->execute()) {
    header("Location: manage_flights.php?sucesso=1");
    exit();
} else {
    header("Location: manage_flights.php?erro=1");
    exit();
}
?>