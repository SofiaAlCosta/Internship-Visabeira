<?php
include 'includes/auth.php';
include 'includes/db.php';
include 'includes/header.php';

$utilizador_id = $_SESSION['utilizador_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);

    if ($nome && $email) {
        $stmt = $conn->prepare("UPDATE utilizadores SET nome = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nome, $email, $utilizador_id);
        $stmt->execute();
        $msg = "Perfil atualizado com sucesso.";
    } else {
        $msg = "Preenche todos os campos.";
    }
}

$stmt = $conn->prepare("SELECT nome, email FROM utilizadores WHERE id = ?");
$stmt->bind_param("i", $utilizador_id);
$stmt->execute();
$resultado = $stmt->get_result();
$utilizador = $resultado->fetch_assoc();
?>

<h2>O Meu Perfil</h2>

<?php if (isset($msg)): ?>
    <p style="color: green;"><?= $msg ?></p>
<?php endif; ?>

<form method="post">
    <label>Nome:</label>
    <input type="text" name="nome" required value="<?= htmlspecialchars($utilizador['nome']) ?>">

    <label>Email:</label>
    <input type="email" name="email" required value="<?= htmlspecialchars($utilizador['email']) ?>">

    <button type="submit">Atualizar Perfil</button>
</form>

<hr>

<form method="post" action="delete_account.php" onsubmit="return confirm('Tens a certeza que queres apagar a tua conta? Esta ação é irreversível!');">
    <button type="submit" style="background-color: red;">Apagar Conta</button>
</form>

<?php include 'includes/footer.php'; ?>