<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt">
    <head>
        <meta charset="UTF-8">
        <title>Gestor de Tarefas</title>
        <link rel="stylesheet" href="/task-manager-system/assets/css/style.css">
    </head>
    <body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <span class="brand-name">
                    <a href="index.php" style="color: white; text-decoration: none;">
                        <img src="assets/images/logotask.png" alt="Logo" style="height: 40px; vertical-align: middle; margin-right: 8px;">
                        TaskManager
                    </a>
                </span>
            </div>
            <ul>
                <li><a href="index.php">Início</a></li>
                <?php if (isset($_SESSION['utilizador_id'])): ?>
                    <li><a href="my_tasks.php">Minhas Tarefas</a></li>
                    <li><a href="task_history.php">Histórico</a></li>
                    <li><a href="profile.php">Perfil</a></li>
                    <li><a href="logout.php">Logout</a></li>
                <?php else: ?>
                    <li><a href="login.php">Login</a></li>
                    <li><a href="register.php">Registar</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
<main>