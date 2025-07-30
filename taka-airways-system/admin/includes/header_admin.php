<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["utilizador_tipo"]) || $_SESSION["utilizador_tipo"] !== "admin") {
    header("Location: ../index.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt">
    <head>
        <meta charset="UTF-8">
        <title>Administração</title>
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>
    <body>

    <header>
        <nav>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="gerir_utilizadores.php">Utilizadores</a></li>
                <li><a href="gerir_voos.php">Voos</a></li>
                <li><a href="gerir_reservas.php">Reservas</a></li>
                <li><a href="../logout.php">Sair</a></li>
            </ul>
        </nav>
    </header>

<main>