<?php
session_start();

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../templates/header.php";

// 🔒 CHECK ADMIN
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "login");
    exit;
}

// STATS
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalResumes = $pdo->query("SELECT COUNT(*) FROM resumes")->fetchColumn();
$totalAnalysis = $pdo->query("SELECT COUNT(*) FROM analysis_results")->fetchColumn();
$avgScore = $pdo->query("SELECT AVG(score) FROM analysis_results")->fetchColumn();
?>

<div class="max-w-7xl mx-auto px-4">

    <h2 class="text-3xl font-bold mb-8">Admin Dashboard</h2>

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

    <!-- NAV -->
    <div class="grid md:grid-cols-3 gap-6">

        <a href="<?php echo BASE_URL; ?>admin_users" class="card">
            <h3 class="text-xl font-semibold">Manage Users</h3>
        </a>

        <a href="<?php echo BASE_URL; ?>admin_resumes" class="card">
            <h3 class="text-xl font-semibold">View Resumes</h3>
        </a>

        <a href="<?php echo BASE_URL; ?>admin_analysis" class="card">
            <h3 class="text-xl font-semibold">View Analysis</h3>
        </a>

    </div>

</div>

<?php require_once __DIR__ . "/../../templates/footer.php"; ?>
