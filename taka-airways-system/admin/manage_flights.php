<?php
session_start();

if (!isset($_SESSION["utilizador_tipo"]) || $_SESSION["utilizador_tipo"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';
include 'includes/header_admin.php';

$voos = $conn->query("SELECT * FROM voos ORDER BY data_partida, hora_partida");
?>

<main>
    <h2>Gestão de Voos</h2>
    <p>Lista de voos registados no sistema:</p>

    <a href="add_flight.php" style="display:inline-block; margin: 15px 0; background:#0a9396; color:white; padding:8px 16px; border-radius:6px; text-decoration:none;">+ Adicionar Voo</a>

    <table>
        <tr>
            <th>Origem</th>
            <th>Destino</th>
            <th>Data</th>
            <th>Hora</th>
            <th>Preço (€)</th>
            <th>Ações</th>
        </tr>
        <?php while ($voo = $voos->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($voo["origem"]) ?></td>
                <td><?= htmlspecialchars($voo["destino"]) ?></td>
                <td><?= htmlspecialchars($voo["data_partida"]) ?></td>
                <td><?= htmlspecialchars($voo["hora_partida"]) ?></td>
                <td><?= number_format($voo["preco"], 2, ',', '.') ?></td>
                <td>
                    <a href="edit_flight.php?id=<?= $voo["id"] ?>">Editar</a> |
                    <a href="delete_flight.php?id=<?= $voo["id"] ?>" onclick="return confirm('Tens a certeza que queres eliminar este voo?');">Eliminar</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>
</main>

<?php include '../includes/footer.php'; ?>