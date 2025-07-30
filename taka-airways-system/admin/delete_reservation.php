<?php
session_start();

if (!isset($_SESSION["utilizador_tipo"]) || $_SESSION["utilizador_tipo"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: manage_reservations.php");
    exit();
}

$id_reserva = intval($_GET["id"]);

$stmt = $conn->prepare("DELETE FROM reservas WHERE id = ?");
$stmt->bind_param("i", $id_reserva);

if ($stmt->execute()) {
    header("Location: manage_reservations.php?sucesso=1");
    exit();
} else {
    header("Location: manage_reservations.php?erro=1");
    exit();
}
?>