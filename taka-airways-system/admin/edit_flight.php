<?php
session_start();

if (!isset($_SESSION["utilizador_tipo"]) || $_SESSION["utilizador_tipo"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';
include 'includes/header_admin.php';

$mensagem = "";

if (!isset($_GET["id"])) {
    header("Location: manage_flights.php");
    exit();
}

$id_voo = $_GET["id"];

$stmt = $conn->prepare("SELECT * FROM voos WHERE id = ?");
$stmt->bind_param("i", $id_voo);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows !== 1) {
    header("Location: manage_flights.php");
    exit();
}

$voo = $resultado->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $origem = $_POST["origem"];
    $destino = $_POST["destino"];
    $data_partida = $_POST["data_partida"];
    $hora_partida = $_POST["hora_partida"];
    $preco = $_POST["preco"];

    if (empty($origem) || empty($destino) || empty($data_partida) || empty($hora_partida) || empty($preco)) {
        $mensagem = "Todos os campos são obrigatórios.";
    } else {
        $stmt = $conn->prepare("UPDATE voos SET origem = ?, destino = ?, data_partida = ?, hora_partida = ?, preco = ? WHERE id = ?");
        $stmt->bind_param("ssssdi", $origem, $destino, $data_partida, $hora_partida, $preco, $id_voo);

        if ($stmt->execute()) {
            header("Location: manage_flights.php");
            exit();
        } else {
            $mensagem = "Erro ao atualizar voo.";
        }
    }
}
?>

<main>
    <h2>Editar Voo</h2>

    <?php if (!empty($mensagem)): ?>
        <p style="color: red;"><?= $mensagem ?></p>
    <?php endif; ?>

    <form method="POST">
        <label for="origem">Origem:</label>
        <input type="text" name="origem" id="origem" value="<?= htmlspecialchars($voo['origem']) ?>">

        <label for="destino">Destino:</label>
        <input type="text" name="destino" id="destino" value="<?= htmlspecialchars($voo['destino']) ?>">

        <label for="data_partida">Data de Partida:</label>
        <input type="date" name="data_partida" id="data_partida" value="<?= $voo['data_partida'] ?>">

        <label for="hora_partida">Hora de Partida:</label>
        <input type="time" name="hora_partida" id="hora_partida" value="<?= $voo['hora_partida'] ?>">

        <label for="preco">Preço (€):</label>
        <input type="number" step="0.01" name="preco" id="preco" value="<?= $voo['preco'] ?>">

        <button type="submit">Guardar Alterações</button>
    </form>
</main>

<?php include '../includes/footer.php'; ?>