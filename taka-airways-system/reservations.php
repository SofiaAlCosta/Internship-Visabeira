<?php
include 'includes/auth.php';
include 'includes/db.php';
include 'includes/header.php';

if (!isset($_GET["id"])) {
    echo "<p>ID de voo não fornecido.</p>";
    include 'includes/footer.php';
    exit();
}

$voo_id = intval($_GET["id"]);
$utilizador_id = $_SESSION["utilizador_id"];

$stmt = $conn->prepare("SELECT * FROM voos WHERE id = ?");
$stmt->bind_param("i", $voo_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows != 1) {
    echo "<p>Voo não encontrado.</p>";
    include 'includes/footer.php';
    exit();
}

$voo = $resultado->fetch_assoc();
$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $conn->prepare("INSERT INTO reservas (utilizador_id, voo_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $utilizador_id, $voo_id);

    if ($stmt->execute()) {
        $mensagem = "Reserva efetuada com sucesso!";
    } else {
        $mensagem = "Erro ao efetuar a reserva.";
    }
    $stmt->close();
}
?>

<div style="width: 300px;">
    <h2>Reserva do Voo</h2>

    <p><strong>Origem:</strong> <?= htmlspecialchars($voo["origem"]) ?></p>
    <p><strong>Destino:</strong> <?= htmlspecialchars($voo["destino"]) ?></p>
    <p><strong>Data:</strong> <?= $voo["data_partida"] ?></p>
    <p><strong>Hora:</strong> <?= $voo["hora_partida"] ?></p>
    <p><strong>Preço:</strong> <?= number_format($voo["preco"], 2) ?> €</p>

    <?php if (!empty($mensagem)): ?>
        <p><?= $mensagem ?></p>
    <?php else: ?>
        <form method="POST">
            <button type="submit">Confirmar Reserva</button>
        </form>
    <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>