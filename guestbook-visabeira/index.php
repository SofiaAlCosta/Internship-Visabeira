<?php
session_start();
include 'includes/db.php';
include 'includes/header.php';
?>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $mensagem = $_POST['mensagem'];

    $stmt = $conn->prepare("INSERT INTO mensagens (nome, mensagem) VALUES (?, ?)");
    $stmt->bind_param("ss", $nome, $mensagem);
    $stmt->execute();

    header("Location: index.php");
    exit;
}
?>

<form method="POST" action="index.php">
    <input type="text" name="nome" placeholder="O teu nome" required>
    <textarea name="mensagem" placeholder="Escreve a tua mensagem..." required></textarea>
    <button type="submit">Submeter</button>
</form>

<hr>

<h2 style="margin-left: 30px; margin-top: 30px;">Mensagens Recentes:</h2>
<?php
$result = $conn->query("SELECT * FROM mensagens ORDER BY data DESC");

while ($row = $result->fetch_assoc()) {
    echo "<div class='mensagem'>";
    echo "<strong>" . htmlspecialchars($row['nome']) . "</strong><br>";
    echo "<p>" . nl2br(htmlspecialchars($row['mensagem'])) . "</p>";
    echo "<small>" . date("d/m/Y H:i", strtotime($row['data'])) . "</small>";

    if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
        echo " <a href='delete.php?id=" . $row['id'] . "' onclick='return confirm(\"Apagar esta mensagem?\")'>🗑️ Apagar</a>";
    }

    echo "</div><hr>";
}
?>

<?php include 'includes/footer.php'; ?>