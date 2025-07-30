<?php
session_start();

if (!isset($_SESSION["utilizador_tipo"]) || $_SESSION["utilizador_tipo"] !== "admin") {
    header("Location: ../login.php");
    exit();
}

include '../includes/db.php';
include 'includes/header_admin.php';

$mensagem = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $origem = $_POST["origem"];
    $destino = $_POST["destino"];
    $data_partida = $_POST["data_partida"];
    $hora_partida = $_POST["hora_partida"];
    $preco = $_POST["preco"];

    if (empty($origem) || empty($destino) || empty($data_partida) || empty($hora_partida) || empty($preco)) {
        $mensagem = "Preenche todos os campos.";
    } else {
        $stmt = $conn->prepare("INSERT INTO voos (origem, destino, data_partida, hora_partida, preco) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssd", $origem, $destino, $data_partida, $hora_partida, $preco);

        if ($stmt->execute()) {
            header("Location: manage_flights.php");
            exit();
        } else {
            $mensagem = "Erro ao adicionar voo.";
        }

        $stmt->close();
    }
}
?>

<main>
    <h2>Adicionar Novo Voo</h2>

    <?php if (!empty($mensagem)): ?>
        <p style="color: red;"><?= $mensagem ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="origem">Origem:</label>
        <input type="text" name="origem" id="origem">

        <label for="destino">Destino:</label>
        <input type="text" name="destino" id="destino">

        <label for="data_partida">Data de Partida:</label>
        <input type="date" name="data_partida" id="data_partida">

        <label for="hora_partida">Hora de Partida:</label>
        <input type="time" name="hora_partida" id="hora_partida">

        <label for="preco">Preço (€):</label>
        <input type="number" step="0.01" name="preco" id="preco">

        <button type="submit">Adicionar Voo</button>
    </form>
</main>

<?php include '../includes/footer.php'; ?>