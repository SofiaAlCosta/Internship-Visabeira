<?php
session_start();

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $utilizador = $_POST['utilizador'];
    $senha = $_POST['senha'];

    if ($utilizador === 'admin' && $senha === '1234') {
        $_SESSION['admin'] = true;
        header('Location: ../index.php');
        exit;
    } else {
        $erro = 'Credenciais inválidas';
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
    <head>
        <meta charset="UTF-8">
        <title>Login de Administrador</title>
        <link rel="stylesheet" href="../assets/css/style.css">
    </head>
    <body>
        <h1 style="text-align: center; margin-top: 10%;">🔐 Login de Administrador</h1>

        <?php if ($erro): ?>
            <p style="color: red;"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="text" name="utilizador" placeholder="Utilizador" required>
            <input type="password" name="senha" placeholder="Senha" required><br>
            <button type="submit">Entrar</button>
        </form>

        <p style="margin-left: 35%;"><a href="../index.php">← Voltar</a></p>
    </body>
</html>