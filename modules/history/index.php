<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    header("Location: " . BASE_URL . "login");
    exit;
}

$statement = $pdo->prepare(
    "SELECT analysis_results.*, resumes.file_name, resumes.position, positions.name AS position_name
     FROM analysis_results
     JOIN resumes ON resumes.id = analysis_results.resume_id
     LEFT JOIN positions ON positions.id = resumes.position
     WHERE resumes.user_id = ?
     ORDER BY analysis_results.id DESC"
);
$statement->execute([$userId]);
$results = $statement->fetchAll();
require_once __DIR__ . "/../../templates/header.php";
?>
<main class="mx-auto w-full max-w-7xl px-4 pb-14 pt-5 sm:px-6">
    <?php if (!$results): ?>
        <section class="surface-card mx-auto max-w-3xl rounded-3xl px-6 py-16 text-center sm:px-12">
            <span class="mx-auto mb-5 grid h-16 w-16 place-items-center rounded-2xl border border-teal-200/15 bg-teal-200/[.07] text-3xl text-teal-100" aria-hidden="true">◷</span>
            <p class="mb-2 text-xs font-semibold uppercase tracking-[.2em] text-teal-200">Your progress, in one place</p>
            <h1 class="text-3xl font-bold tracking-tight text-white">No analysis history yet</h1>
            <p class="mx-auto mt-3 max-w-md text-sm leading-6 text-slate-400">Upload a resume and analyze it against a position to see your match scores and recommendations here.</p>
            <a href="<?php echo BASE_URL; ?>upload" class="mt-7 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#9af2d5] to-[#67d9f3] px-5 py-3 font-bold text-[#06131a] shadow-lg shadow-teal-900/20 transition hover:-translate-y-0.5">Upload a resume <span aria-hidden="true">→</span></a>
        </section>
    <?php else: ?>
        <?php
            $latest = $results[0];
            $matched = array_values(array_filter(array_map('trim', explode(',', (string)$latest['matched_keywords']))));
            $missing = array_values(array_filter(array_map('trim', explode(',', (string)$latest['missing_keywords']))));
            $totalAnalyses = count($results);
            $averageScore = (int)round(array_sum(array_map('intval', array_column($results, 'score'))) / $totalAnalyses);
            $scoresChronological = array_reverse($results);
            $chartLabels = [];
            $chartScores = [];
            foreach ($scoresChronological as $row) {
                $chartLabels[] = date('d M', strtotime($row['created_at']));
                $chartScores[] = (int)$row['score'];
            }
            $suggestions = array_values(array_filter(array_map('trim', explode(' | ', (string)$latest['suggestions']))));
        ?>
        <section class="mb-8 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="mb-2 text-xs font-semibold uppercase tracking-[.2em] text-teal-200">Your resume insights</p>
                <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Analysis history</h1>
                <p class="mt-2 text-sm leading-6 text-slate-400">Review your past match scores and the guidance from each analysis.</p>
            </div>
            <a href="<?php echo BASE_URL; ?>analyze" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#9af2d5] to-[#67d9f3] px-4 py-2.5 text-sm font-bold text-[#06131a] shadow-lg shadow-teal-900/20 transition hover:-translate-y-0.5">Analyze a resume <span aria-hidden="true">→</span></a>
        </section>

        <section aria-label="Your analysis totals" class="mb-6 grid gap-4 sm:grid-cols-3">
            <article class="surface-card rounded-2xl p-5"><p class="text-xs font-semibold uppercase tracking-[.13em] text-slate-400">Total analyses</p><p class="mt-3 text-3xl font-bold text-white"><?php echo number_format($totalAnalyses); ?></p><p class="mt-1 text-xs text-slate-500">Completed resume checks</p></article>
            <article class="surface-card rounded-2xl p-5"><p class="text-xs font-semibold uppercase tracking-[.13em] text-slate-400">Average match</p><p class="mt-3 text-3xl font-bold text-teal-100"><?php echo $averageScore; ?><span class="text-lg">%</span></p><p class="mt-1 text-xs text-slate-500">Across your analysis history</p></article>
            <article class="surface-card rounded-2xl p-5"><p class="text-xs font-semibold uppercase tracking-[.13em] text-slate-400">Latest match</p><p class="mt-3 text-3xl font-bold text-cyan-100"><?php echo (int)$latest['score']; ?><span class="text-lg">%</span></p><p class="mt-1 text-xs text-slate-500">ATS compatibility <?php echo (int)$latest['ats_score']; ?>%</p></article>
        </section>

        <section aria-label="Analysis charts" class="mb-6 grid gap-5 xl:grid-cols-[1.2fr_.8fr]">
            <article class="surface-card rounded-2xl p-5 sm:p-6">
                <div class="mb-4">
                    <h2 class="font-semibold text-white">Match score over time</h2>
                    <p class="mt-1 text-xs text-slate-500">Your resume match percentage for each analysis</p>
                </div>
                <div class="relative h-[240px] sm:h-[280px]"><canvas id="scoreTrend" role="img" aria-label="Line chart of resume match scores over time"></canvas></div>
            </article>
            <article class="surface-card rounded-2xl p-5 sm:p-6">
                <div class="mb-4">
                    <h2 class="font-semibold text-white">Latest keyword match</h2>
                    <p class="mt-1 text-xs text-slate-500">Matched skills compared with missing skills</p>
                </div>
                <div class="relative mx-auto h-[240px] max-w-[300px] sm:h-[280px]">
                    <canvas id="keywordChart" role="img" aria-label="Doughnut chart of matched and missing skills"></canvas>
                    <div class="pointer-events-none absolute inset-0 grid place-items-center text-center"><div><p class="text-3xl font-bold text-white"><?php echo count($matched); ?>/<?php echo count($matched) + count($missing); ?></p><p class="mt-1 text-[.65rem] font-semibold uppercase tracking-[.15em] text-slate-500">Skills matched</p></div></div>
                </div>
                <div class="mt-1 flex justify-center gap-5 border-t border-white/[.07] pt-4 text-xs text-slate-400"><span><i class="mr-2 inline-block h-2 w-2 rounded-full bg-teal-300"></i>Matched <?php echo count($matched); ?></span><span><i class="mr-2 inline-block h-2 w-2 rounded-full bg-rose-300"></i>Missing <?php echo count($missing); ?></span></div>
            </article>
        </section>

        <section class="surface-card mb-8 rounded-2xl p-5 sm:p-7">
            <div class="flex flex-col gap-4 border-b border-white/[.07] pb-5 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[.16em] text-teal-200">Latest analysis</p>
                    <h2 class="mt-2 truncate text-xl font-semibold text-white"><?php echo htmlspecialchars($latest['file_name']); ?></h2>
                    <p class="mt-1 text-sm text-slate-500"><?php echo htmlspecialchars($latest['position_name'] ?: 'Custom job description'); ?> · <?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($latest['created_at']))); ?></p>
                </div>
                <div class="flex shrink-0 flex-wrap gap-2">
                    <a href="<?php echo BASE_URL; ?>modules/resume/view.php?id=<?php echo (int)$latest['resume_id']; ?>" target="_blank" rel="noopener noreferrer" class="rounded-xl border border-teal-200/15 bg-teal-200/[.06] px-3.5 py-2 text-sm font-semibold text-teal-100 hover:bg-teal-200/[.12]">View resume</a>
                    <a href="<?php echo BASE_URL; ?>modules/analysis/generate_pdf.php?id=<?php echo (int)$latest['id']; ?>" class="rounded-xl bg-gradient-to-r from-[#9af2d5] to-[#67d9f3] px-3.5 py-2 text-sm font-bold text-[#06131a]">Download report</a>
                </div>
            </div>

            <div class="grid gap-4 py-5 sm:grid-cols-2">
                <div class="rounded-xl border border-teal-200/10 bg-teal-200/[.035] p-4">
                    <div class="mb-2 flex items-center justify-between"><span class="text-sm text-slate-300">Resume match</span><strong class="text-lg text-teal-100"><?php echo (int)$latest['score']; ?>%</strong></div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-700/70"><div class="h-full rounded-full bg-gradient-to-r from-teal-300 to-cyan-300" style="width:<?php echo max(0, min(100, (int)$latest['score'])); ?>%"></div></div>
                </div>
                <div class="rounded-xl border border-cyan-200/10 bg-cyan-200/[.035] p-4">
                    <div class="mb-2 flex items-center justify-between"><span class="text-sm text-slate-300">ATS compatibility</span><strong class="text-lg text-cyan-100"><?php echo (int)$latest['ats_score']; ?>%</strong></div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-700/70"><div class="h-full rounded-full bg-gradient-to-r from-sky-300 to-indigo-300" style="width:<?php echo max(0, min(100, (int)$latest['ats_score'])); ?>%"></div></div>
                </div>
            </div>

            <div class="grid gap-5 border-t border-white/[.07] pt-5 lg:grid-cols-2">
                <div>
                    <h3 class="mb-3 text-sm font-semibold text-teal-100">Matched skills <span class="text-slate-500">(<?php echo count($matched); ?>)</span></h3>
                    <div class="flex flex-wrap gap-2">
                        <?php if ($matched): foreach ($matched as $skill): ?><span class="rounded-full border border-teal-200/15 bg-teal-200/[.06] px-3 py-1.5 text-xs text-teal-100"><?php echo htmlspecialchars($skill); ?></span><?php endforeach; else: ?><span class="text-sm text-slate-500">No required skills matched yet.</span><?php endif; ?>
                    </div>
                </div>
                <div>
                    <h3 class="mb-3 text-sm font-semibold text-rose-200">Skills to improve <span class="text-slate-500">(<?php echo count($missing); ?>)</span></h3>
                    <div class="flex flex-wrap gap-2">
                        <?php if ($missing): foreach ($missing as $skill): ?><span class="rounded-full border border-rose-200/15 bg-rose-200/[.05] px-3 py-1.5 text-xs text-rose-100"><?php echo htmlspecialchars($skill); ?></span><?php endforeach; else: ?><span class="text-sm text-slate-500">No missing skills in this analysis.</span><?php endif; ?>
                    </div>
                </div>
            </div>

            <?php if ($suggestions): ?>
                <div class="mt-6 border-t border-white/[.07] pt-5">
                    <div class="mb-3 flex items-center gap-2"><span class="grid h-7 w-7 place-items-center rounded-lg bg-violet-200/[.08] text-violet-200" aria-hidden="true">✦</span><h3 class="text-sm font-semibold text-white">Recommendations</h3><span class="text-xs text-slate-500">from your latest analysis</span></div>
                    <ul class="grid gap-2 md:grid-cols-2">
                        <?php foreach (array_slice($suggestions, 0, 6) as $suggestion): ?><li class="flex items-start gap-2.5 rounded-xl border border-white/[.06] bg-white/[.025] p-3 text-sm leading-5 text-slate-300"><span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-violet-300" aria-hidden="true"></span><?php echo htmlspecialchars($suggestion); ?></li><?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        </section>

        <?php if ($totalAnalyses > 1): ?>
        <section aria-labelledby="past-analyses-heading">
            <div class="mb-4 flex items-end justify-between gap-3"><div><h2 id="past-analyses-heading" class="text-xl font-semibold text-white">Past analyses</h2><p class="mt-1 text-sm text-slate-500">Your most recent results appear first.</p></div><span class="text-xs text-slate-500"><?php echo number_format($totalAnalyses); ?> total</span></div>
            <div class="grid gap-3">
                <?php foreach (array_slice($results, 1) as $item): ?>
                    <article class="surface-card rounded-2xl p-4 transition hover:border-teal-200/20 sm:p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <h3 class="truncate font-semibold text-slate-100"><?php echo htmlspecialchars($item['file_name']); ?></h3>
                                <p class="mt-1 text-xs text-slate-500"><?php echo htmlspecialchars($item['position_name'] ?: 'Custom job description'); ?> · <?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($item['created_at']))); ?></p>
                            </div>
                            <div class="flex flex-wrap items-center gap-3 sm:justify-end">
                                <span class="rounded-full border border-teal-200/10 bg-teal-200/[.05] px-3 py-1.5 text-xs font-semibold text-teal-100"><?php echo (int)$item['score']; ?>% match</span>
                                <span class="text-xs text-slate-400">ATS <?php echo (int)$item['ats_score']; ?>%</span>
                                <a href="<?php echo BASE_URL; ?>modules/resume/view.php?id=<?php echo (int)$item['resume_id']; ?>" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-teal-200 hover:text-teal-100">Resume ↗</a>
                                <a href="<?php echo BASE_URL; ?>modules/analysis/generate_pdf.php?id=<?php echo (int)$item['id']; ?>" class="text-xs font-semibold text-sky-200 hover:text-sky-100">Report ↓</a>
                            </div>
                        </div>
                        <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-slate-700/60"><div class="h-full rounded-full bg-gradient-to-r from-teal-300 to-cyan-300" style="width:<?php echo max(0, min(100, (int)$item['score'])); ?>%"></div></div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>
    <?php endif; ?>
</main>

<?php if (!empty($results)): ?>
<script>
const chartFont = { family: 'Inter, ui-sans-serif, system-ui, sans-serif' };
const tooltipStyle = { backgroundColor: 'rgba(8, 13, 27, .96)', borderColor: 'rgba(148, 163, 184, .2)', borderWidth: 1, titleColor: '#f8fafc', bodyColor: '#cbd5e1', padding: 11, titleFont: { ...chartFont, weight: '600' }, bodyFont: chartFont };
new Chart(document.getElementById('scoreTrend'), {
    type: 'line',
    data: { labels: <?php echo json_encode($chartLabels); ?>, datasets: [{ label: 'Match score', data: <?php echo json_encode($chartScores); ?>, borderColor: '#67e8d3', backgroundColor: 'rgba(103, 232, 211, .12)', pointBackgroundColor: '#9af2d5', pointBorderColor: '#102334', pointRadius: 3, pointHoverRadius: 5, borderWidth: 2, tension: .35, fill: true }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: tooltipStyle }, scales: { x: { grid: { display: false }, border: { display: false }, ticks: { color: '#8090a8', maxTicksLimit: 8, font: { ...chartFont, size: 10 } } }, y: { min: 0, max: 100, border: { display: false }, grid: { color: 'rgba(148,163,184,.09)' }, ticks: { color: '#71809a', stepSize: 25, callback: value => value + '%', font: { ...chartFont, size: 10 } } } } }
});
new Chart(document.getElementById('keywordChart'), {
    type: 'doughnut',
    data: { labels: ['Matched', 'Missing'], datasets: [{ data: [<?php echo count($matched); ?>, <?php echo count($missing); ?>], backgroundColor: ['rgba(45, 212, 191, .9)', 'rgba(251, 113, 133, .82)'], hoverBackgroundColor: ['#2dd4bf', '#fb7185'], borderColor: 'rgba(17, 24, 39, .95)', borderWidth: 4, hoverOffset: 6, spacing: 2 }] },
    options: { responsive: true, maintainAspectRatio: false, cutout: '76%', plugins: { legend: { display: false }, tooltip: { ...tooltipStyle, callbacks: { label: context => ` ${context.label}: ${context.raw}` } } } }
});
</script>
<?php endif; ?>
<?php require_once __DIR__ . "/../../templates/footer.php"; ?>
