<?php
include 'includes/auth.php';
include 'includes/db.php';
include 'includes/header.php';

$utilizador_id = $_SESSION["utilizador_id"];
$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["cancelar_id"])) {
    $reserva_id = intval($_POST["cancelar_id"]);

    $stmt = $conn->prepare("SELECT id FROM reservas WHERE id = ? AND utilizador_id = ?");
    $stmt->bind_param("ii", $reserva_id, $utilizador_id);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 1) {
        $delete = $conn->prepare("DELETE FROM reservas WHERE id = ?");
        $delete->bind_param("i", $reserva_id);
        $delete->execute();

        $mensagem = "Reserva cancelada com sucesso.";
    } else {
        $mensagem = "Reserva não encontrada ou não pertence a ti.";
    }
}

$stmt = $conn->prepare("
    SELECT r.id AS reserva_id, v.origem, v.destino, v.data_partida, v.hora_partida, v.preco, r.data_reserva
    FROM reservas r
    JOIN voos v ON r.voo_id = v.id
    WHERE r.utilizador_id = ?
    ORDER BY r.data_reserva DESC
");
$stmt->bind_param("i", $utilizador_id);
$stmt->execute();
$resultado = $stmt->get_result();
?>

<h2>As Minhas Reservas</h2>

<?php if (!empty($mensagem)): ?>
    <p><?= $mensagem ?></p>
<?php endif; ?>

<?php if ($resultado->num_rows > 0): ?>
    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>Origem</th>
                <th>Destino</th>
                <th>Data do Voo</th>
                <th>Hora</th>
                <th>Preço</th>
                <th>Data da Reserva</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($reserva = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($reserva["origem"]) ?></td>
                    <td><?= htmlspecialchars($reserva["destino"]) ?></td>
                    <td><?= $reserva["data_partida"] ?></td>
                    <td><?= $reserva["hora_partida"] ?></td>
                    <td><?= number_format($reserva["preco"], 2) ?> €</td>
                    <td><?= $reserva["data_reserva"] ?></td>
                    <td>
                        <form method="POST" onsubmit="return confirm('Tens a certeza que queres cancelar esta reserva?');">
                            <input type="hidden" name="cancelar_id" value="<?= $reserva["reserva_id"] ?>">
                            <button type="submit">Cancelar</button>
                        </form>
                        <a href="gerar_bilhete_pdf.php?id=<?= $reserva['reserva_id'] ?>" target="_blank" style="margin-left:10px;">
                            <button type="button">Bilhete PDF</button>
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Não tens reservas ativas.</p>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>