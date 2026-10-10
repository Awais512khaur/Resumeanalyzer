<?php
// session_start();

require_once __DIR__ . "/../../../config/config.php";
require_once __DIR__ . "/../../../config/db.php";

// ADMIN CHECK
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL);
    exit;
}

// GET USER ID
$user_id = $_GET['id'] ?? null;

if (!$user_id) {
    die("User not found");
}

/* ================= USER ================= */
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("User not found");
}

/* ================= RESUMES ================= */
$stmt = $pdo->prepare("
    SELECT r.*, p.name as position_name
    FROM resumes r
    LEFT JOIN positions p ON r.position = p.id
    WHERE r.user_id = ?
    ORDER BY r.id DESC
");
$stmt->execute([$user_id]);
$resumes = $stmt->fetchAll();

/* ================= ANALYSIS ================= */
$stmt = $pdo->prepare("
    SELECT ar.*, r.file_name
    FROM analysis_results ar
    JOIN resumes r ON ar.resume_id = r.id
    WHERE r.user_id = ?
    ORDER BY ar.id DESC
");
$stmt->execute([$user_id]);
$analysis = $stmt->fetchAll();

/* ================= STATS ================= */
$total_resumes = count($resumes);
$total_analysis = count($analysis);

$avg_score = 0;
if ($total_analysis > 0) {
    $avg_score = round(array_sum(array_column($analysis, 'score')) / $total_analysis);
}

/* ================= CHART DATA ================= */
$labels = [];
$scores = [];

foreach ($analysis as $a) {
    $labels[] = date("d M", strtotime($a['created_at']));
    $scores[] = (int)$a['score'];
}



require_once __DIR__ . "/../../../templates/header.php";
?>

<div class="max-w-7xl mx-auto px-4">

    <!-- USER INFO -->
    <div class="bg-white/10 p-6 rounded-xl mb-6 backdrop-blur-lg">
        <h2 class="text-2xl font-bold"><?php echo htmlspecialchars($user['name']); ?></h2>
        <p class="text-gray-300"><?php echo $user['email']; ?></p>
        <p class="text-yellow-400 mt-2">Role: <?php echo $user['role']; ?></p>
    </div>

    <!-- STATS -->
    <div class="grid md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white/10 p-4 rounded text-center">
            <h3>Total Resumes</h3>
            <p class="text-2xl font-bold"><?php echo $total_resumes; ?></p>
        </div>

        <div class="bg-white/10 p-4 rounded text-center">
            <h3>Total Analysis</h3>
            <p class="text-2xl font-bold"><?php echo $total_analysis; ?></p>
        </div>

        <div class="bg-white/10 p-4 rounded text-center">
            <h3>Avg Score</h3>
            <p class="text-2xl font-bold text-green-400"><?php echo $avg_score; ?>%</p>
        </div>
    </div>
    <!-- CHART -->
    <div class="bg-white/10 p-6 rounded-xl mb-8">
        <h3 class="text-xl font-bold mb-4">Performance Chart</h3>
        <canvas id="scoreChart"></canvas>
    </div>

    <!-- RESUMES -->
    <div class="mb-10">
        <h3 class="text-xl font-bold mb-4">Resumes</h3>

        <div class="bg-white/10 rounded-xl overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-white/20">
                    <tr>
                        <th class="p-3">#</th>
                        <th class="p-3">File</th>
                        <th class="p-3">Position</th>
                        <th class="p-3">Date</th>
                        <th class="p-3">View</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($resumes as $i => $r): ?>
                        <tr class="border-b border-white/10">
                            <td class="p-3"><?php echo $i + 1; ?></td>
                            <td class="p-3"><?php echo htmlspecialchars($r['file_name']); ?></td>
                            <td class="p-3 text-blue-400"><?php echo $r['position_name'] ?? 'N/A'; ?></td>
                            <td class="p-3"><?php echo date("d M Y", strtotime($r['created_at'])); ?></td>
                            <td class="p-3">
                                <a href="<?php echo BASE_URL . $r['file_path']; ?>"
                                   target="_blank"
                                   class="bg-blue-500 px-3 py-1 rounded">
                                    View
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
        </div>
    </div>

    <!-- ANALYSIS -->
    <div>
        <h3 class="text-xl font-bold mb-4">Analysis History</h3>

        <?php foreach ($analysis as $a): ?>
            <div class="bg-white/10 p-4 rounded mb-4">
                <div class="flex justify-between">
                    <p><?php echo htmlspecialchars($a['file_name']); ?></p>
                    <span class="text-green-400 font-bold"><?php echo $a['score']; ?>%</span>
                </div>

                <div class="w-full bg-gray-700 h-2 rounded mt-2">
                    <div class="bg-green-500 h-2 rounded"
                         style="width: <?php echo $a['score']; ?>%">
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>

<!-- CHART SCRIPT -->
<script>
const ctx = document.getElementById('scoreChart');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Resume Score',
            data: <?php echo json_encode($scores); ?>,
            borderWidth: 2,
            tension: 0.4
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true,
                max: 100
            }
        }
    }
});
</script>

<?php require_once __DIR__ . "/../../../templates/footer.php"; ?>