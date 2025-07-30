<?php
session_start();

include 'includes/db.php';

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST["email"];
    $senha = $_POST["senha"];

    if (empty($email) || empty($senha)) {
        $mensagem = "Preenche todos os campos.";
    } else {
        $stmt = $conn->prepare("SELECT id, nome, palavra_passe, tipo FROM utilizadores WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows == 1) {
            $utilizador = $resultado->fetch_assoc();

            if (password_verify($senha, $utilizador["palavra_passe"])) {
                $_SESSION["utilizador_id"] = $utilizador["id"];
                $_SESSION["utilizador_nome"] = $utilizador["nome"];
                $_SESSION["utilizador_tipo"] = $utilizador["tipo"];

                if ($utilizador["tipo"] === "admin") {
                    header("Location: admin/dashboard.php");
                } else {
                    header("Location: flights.php");
                }
                exit();
            } else {
                $mensagem = "Palavra-passe incorreta.";
            }
        } else {
            $mensagem = "Email não encontrado.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
    <head>
        <meta charset="UTF-8">
        <title>Login</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body>
        <div class="form-container">
            <div class="form-box">
                <h2>Iniciar Sessão</h2>

                <?php if (!empty($mensagem)): ?>
                    <p><?php echo $mensagem; ?></p>
                <?php endif; ?>

                <form method="POST" action="">
                    <label for="email">Email:</label><br>
                    <input type="email" id="email" name="email"><br><br>

                    <label for="senha">Palavra-passe:</label><br>
                    <input type="password" id="senha" name="senha"><br><br>

                    <button type="submit">Entrar</button>
                </form>
            </div>
        </div>
    </body>
</html>