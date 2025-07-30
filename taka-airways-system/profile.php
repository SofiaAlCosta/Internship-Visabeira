<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION["utilizador_id"])) {
    header("Location: login.php");
    exit();
}

$mensagem = "";
$utilizador_id = $_SESSION["utilizador_id"];

$stmt = $conn->prepare("SELECT nome, email FROM utilizadores WHERE id = ?");
$stmt->bind_param("i", $utilizador_id);
$stmt->execute();
$resultado = $stmt->get_result();
$utilizador = $resultado->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $novo_nome = $_POST["nome"];
    $novo_email = $_POST["email"];
    $nova_senha = $_POST["nova_senha"];
    $confirmar_senha = $_POST["confirmar_senha"];

    if (empty($novo_nome) || empty($novo_email)) {
        $mensagem = "Nome e email são obrigatórios.";
    } else {
        $stmt = $conn->prepare("UPDATE utilizadores SET nome = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $novo_nome, $novo_email, $utilizador_id);
        $stmt->execute();
        $stmt->close();

        if (!empty($nova_senha)) {
            if ($nova_senha === $confirmar_senha) {
                $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE utilizadores SET palavra_passe = ? WHERE id = ?");
                $stmt->bind_param("si", $senha_hash, $utilizador_id);
                $stmt->execute();
                $stmt->close();
            } else {
                $mensagem = "As palavras-passe não coincidem.";
            }
        }

        if (empty($mensagem)) {
            $mensagem = "Perfil atualizado com sucesso!";
            $_SESSION["utilizador_nome"] = $novo_nome;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt">
    <head>
        <meta charset="UTF-8">
        <title>Editar Perfil</title>
        <link rel="stylesheet" href="assets/css/style.css">
    </head>
    <body>
        <?php include 'includes/header.php'; ?>

        <main>
            <h2>Editar Perfil</h2>

            <?php if (!empty($mensagem)): ?>
                <p><?php echo $mensagem; ?></p>
            <?php endif; ?>

            <form method="POST" action="">
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($utilizador['nome']); ?>" required>

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($utilizador['email']); ?>" required>

                <label for="nova_senha">Nova Palavra-passe:</label>
                <input type="password" id="nova_senha" name="nova_senha">

                <label for="confirmar_senha">Confirmar Nova Palavra-passe:</label>
                <input type="password" id="confirmar_senha" name="confirmar_senha">

                <button type="submit">Guardar Alterações</button>
            </form>
        </main>

        <?php include 'includes/footer.php'; ?>
    </body>
</html>