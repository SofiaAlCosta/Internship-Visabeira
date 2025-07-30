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
?>

<h2>Detalhes do Voo</h2>

<ul>
    <li><strong>Origem:</strong> <?= htmlspecialchars($voo["origem"]) ?></li>
    <li><strong>Destino:</strong> <?= htmlspecialchars($voo["destino"]) ?></li>
    <li><strong>Data de Partida:</strong> <?= $voo["data_partida"] ?></li>
    <li><strong>Hora de Partida:</strong> <?= $voo["hora_partida"] ?></li>
    <li><strong>Preço:</strong> <?= number_format($voo["preco"], 2) ?> €</li>
</ul>

<p><a href="reservations.php?id=<?= $voo["id"] ?>">Reservar este voo</a></p>

<?php include 'includes/footer.php'; ?>