<?php
// session_start();

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../templates/header.php";

// ADMIN CHECK
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL);
    exit;
}

// STATS
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalResumes = $pdo->query("SELECT COUNT(*) FROM resumes")->fetchColumn();
$totalAnalysis = $pdo->query("SELECT COUNT(*) FROM analysis_results")->fetchColumn();
$avgScore = $pdo->query("SELECT AVG(score) FROM analysis_results")->fetchColumn();

// SCORE DISTRIBUTION
$low = $pdo->query("SELECT COUNT(*) FROM analysis_results WHERE score < 40")->fetchColumn();
$medium = $pdo->query("SELECT COUNT(*) FROM analysis_results WHERE score BETWEEN 40 AND 70")->fetchColumn();
$high = $pdo->query("SELECT COUNT(*) FROM analysis_results WHERE score > 70")->fetchColumn();
?>

<div class="max-w-7xl mx-auto px-4">

    <h2 class="text-3xl font-bold mb-8">Admin Analytics</h2>

    <!-- STATS -->
    <div class="grid md:grid-cols-4 gap-6 mb-10">

        <div class="stat-card">
            <h3>Total Users</h3>
            <p class="text-2xl font-bold"><?php echo $totalUsers; ?></p>
        </div>

        <div class="stat-card">
            <h3>Total Resumes</h3>
            <p class="text-2xl font-bold"><?php echo $totalResumes; ?></p>
        </div>

        <div class="stat-card">
            <h3>Analyses</h3>
            <p class="text-2xl font-bold"><?php echo $totalAnalysis; ?></p>
        </div>

        <div class="stat-card">
            <h3>Avg Score</h3>
            <p class="text-2xl font-bold text-green-400">
                <?php echo round($avgScore); ?>%
            </p>
        </div>

    </div>

    <!-- 🔥 CHARTS -->
    <div class="grid md:grid-cols-2 gap-8">

        <!-- BAR CHART -->
        <div class="bg-white/10 p-6 rounded-xl backdrop-blur-lg">
            <h3 class="mb-4 font-semibold">System Overview</h3>
            <canvas id="barChart"></canvas>
        </div>

        <!-- PIE CHART -->
        <div class="bg-white/10 p-6 rounded-xl backdrop-blur-lg">
            <h3 class="mb-4 font-semibold">Score Distribution</h3>
            <canvas id="pieChart"></canvas>
        </div>

    </div>

</div>

<style>
.stat-card {
    background: rgba(255,255,255,0.08);
    padding: 20px;
    border-radius: 16px;
    text-align: center;
}
</style>

<script>
// BAR CHART
new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: ['Users', 'Resumes', 'Analyses'],
        datasets: [{
            data: [
                <?php echo $totalUsers; ?>,
                <?php echo $totalResumes; ?>,
                <?php echo $totalAnalysis; ?>
            ],
            backgroundColor: ['#3b82f6', '#22c55e', '#f59e0b']
        }]
    },
    options: {
        plugins: {
            legend: { display: false }
        }
    }
});

// PIE CHART
new Chart(document.getElementById('pieChart'), {
    type: 'doughnut',
    data: {
        labels: ['Low (<40)', 'Medium (40-70)', 'High (>70)'],
        datasets: [{
            data: [
                <?php echo $low; ?>,
                <?php echo $medium; ?>,
                <?php echo $high; ?>
            ],
            backgroundColor: ['#ef4444', '#facc15', '#22c55e']
        }]
    }
});
</script>

<?php require_once __DIR__ . "/../../templates/footer.php"; ?>