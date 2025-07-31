<?php
session_start();
require_once 'includes/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, password FROM utilizadores WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $utilizador = $resultado->fetch_assoc();
        if (password_verify($password, $utilizador["password"])) {
            $_SESSION["utilizador_id"] = $utilizador["id"];
            header("Location: my_tasks.php");
            exit();
        } else {
            $erro = "Credenciais inválidas.";
        }
    } else {
        $erro = "Utilizador não encontrado.";
    }
}
include 'includes/header.php';
?>

<div class="form-container" style="width: 500px">
    <div class="form-box">
        <h2>Iniciar Sessão</h2>
        <?php if (isset($erro)) echo "<p style='color: red;'>$erro</p>"; ?>
        <form method="post">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Palavra-passe" required>
            <button type="submit">Entrar</button>
            <p style="margin-top: 10px; text-align: center;">Não tem conta? <a href="register.php">Registe-se aqui</a></p>
        </form>
    </div>
</div>

<?php include 'includes/footer.php'; ?>