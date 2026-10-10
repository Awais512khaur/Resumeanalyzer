<?php

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login");
    exit;
}

// 🔒 PREVENT BACK CACHE
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../templates/header.php";

// 🔥 IMPORTANT (missing before)
$user_role = $_SESSION['role'] ?? 'user';

// USER ID
$user_id = $_SESSION['user_id'];

// TOTAL RESUMES
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM resumes WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_resumes = $stmt->fetch()['total'] ?? 0;

// TOTAL ANALYSIS
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total 
    FROM analysis_results ar
    JOIN resumes r ON ar.resume_id = r.id
    WHERE r.user_id = ?
");
$stmt->execute([$user_id]);
$total_analysis = $stmt->fetch()['total'] ?? 0;

// AVG SCORE
$stmt = $pdo->prepare("
    SELECT AVG(score) as avg_score 
    FROM analysis_results ar
    JOIN resumes r ON ar.resume_id = r.id
    WHERE r.user_id = ?
");
$stmt->execute([$user_id]);
$avg_score = round($stmt->fetch()['avg_score'] ?? 0);

?>

<div class="max-w-7xl mx-auto px-4">

    <!-- Welcome -->
    <div class="mb-8 animate-fade-in">
        <h2 class="text-3xl font-bold">Welcome Back 👋</h2>
        <p class="text-gray-300">Analyze your resume and improve your chances</p>
    </div>

    <!-- ACTION CARDS -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

        <?php if ($user_role === 'admin'): ?>
            <a href="<?php echo BASE_URL; ?>admin_positions" class="card">
                <h3 class="text-xl font-semibold mb-2">Manage Positions</h3>
                <p class="text-gray-300">Add / manage positions</p>
            </a>
        <?php endif; ?>

        <a href="<?php echo BASE_URL; ?>upload" class="card">
            <h3 class="text-xl font-semibold mb-2">Upload Resume</h3>
            <p class="text-gray-300">Upload and analyze your resume</p>
        </a>

        <a href="<?php echo BASE_URL; ?>analyze" class="card">
            <h3 class="text-xl font-semibold mb-2">Analyze Resume</h3>
            <p class="text-gray-300">Compare with job description</p>
        </a>

        <?php if ($user_role === 'user'): ?>
            <a href="<?php echo BASE_URL; ?>history" class="card">
                <h3 class="text-xl font-semibold mb-2">View History</h3>
                <p class="text-gray-300">Check past analysis reports</p>
            </a>
        <?php else: ?>
            <a href="<?php echo BASE_URL; ?>admin_analysis" class="card">
                <h3 class="text-xl font-semibold mb-2">View Analytics</h3>
                <p class="text-gray-300">Admin analytics dashboard</p>
            </a>
        <?php endif; ?>

    </div>

    <!-- STATS -->
    <div class="mt-10 grid grid-cols-1 md:grid-cols-3 gap-6">

        <div class="stat-card">
            <h3>Total Resumes</h3>
            <p class="text-2xl font-bold"><?php echo $total_resumes; ?></p>
        </div>

        <div class="stat-card">
            <h3>Analyses Done</h3>
            <p class="text-2xl font-bold"><?php echo $total_analysis; ?></p>
        </div>

        <div class="stat-card">
            <h3>Avg Score</h3>
            <p class="text-2xl font-bold text-green-400">
                <?php echo $avg_score; ?>%
            </p>
        </div>

    </div>

</div>

<style>
.card {
    background: rgba(255,255,255,0.08);
    padding: 20px;
    border-radius: 16px;
    transition: 0.3s;
    backdrop-filter: blur(10px);
}
.card:hover {
    transform: translateY(-5px);
    background: rgba(255,255,255,0.15);
}

.stat-card {
    background: rgba(255,255,255,0.08);
    padding: 20px;
    border-radius: 16px;
    text-align: center;
}

@keyframes fade-in {
    from {opacity:0; transform: translateY(20px);}
    to {opacity:1;}
}
.animate-fade-in {
    animation: fade-in 0.8s ease;
}
</style>

<?php require_once __DIR__ . "/../../templates/footer.php"; ?>