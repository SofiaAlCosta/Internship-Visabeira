<?php
include 'includes/auth.php';
include 'includes/db.php';
include 'includes/header.php';

$filtros = [];
$parametros = [];

$sql = "SELECT * FROM voos WHERE 1=1";

if (!empty($_GET['origem'])) {
    $sql .= " AND origem LIKE ?";
    $filtros[] = "%" . $_GET['origem'] . "%";
}

if (!empty($_GET['destino'])) {
    $sql .= " AND destino LIKE ?";
    $filtros[] = "%" . $_GET['destino'] . "%";
}

if (!empty($_GET['data_min'])) {
    $sql .= " AND data_partida >= ?";
    $filtros[] = $_GET['data_min'];
}

if (!empty($_GET['data_max'])) {
    $sql .= " AND data_partida <= ?";
    $filtros[] = $_GET['data_max'];
}

if (!empty($_GET['preco_max'])) {
    $sql .= " AND preco <= ?";
    $filtros[] = $_GET['preco_max'];
}

$sql .= " ORDER BY data_partida, hora_partida";

$stmt = $conn->prepare($sql);
if ($filtros) {
    $tipos = str_repeat("s", count($filtros));
    $stmt->bind_param($tipos, ...$filtros);
}
$stmt->execute();
$resultado = $stmt->get_result();
?>

<h2>Lista de Voos Disponíveis</h2>

<form method="get" style="display:flex; gap:10px; width:760px">
    <input type="text" name="origem" placeholder="Origem" value="<?= htmlspecialchars($_GET['origem'] ?? '') ?>">
    <input type="text" name="destino" placeholder="Destino" value="<?= htmlspecialchars($_GET['destino'] ?? '') ?>">
    <input type="date" name="data_min" value="<?= htmlspecialchars($_GET['data_min'] ?? '') ?>">
    <input type="date" name="data_max" value="<?= htmlspecialchars($_GET['data_max'] ?? '') ?>">
    <input type="number" name="preco_max" placeholder="Preço máx (€)" step="0.01" value="<?= htmlspecialchars($_GET['preco_max'] ?? '') ?>">
    <button type="submit">Filtrar</button>
    <a href="voos.php" style="margin-left: 10px;">Limpar</a>
</form>

<?php if ($resultado->num_rows > 0): ?>
    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>Origem</th>
                <th>Destino</th>
                <th>Data</th>
                <th>Hora</th>
                <th>Preço</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($voo = $resultado->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($voo["origem"]) ?></td>
                    <td><?= htmlspecialchars($voo["destino"]) ?></td>
                    <td><?= $voo["data_partida"] ?></td>
                    <td><?= $voo["hora_partida"] ?></td>
                    <td><?= number_format($voo["preco"], 2) ?> €</td>
                    <td><a href="reservations.php?id=<?= $voo["id"] ?>">Reservar</a></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Não há voos disponíveis com os filtros aplicados.</p>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>