<?php
session_start();
require_once 'includes/db.php';
include 'includes/header.php';

$priorityStats = [];
$monthlyStats = [];
$avgCompletionTime = null;

if (isset($_SESSION['utilizador_id'])) {
    $utilizador_id = $_SESSION['utilizador_id'];

    $stmt = $conn->prepare("SELECT prioridade, COUNT(*) as total FROM tarefas WHERE utilizador_id = ? AND estado = 'Concluída' GROUP BY prioridade");
    $stmt->bind_param("i", $utilizador_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $priorityStats[$row['prioridade']] = $row['total'];
    }

    $stmt = $conn->prepare("SELECT DATE_FORMAT(data_criacao, '%Y-%m') as mes, COUNT(*) as total FROM tarefas WHERE utilizador_id = ? AND estado = 'Concluída' GROUP BY mes");
    $stmt->bind_param("i", $utilizador_id);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $monthlyStats[$row['mes']] = $row['total'];
    }

    $stmt = $conn->prepare("SELECT AVG(TIMESTAMPDIFF(HOUR, data_criacao, data_limite)) as tempo_medio FROM tarefas WHERE utilizador_id = ? AND estado = 'Concluída' AND data_limite IS NOT NULL");
    $stmt->bind_param("i", $utilizador_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $avgCompletionTime = is_null($row['tempo_medio']) ? null : round($row['tempo_medio'], 1);
}
?>

<main>
<?php if (!isset($_SESSION['utilizador_id'])): ?>
    <div class="hero">
        <h1>Bem-vindo ao TaskManager</h1>
        <p>Organiza as tuas tarefas com facilidade e produtividade.</p>
        <a href="login.php" class="btn-primary">Começar Agora</a>
    </div>
<?php else:
    $utilizador_id = $_SESSION['utilizador_id'];
    $stmt = $conn->prepare("SELECT nome FROM utilizadores WHERE id = ?");
    $stmt->bind_param("i", $utilizador_id);
    $stmt->execute();
    $stmt->bind_result($nome);
    $stmt->fetch();
    $stmt->close();
?>
    <div class="hero">
        <h1>Olá, <?php echo htmlspecialchars($nome); ?>!</h1>
        <p>Estamos prontos para te ajudar a organizar as tuas tarefas.</p>
        <a href="my_tasks.php" class="btn-primary">Ver Tarefas</a>
    </div>
    <section class="dashboard">
        <h2>📊 Dashboard Analítico</h2>
        <div style="width: 200px; margin-bottom: 40px; text-align: center;">
            <canvas id="priorityChart" height="200"></canvas>
        </div>
        <div style="width: 400px; margin-bottom: 40px;">
            <canvas id="monthlyChart" height="200"></canvas>
        </div>
        <?php if (empty($priorityStats)): ?>
            <p style="text-align:center; color: #777;">Sem dados para mostrar tarefas por prioridade.</p>
        <?php endif; ?>
        <?php if (empty($monthlyStats)): ?>
            <p style="text-align:center; color: #777;">Sem dados para mostrar tarefas concluídas por mês.</p>
        <?php endif; ?>
        <p><strong>⏱ Tempo médio para completar tarefas:</strong>
            <?= is_numeric($avgCompletionTime) ? $avgCompletionTime . ' horas' : 'Sem dados disponíveis' ?>
        </p>
    </section>
    <?php endif; ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const priorityData = <?= json_encode(array_values($priorityStats)) ?>;
    const priorityLabels = <?= json_encode(array_keys($priorityStats)) ?>;

    const monthlyData = <?= json_encode(array_values($monthlyStats)) ?>;
    const monthlyLabels = <?= json_encode(array_keys($monthlyStats)) ?>;

    const ctxPriority = document.getElementById('priorityChart');
    const ctxMonthly = document.getElementById('monthlyChart');

    if (ctxPriority) {
        new Chart(ctxPriority, {
            type: 'pie',
            data: {
                labels: priorityLabels,
                datasets: [{
                    label: 'Tarefas por Prioridade',
                    data: priorityData
                }]
            }
        });
    }

    if (ctxMonthly) {
        new Chart(ctxMonthly, {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'Tarefas Concluídas por Mês',
                    data: monthlyData
                }]
            }
        });
    }

    const hasPriorityData = priorityData.length > 0;
    const hasMonthlyData = monthlyData.length > 0;

    if (!hasPriorityData) {
        document.getElementById('priorityChart').style.display = 'none';
    }

    if (!hasMonthlyData) {
        document.getElementById('monthlyChart').style.display = 'none';
    }
</script>


<?php include 'includes/footer.php'; ?>