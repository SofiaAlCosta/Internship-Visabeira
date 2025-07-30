<?php
session_start();
include 'includes/header.php';
?>
<main class="homepage">
    <h1>Bem-vindo ao sistema de Reserva de Voos</h1>

    <?php if (isset($_SESSION["utilizador_id"])): ?>
        <p>Olá, <strong><?= htmlspecialchars($_SESSION["utilizador_nome"]) ?></strong>! 👋</p>
        <p><a href="voos.php">Ver voos disponíveis</a></p>
    <?php else: ?>
        <p>Para começar, inicia sessão ou cria uma conta:</p>
        <ul style="list-style-type:none;">
            <li><a href="login.php">Iniciar Sessão</a></li>
            <li><a href="register.php">Criar Conta</a></li>
        </ul>
    <?php endif; ?>
</main>
<?php include 'includes/footer.php'; ?>