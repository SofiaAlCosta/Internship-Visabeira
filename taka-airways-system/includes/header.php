<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt">
    <head>
        <meta charset="UTF-8">
        <title>Taka Airways</title>
        <link rel="stylesheet" href="/taka-airways-system/assets/css/style.css">
        <script src="assets/js/main.js" defer></script>
    </head>
    <body>
        <header>
            <div class="navbar">
                <div class="logo">
                    <a href="index.php">
                        <img src="/reserva_voos/assets/images/planelogo.png" alt="Taka Airways Logo">
                        <span class="brand-name">Taka Airways</span>
                    </a>
                </div>
                <nav>
                    <ul>
                        <li><a href="index.php">Início</a></li>
                        <li><a href="flights.php">Voos</a></li>
                        <?php if (isset($_SESSION["utilizador_id"])): ?>
                            <li><a href="my_reservations.php">Minhas Reservas</a></li>
                            <li><a href="profile.php">Perfil</a></li>
                            <li><a href="logout.php">Sair</a></li>
                        <?php else: ?>
                            <li><a href="login.php">Login</a></li>
                            <li><a href="register.php">Registar</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </header>
        <main>