<?php
// session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../templates/header.php";
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header("Location: " . BASE_URL . "login");
    exit;
}

/* =========================
   GET ALL USER ANALYSIS
=========================*/
$stmt = $pdo->prepare("
    SELECT ar.*, r.file_name 
    FROM analysis_results ar
    JOIN resumes r ON ar.resume_id = r.id
    WHERE r.user_id = ?
    ORDER BY ar.id DESC
");
$stmt->execute([$user_id]);
$results = $stmt->fetchAll();

if (!$results) {
    echo "<div class='max-w-4xl mx-auto px-4 text-center py-16'>";
    echo "<div class='bg-white/10 p-8 rounded-2xl backdrop-blur-lg'>";
    echo "<h3 class='text-2xl font-bold mb-3'>No Analysis Yet</h3>";
    echo "<p class='text-gray-300 mb-6'>Upload a resume and analyze it against a job description to see AI suggestions.</p>";
    echo "<a href='" . BASE_URL . "analyze' class='bg-blue-600 hover:bg-blue-700 px-6 py-2.5 rounded-lg font-bold inline-block'>Analyze a Resume</a>";
    echo "</div></div>";
    require_once __DIR__ . "/../../templates/footer.php";
    exit;
}

// LATEST USER ANALYSIS
$latest = $results[0];
$matched = explode(",", $latest['matched_keywords']);
$missing = explode(",", $latest['missing_keywords']);
$matched_count = count(array_filter($matched));
$missing_count = count(array_filter($missing));

/* =========================
   STATS
=========================*/
$total = count($results);
$avg = round(array_sum(array_column($results, 'score')) / $total);

/* =========================
   CHART DATA (Chronological)
=========================*/
$labels = [];
$scores = [];

foreach (array_reverse($results) as $row) {
    $labels[] = date("d M", strtotime($row['created_at']));
    $scores[] = (int)$row['score'];
}
?>
<div class="max-w-6xl mx-auto px-4">
    <div class="flex justify-between items-center mb-8 flex-wrap gap-4">
        <h2 class="text-3xl font-bold">Analysis Dashboard</h2>
        <a href="<?php echo BASE_URL; ?>analyze" class="bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg font-semibold text-sm flex items-center gap-1.5">
            <span>+</span> Analyze Another Resume
        </a>
    </div>

    <div class="grid md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white/10 p-4 rounded text-center">
            <h3>Total Analyses</h3>
            <p class="text-2xl font-bold"><?php echo $total; ?></p>
        </div>

        <div class="bg-white/10 p-4 rounded text-center">
            <h3>Average Score</h3>
            <p class="text-2xl font-bold text-green-400"><?php echo $avg; ?>%</p>
        </div>

        <div class="bg-white/10 p-4 rounded text-center">
            <h3>Latest Match Score</h3>
            <p class="text-2xl font-bold text-yellow-400"><?php echo $latest['score']; ?>%</p>
        </div>
    </div>

    <!-- 🔥 CHARTS -->
    <div class="grid md:grid-cols-2 gap-8 mb-10">
        <!-- SCORE CHART -->
        <div class="bg-white/10 p-6 rounded-xl backdrop-blur-lg">
            <h3 class="mb-4 font-semibold">Latest Match & ATS Score</h3>
            <canvas id="scoreChart"></canvas>
        </div>
        <!-- KEYWORD CHART -->
        <div class="bg-white/10 p-6 rounded-xl backdrop-blur-lg">
            <h3 class="mb-4 font-semibold">Keyword Distribution</h3>
            <canvas id="keywordChart"></canvas>
        </div>
    </div>

    <!-- MAIN CARD -->
    <div class="bg-white/10 p-8 rounded-2xl shadow-xl backdrop-blur-lg">
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-white/10 flex-wrap gap-2">
            <div>
                <span class="text-xs uppercase tracking-wider text-gray-400">Latest Analysis</span>
                <h3 class="text-xl font-bold text-white"><?php echo htmlspecialchars($latest['file_name']); ?></h3>
            </div>
            <span class="text-xs text-gray-400">Analyzed on <?php echo date("d M Y, h:i A", strtotime($latest['created_at'])); ?></span>
        </div>

        <!-- CIRCULAR METERS -->
        <div class="flex flex-col md:flex-row justify-around items-center gap-10 mb-10">
            <!-- RESUME SCORE -->
            <div class="text-center">
                <div class="relative w-40 h-40">
                    <svg class="w-40 h-40 transform -rotate-90">
                        <circle cx="80" cy="80" r="70"
                            stroke="#374151" stroke-width="10" fill="none"/>

                        <circle cx="80" cy="80" r="70"
                            stroke="#22c55e" stroke-width="10" fill="none"
                            stroke-dasharray="440"
                            stroke-dashoffset="<?php echo 440 - (440 * $latest['score'] / 100); ?>"
                            stroke-linecap="round"
                            class="transition-all duration-1000"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-2xl font-bold text-green-400">
                            <?php echo $latest['score']; ?>%
                        </span>
                    </div>
                </div>
                <p class="text-gray-300 mt-3 font-medium">Resume Match Score</p>
            </div>
            <!-- ATS SCORE -->
            <div class="text-center">
                <div class="relative w-40 h-40">
                    <svg class="w-40 h-40 transform -rotate-90">
                        <circle cx="80" cy="80" r="70"
                            stroke="#374151" stroke-width="10" fill="none"/>

                        <circle cx="80" cy="80" r="70"
                            stroke="#facc15" stroke-width="10" fill="none"
                            stroke-dasharray="440"
                            stroke-dashoffset="<?php echo 440 - (440 * $latest['ats_score'] / 100); ?>"
                            stroke-linecap="round"
                            class="transition-all duration-1000"/>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <span class="text-2xl font-bold text-yellow-400">
                            <?php echo $latest['ats_score']; ?>%
                        </span>
                    </div>
                </div>
                <p class="text-gray-300 mt-3 font-medium">ATS Compatibility Score</p>
            </div>
        </div>

        <!-- MATCHED -->
        <div class="mb-6">
            <h4 class="text-green-400 font-semibold mb-2">Matched Keywords (<?php echo $matched_count; ?>)</h4>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($matched as $word): ?>
                    <?php if (!empty(trim($word))): ?>
                        <span class="bg-green-500/20 text-green-300 border border-green-500/30 px-3 py-1 rounded-full text-sm">
                            <?php echo htmlspecialchars(trim($word)); ?>
                        </span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- MISSING -->
        <div class="mb-6">
            <h4 class="text-red-400 font-semibold mb-2">Missing Keywords (<?php echo $missing_count; ?>)</h4>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($missing as $word): ?>
                    <?php if (!empty(trim($word))): ?>
                        <span class="bg-red-500/20 text-red-300 border border-red-500/30 px-3 py-1 rounded-full text-sm">
                            <?php echo htmlspecialchars(trim($word)); ?>
                        </span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 🤖 AI SUGGESTIONS SECTION -->
        <div class="mt-8 pt-6 border-t border-white/10">
            <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        AI Recommendations
                    </span>
                    <h4 class="text-yellow-400 font-semibold text-lg">Resume Improvement Suggestions</h4>
                </div>
                <span class="text-xs text-gray-400 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-green-400 animate-pulse"></span>
                    Powered by Gemini AI
                </span>
            </div>

            <div class="grid gap-3">
                <?php 
                $suggestionsList = explode(" | ", $latest['suggestions']);
                foreach ($suggestionsList as $index => $s): 
                    $cleanS = trim($s);
                    if (!empty($cleanS)):
                ?>
                    <div class="flex items-start gap-3 bg-white/5 hover:bg-white/10 p-3.5 rounded-xl border border-white/10 transition">
                        <span class="flex-shrink-0 w-6 h-6 rounded-full bg-yellow-400/20 text-yellow-300 flex items-center justify-center text-xs font-bold mt-0.5">
                            <?php echo $index + 1; ?>
                        </span>
                        <p class="text-gray-200 text-sm leading-relaxed">
                            <?php echo htmlspecialchars($cleanS); ?>
                        </p>
                    </div>
                <?php 
                    endif;
                endforeach; 
                ?>
            </div>
        </div>

        <!-- DOWNLOAD BUTTON -->
        <div class="text-center mt-8 pt-6 border-t border-white/10">
            <a href="<?php echo BASE_URL; ?>modules/analysis/generate_pdf.php?id=<?php echo $latest['id']; ?>"
               class="bg-blue-600 px-6 py-2.5 rounded-lg hover:bg-blue-700 inline-flex items-center gap-2 font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Download PDF Report
            </a>
        </div>
    </div>

    <!-- PERFORMANCE TREND CHART -->
    <div class="bg-white/10 p-6 rounded-xl mb-10 mt-10">
        <h3 class="mb-4 font-semibold text-lg">Performance History</h3>
        <canvas id="chart"></canvas>
    </div>

    <!-- PAST ANALYSES LIST -->
    <h3 class="text-xl font-bold mb-4">Past Analyses</h3>
    <?php foreach ($results as $item): ?>
        <div class="bg-white/10 p-5 rounded-xl mb-4 border border-white/5">
            <!-- TITLE -->
            <div class="flex justify-between items-center mb-2">
                <h4 class="font-semibold text-base"><?php echo htmlspecialchars($item['file_name']); ?></h4>
                <div class="text-right">
                    <span class="text-green-400 font-bold"><?php echo $item['score']; ?>% Match</span>
                    <span class="text-xs text-gray-400 block"><?php echo date("d M Y, h:i A", strtotime($item['created_at'])); ?></span>
                </div>
            </div>

            <!-- BAR -->
            <div class="w-full bg-gray-700 h-2 rounded mb-3">
                <div class="bg-green-500 h-2 rounded"
                     style="width: <?php echo $item['score']; ?>%">
                </div>
            </div>

            <div class="flex justify-between items-center text-xs text-gray-400">
                <span>ATS Score: <strong class="text-yellow-400"><?php echo $item['ats_score']; ?>%</strong></span>
                <a href="<?php echo BASE_URL; ?>modules/analysis/generate_pdf.php?id=<?php echo $item['id']; ?>" class="text-blue-400 hover:text-blue-300 font-medium">
                    View & Download PDF &rarr;
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Chart Script -->
<script>
new Chart(document.getElementById('scoreChart'), {
    type: 'bar',
    data: {
        labels: ['Resume Match', 'ATS Score'],
        datasets: [{
            data: [<?php echo $latest['score']; ?>, <?php echo $latest['ats_score']; ?>],
            backgroundColor: ['#22c55e', '#facc15']
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, max: 100 } }
    }
});

new Chart(document.getElementById('keywordChart'), {
    type: 'doughnut',
    data: {
        labels: ['Matched', 'Missing'],
        datasets: [{
            data: [<?php echo $matched_count; ?>, <?php echo $missing_count; ?>],
            backgroundColor: ['#22c55e', '#ef4444']
        }]
    }
});

new Chart(document.getElementById('chart'), {
    type: 'line',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Score',
            data: <?php echo json_encode($scores); ?>,
            borderWidth: 2,
            tension: 0.4
        }]
    },
    options: {
        scales: {
            y: { beginAtZero: true, max: 100 }
        }
    }
});
</script>
<?php require_once __DIR__ . "/../../templates/footer.php"; ?>