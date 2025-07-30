<?php
session_start();

if (!isset($_SESSION["utilizador_tipo"]) || $_SESSION["utilizador_tipo"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';
include 'includes/header_admin.php';

$sql = "
    SELECT r.id, r.data_reserva,
           u.nome AS nome_utilizador,
           v.origem, v.destino, v.data_partida, v.hora_partida
    FROM reservas r
    JOIN utilizadores u ON r.utilizador_id = u.id
    JOIN voos v ON r.voo_id = v.id
    ORDER BY r.data_reserva DESC
";

$reservas = $conn->query($sql);
?>

<main>
    <h2>Gestão de Reservas</h2>
    <table>
        <thead>
            <tr>
                <th>Utilizador</th>
                <th>Origem</th>
                <th>Destino</th>
                <th>Data do Voo</th>
                <th>Hora do Voo</th>
                <th>Data da Reserva</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($r = $reservas->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($r["nome_utilizador"]) ?></td>
                    <td><?= htmlspecialchars($r["origem"]) ?></td>
                    <td><?= htmlspecialchars($r["destino"]) ?></td>
                    <td><?= $r["data_partida"] ?></td>
                    <td><?= $r["hora_partida"] ?></td>
                    <td><?= $r["data_reserva"] ?></td>
                    <td>
                        <a href="delete_reservation.php?id=<?= $r["id"] ?>" onclick="return confirm('Eliminar esta reserva?');">Eliminar</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table><br>
    <a href="export_reservations_pdf.php.php" class="btn-exportar" target="_blank">📄 Exportar para PDF</a>
</main>

<?php include '../includes/footer.php'; ?>