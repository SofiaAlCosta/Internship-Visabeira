<?php

?>
<!DOCTYPE html>
<html lang="pt">
    <head>
        <meta charset="UTF-8">
        <title>Livro de Visitas</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body>
        <header style="margin-top: 30px;">
            <h1>📖 Livro de Visitas</h1>
            <?php if (isset($_SESSION['admin']) && $_SESSION['admin'] === true): ?>
            <p><a href="admin/logout.php">Terminar sessão</a></p>
            <?php endif; ?>
        </header>
    <main>