<?php
include 'includes/admin_auth.php';
include '../includes/db.php';
include 'includes/header_admin.php';

$contagemTipos = $conn->query("SELECT tipo, COUNT(*) AS total FROM utilizadores GROUP BY tipo");

$tipos = [];
$quantidades = [];

while ($row = $contagemTipos->fetch_assoc()) {
    $tipos[] = $row['tipo'];
    $quantidades[] = $row['total'];
}

$utilizadores = $conn->query("SELECT COUNT(*) AS total FROM utilizadores");
$total_utilizadores = $utilizadores->fetch_assoc()["total"];

$voos = $conn->query("SELECT COUNT(*) AS total FROM voos");
$total_voos = $voos->fetch_assoc()["total"];

$reservas = $conn->query("SELECT COUNT(*) AS total FROM reservas");
$total_reservas = $reservas->fetch_assoc()["total"];

$receita = $conn->query("
    SELECT SUM(voos.preco) AS total_receita
    FROM reservas
    INNER JOIN voos ON reservas.voo_id = voos.id
");
$total_receita = $receita->fetch_assoc()["total_receita"] ?? 0;
?>

<main>
    <h2>Painel de Administração</h2>

    <div class="painel-estatisticas">
        <div class="estatistica-box">
            <h3>Utilizadores</h3>
            <p><?= $total_utilizadores ?></p>
        </div>
        <div class="estatistica-box">
            <h3>Voos Disponíveis</h3>
            <p><?= $total_voos ?></p>
        </div>
        <div class="estatistica-box">
            <h3>Reservas Realizadas</h3>
            <p><?= $total_reservas ?></p>
        </div>
        <div class="estatistica-box">
            <h3>Receita Total</h3>
            <p><?= number_format($total_receita, 2) ?> €</p>
        </div>
    </div>

    <div style="max-width: 200px; margin: 40px auto;">
        <h3 style="text-align: center;">Distribuição de Utilizadores</h3>
        <canvas id="graficoUtilizadores"></canvas>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('graficoUtilizadores').getContext('2d');

    const grafico = new Chart(ctx, {
        type: 'pie',
        data: {
            labels: <?= json_encode($tipos) ?>,
            datasets: [{
                label: 'Utilizadores por Tipo',
                data: <?= json_encode($quantidades) ?>,
                backgroundColor: ['#007f84', '#94d2bd', '#ee9b00', '#e63946'],
                borderColor: '#ffffff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true
        }
    });
</script>

<?php include '../includes/footer.php'; ?>