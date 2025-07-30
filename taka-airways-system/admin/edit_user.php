<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION["utilizador_tipo"]) || $_SESSION["utilizador_tipo"] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$mensagem = "";

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: manage_users.php");
    exit();
}

$id = intval($_GET["id"]);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = $_POST["nome"];
    $email = $_POST["email"];
    $tipo = $_POST["tipo"];

    if (empty($nome) || empty($email) || empty($tipo)) {
        $mensagem = "Preenche todos os campos.";
    } else {
        $stmt = $conn->prepare("UPDATE utilizadores SET nome = ?, email = ?, tipo = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nome, $email, $tipo, $id);

        if ($stmt->execute()) {
            header("Location: manage_users.php");
            exit();
        } else {
            $mensagem = "Erro ao atualizar utilizador.";
        }
    }
}

$stmt = $conn->prepare("SELECT nome, email, tipo FROM utilizadores WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows !== 1) {
    header("Location: manage_users.php");
    exit();
}

$utilizador = $resultado->fetch_assoc();
?>

<?php include 'includes/header_admin.php'; ?>

<main>
    <h2>Editar Utilizador</h2>

    <?php if ($mensagem): ?>
        <p><?= $mensagem ?></p>
    <?php endif; ?>
    <div style="width: 300px;">
        <form method="POST">
            <label>Nome:</label><br>
            <input type="text" name="nome" value="<?= htmlspecialchars($utilizador["nome"]) ?>"><br><br>

            <label>Email:</label><br>
            <input type="email" name="email" value="<?= htmlspecialchars($utilizador["email"]) ?>"><br><br>

            <label>Tipo:</label><br>
            <select name="tipo">
                <option value="cliente" <?= $utilizador["tipo"] === 'cliente' ? 'selected' : '' ?>>Cliente</option>
                <option value="admin" <?= $utilizador["tipo"] === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select><br><br>

            <button type="submit">Guardar Alterações</button>
        </form>
    </div>
</main>

<?php include '../includes/footer.php'; ?>