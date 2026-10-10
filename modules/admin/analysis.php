<?php
require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: " . BASE_URL . "login");
    exit;
}

$totalUsers = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalResumes = (int)$pdo->query("SELECT COUNT(*) FROM resumes")->fetchColumn();
$totalAnalysis = (int)$pdo->query("SELECT COUNT(*) FROM analysis_results")->fetchColumn();
$avgScore = (float)$pdo->query("SELECT COALESCE(AVG(score), 0) FROM analysis_results")->fetchColumn();
$low = (int)$pdo->query("SELECT COUNT(*) FROM analysis_results WHERE score < 40")->fetchColumn();
$medium = (int)$pdo->query("SELECT COUNT(*) FROM analysis_results WHERE score BETWEEN 40 AND 70")->fetchColumn();
$high = (int)$pdo->query("SELECT COUNT(*) FROM analysis_results WHERE score > 70")->fetchColumn();

require_once __DIR__ . "/../../templates/header.php";
?>
<main class="mx-auto w-full max-w-7xl px-4 pb-14 pt-5 sm:px-6">
    <section class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="mb-2 text-xs font-semibold uppercase tracking-[.2em] text-teal-200">Platform insights</p>
            <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">System analytics</h1>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-slate-400">A live overview of accounts, resume activity, and candidate match scores.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>admin_audit_logs" class="inline-flex items-center justify-center gap-2 rounded-xl border border-teal-200/20 bg-teal-200/[.07] px-4 py-2.5 text-sm font-semibold text-teal-100 transition hover:border-teal-200/40 hover:bg-teal-200/[.12]">
            <span aria-hidden="true">◷</span> Open audit log <span aria-hidden="true">→</span>
        </a>
    </section>

    <section aria-label="Platform totals" class="mb-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <?php
        $stats = [
            ['label' => 'Registered users', 'value' => number_format($totalUsers), 'hint' => 'Accounts in the system', 'icon' => '◎', 'tone' => 'sky'],
            ['label' => 'Resumes', 'value' => number_format($totalResumes), 'hint' => 'Uploaded documents', 'icon' => '▤', 'tone' => 'teal'],
            ['label' => 'Analyses', 'value' => number_format($totalAnalysis), 'hint' => 'Completed resume checks', 'icon' => '✦', 'tone' => 'violet'],
            ['label' => 'Average score', 'value' => number_format($avgScore, 1) . '%', 'hint' => 'Across all analyses', 'icon' => '↗', 'tone' => 'amber'],
        ];
        foreach ($stats as $stat):
        ?>
            <article class="stat-card relative overflow-hidden !rounded-2xl !p-5">
                <div class="absolute -right-6 -top-8 h-24 w-24 rounded-full bg-<?php echo $stat['tone']; ?>-400/[.07] blur-2xl" aria-hidden="true"></div>
                <div class="relative flex items-start justify-between gap-3">
                    <div>
                        <h2 class="!text-[.72rem] uppercase tracking-[.13em]"><?php echo htmlspecialchars($stat['label']); ?></h2>
                        <p class="stat-value !mt-3 !text-3xl !font-bold tracking-tight"><?php echo htmlspecialchars($stat['value']); ?></p>
                        <p class="mt-2 text-xs leading-5 text-slate-500"><?php echo htmlspecialchars($stat['hint']); ?></p>
                    </div>
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl border border-white/[.08] bg-white/[.04] text-lg text-<?php echo $stat['tone']; ?>-200" aria-hidden="true"><?php echo $stat['icon']; ?></span>
                </div>
            </article>
        <?php endforeach; ?>
    </section>

    <section aria-label="Analytics charts" class="grid gap-5 xl:grid-cols-[1.12fr_.88fr]">
        <article class="surface-card rounded-2xl p-5 sm:p-6">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-base font-semibold text-white">System overview</h2>
                    <p class="mt-1 text-xs text-slate-400">Users, uploaded resumes, and completed analyses</p>
                </div>
                <span class="rounded-full border border-sky-200/10 bg-sky-200/[.05] px-3 py-1 text-[.68rem] font-medium text-sky-100">All time</span>
            </div>
            <div class="relative h-[280px] sm:h-[330px]">
                <canvas id="barChart" role="img" aria-label="Bar chart comparing users, resumes, and analyses"></canvas>
            </div>
        </article>

        <article class="surface-card rounded-2xl p-5 sm:p-6">
            <div class="mb-5">
                <h2 class="text-base font-semibold text-white">Score distribution</h2>
                <p class="mt-1 text-xs text-slate-400">Resume match score bands from completed analyses</p>
            </div>
            <div class="relative mx-auto h-[280px] max-w-[430px] sm:h-[330px]">
                <canvas id="scoreChart" role="img" aria-label="Doughnut chart showing low, medium, and high resume scores"></canvas>
                <div class="pointer-events-none absolute inset-0 grid place-items-center pb-1 text-center">
                    <div><p class="text-3xl font-bold text-white"><?php echo number_format($totalAnalysis); ?></p><p class="mt-1 text-[.65rem] font-semibold uppercase tracking-[.16em] text-slate-500">Analyses</p></div>
                </div>
            </div>
            <div class="mt-2 grid grid-cols-3 gap-2 border-t border-white/[.07] pt-4 text-center">
                <div><span class="mx-auto mb-2 block h-1.5 w-7 rounded-full bg-rose-400"></span><p class="text-lg font-semibold text-slate-100"><?php echo number_format($low); ?></p><p class="mt-1 text-[.68rem] text-slate-500">Low &lt; 40</p></div>
                <div><span class="mx-auto mb-2 block h-1.5 w-7 rounded-full bg-amber-300"></span><p class="text-lg font-semibold text-slate-100"><?php echo number_format($medium); ?></p><p class="mt-1 text-[.68rem] text-slate-500">Medium 40–70</p></div>
                <div><span class="mx-auto mb-2 block h-1.5 w-7 rounded-full bg-teal-300"></span><p class="text-lg font-semibold text-slate-100"><?php echo number_format($high); ?></p><p class="mt-1 text-[.68rem] text-slate-500">High &gt; 70</p></div>
            </div>
        </article>
    </section>
</main>

<script>
const chartFont = { family: 'Inter, ui-sans-serif, system-ui, sans-serif' };
const gridColor = 'rgba(148, 163, 184, .09)';
new Chart(document.getElementById('barChart'), {
    type: 'bar',
    data: {
        labels: ['Users', 'Resumes', 'Analyses'],
        datasets: [{
            data: [<?php echo $totalUsers; ?>, <?php echo $totalResumes; ?>, <?php echo $totalAnalysis; ?>],
            backgroundColor: ['rgba(103, 217, 243, .78)', 'rgba(105, 232, 202, .78)', 'rgba(167, 139, 250, .78)'],
            hoverBackgroundColor: ['#67d9f3', '#69e8ca', '#a78bfa'],
            borderColor: ['#67d9f3', '#69e8ca', '#a78bfa'],
            borderWidth: 1,
            borderRadius: 9,
            borderSkipped: false,
            maxBarThickness: 72
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: 700, easing: 'easeOutQuart' },
        plugins: {
            legend: { display: false },
            tooltip: { backgroundColor: 'rgba(8, 13, 27, .96)', borderColor: 'rgba(148,163,184,.2)', borderWidth: 1, titleColor: '#f8fafc', bodyColor: '#cbd5e1', padding: 12, displayColors: false, titleFont: { ...chartFont, weight: '600' }, bodyFont: chartFont }
        },
        scales: {
            x: { grid: { display: false }, border: { display: false }, ticks: { color: '#8090a8', font: { ...chartFont, size: 11 } } },
            y: { beginAtZero: true, border: { display: false }, grid: { color: gridColor, drawTicks: false }, ticks: { color: '#71809a', precision: 0, padding: 10, font: { ...chartFont, size: 10 } } }
        }
    }
});

new Chart(document.getElementById('scoreChart'), {
    type: 'doughnut',
    data: {
        labels: ['Low (<40)', 'Medium (40–70)', 'High (>70)'],
        datasets: [{
            data: [<?php echo $low; ?>, <?php echo $medium; ?>, <?php echo $high; ?>],
            backgroundColor: ['rgba(251, 113, 133, .88)', 'rgba(251, 191, 36, .88)', 'rgba(45, 212, 191, .88)'],
            hoverBackgroundColor: ['#fb7185', '#fbbf24', '#2dd4bf'],
            borderColor: 'rgba(17, 24, 39, .95)',
            borderWidth: 4,
            hoverOffset: 7,
            borderRadius: 5,
            spacing: 2
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '76%',
        animation: { animateRotate: true, duration: 750 },
        plugins: {
            legend: { display: false },
            tooltip: { backgroundColor: 'rgba(8, 13, 27, .96)', borderColor: 'rgba(148,163,184,.2)', borderWidth: 1, titleColor: '#f8fafc', bodyColor: '#cbd5e1', padding: 12, titleFont: { ...chartFont, weight: '600' }, bodyFont: chartFont, callbacks: { label: (context) => ` ${context.label}: ${context.raw}` } }
        }
    }
});
</script>

<?php require_once __DIR__ . "/../../templates/footer.php"; ?>
