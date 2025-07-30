<?php
session_start();

include 'includes/db.php';

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $senha = $_POST["senha"];

    if (empty($nome) || empty($email) || empty($senha)) {
        $mensagem = "Por favor, preenche todos os campos.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM utilizadores WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $mensagem = "Este email já está registado.";
        } else {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO utilizadores (nome, email, palavra_passe) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nome, $email, $senha_hash);

            if ($stmt->execute()) {
                header("Location: login.php");
                exit();
            } else {
                $mensagem = "Erro ao registar. Tenta novamente.";
            }
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
    <head>
        <meta charset="UTF-8">
        <title>Registo</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body>
        <div class="form-container">
            <div class="form-box">
                <h2>Registo de Utilizador</h2>

                <?php if (!empty($mensagem)): ?>
                    <p><?php echo $mensagem; ?></p>
                <?php endif; ?>

                <form method="POST" action="">
                    <label for="nome">Nome:</label><br>
                    <input type="text" id="nome" name="nome"><br><br>

                    <label for="email">Email:</label><br>
                    <input type="email" id="email" name="email"><br><br>

                    <label for="senha">Palavra-passe:</label><br>
                    <input type="password" id="senha" name="senha"><br><br>

                    <button type="submit">Registar</button>
                </form>
            </div>
        </div>
    </body>
</html>