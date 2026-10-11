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
            <article class="surface-card rounded-2xl p-5"><p class="text-xs font-semibold uppercase tracking-[.13em] text-slate-400">Selected match</p><p id="selectedMatchScore" class="mt-3 text-3xl font-bold text-cyan-100"><?php echo (int)$latest['score']; ?><span class="text-lg">%</span></p><p id="selectedAtsScore" class="mt-1 text-xs text-slate-500">ATS compatibility <?php echo (int)$latest['ats_score']; ?>%</p></article>
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
                    <h2 class="font-semibold text-white">Selected keyword match</h2>
                    <p class="mt-1 text-xs text-slate-500">Matched skills compared with missing skills</p>
                </div>
                <div class="relative mx-auto h-[240px] max-w-[300px] sm:h-[280px]">
                    <canvas id="keywordChart" role="img" aria-label="Doughnut chart of matched and missing skills"></canvas>
                    <div class="pointer-events-none absolute inset-0 grid place-items-center text-center"><div><p id="keywordChartCenter" class="text-3xl font-bold text-white"><?php echo count($matched); ?>/<?php echo count($matched) + count($missing); ?></p><p class="mt-1 text-[.65rem] font-semibold uppercase tracking-[.15em] text-slate-500">Skills matched</p></div></div>
                </div>
                <div class="mt-1 flex justify-center gap-5 border-t border-white/[.07] pt-4 text-xs text-slate-400"><span><i class="mr-2 inline-block h-2 w-2 rounded-full bg-teal-300"></i>Matched <b id="matchedCount"><?php echo count($matched); ?></b></span><span><i class="mr-2 inline-block h-2 w-2 rounded-full bg-rose-300"></i>Missing <b id="missingCount"><?php echo count($missing); ?></b></span></div>
            </article>
        </section>

        <section id="activeAnalysis" class="surface-card mb-8 rounded-2xl p-5 sm:p-7">
            <div class="flex flex-col gap-4 border-b border-white/[.07] pb-5 sm:flex-row sm:items-start sm:justify-between">
                <div class="min-w-0">
                    <p id="activeAnalysisLabel" class="text-xs font-semibold uppercase tracking-[.16em] text-teal-200">Latest analysis</p>
                    <h2 id="activeFileName" class="mt-2 truncate text-xl font-semibold text-white"><?php echo htmlspecialchars($latest['file_name']); ?></h2>
                    <p id="activeMeta" class="mt-1 text-sm text-slate-500"><?php echo htmlspecialchars($latest['position_name'] ?: 'Custom job description'); ?> · <?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($latest['created_at']))); ?></p>
                </div>
                <div class="flex shrink-0 flex-wrap gap-2">
                    <a id="activeResumeLink" href="<?php echo BASE_URL; ?>modules/resume/view.php?id=<?php echo (int)$latest['resume_id']; ?>" target="_blank" rel="noopener noreferrer" class="rounded-xl border border-teal-200/15 bg-teal-200/[.06] px-3.5 py-2 text-sm font-semibold text-teal-100 hover:bg-teal-200/[.12]">View resume</a>
                    <a id="activeReportLink" href="<?php echo BASE_URL; ?>modules/analysis/generate_pdf.php?id=<?php echo (int)$latest['id']; ?>" class="rounded-xl bg-gradient-to-r from-[#9af2d5] to-[#67d9f3] px-3.5 py-2 text-sm font-bold text-[#06131a]">Download report</a>
                </div>
            </div>

            <div class="grid gap-4 py-5 sm:grid-cols-2">
                <div class="rounded-xl border border-teal-200/10 bg-teal-200/[.035] p-4">
                    <div class="mb-2 flex items-center justify-between"><span class="text-sm text-slate-300">Resume match</span><strong id="activeMatchScore" class="text-lg text-teal-100"><?php echo (int)$latest['score']; ?>%</strong></div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-700/70"><div id="activeMatchBar" class="h-full rounded-full bg-gradient-to-r from-teal-300 to-cyan-300 transition-[width] duration-500" style="width:<?php echo max(0, min(100, (int)$latest['score'])); ?>%"></div></div>
                </div>
                <div class="rounded-xl border border-cyan-200/10 bg-cyan-200/[.035] p-4">
                    <div class="mb-2 flex items-center justify-between"><span class="text-sm text-slate-300">ATS compatibility</span><strong id="activeAtsScore" class="text-lg text-cyan-100"><?php echo (int)$latest['ats_score']; ?>%</strong></div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-700/70"><div id="activeAtsBar" class="h-full rounded-full bg-gradient-to-r from-sky-300 to-indigo-300 transition-[width] duration-500" style="width:<?php echo max(0, min(100, (int)$latest['ats_score'])); ?>%"></div></div>
                </div>
            </div>

            <div class="grid gap-5 border-t border-white/[.07] pt-5 lg:grid-cols-2">
                <div>
                    <h3 class="mb-3 text-sm font-semibold text-teal-100">Matched skills <span id="matchedSkillHeadingCount" class="text-slate-500">(<?php echo count($matched); ?>)</span></h3>
                    <div id="matchedSkills" class="flex flex-wrap gap-2">
                        <?php if ($matched): foreach ($matched as $skill): ?><span class="rounded-full border border-teal-200/15 bg-teal-200/[.06] px-3 py-1.5 text-xs text-teal-100"><?php echo htmlspecialchars($skill); ?></span><?php endforeach; else: ?><span class="text-sm text-slate-500">No required skills matched yet.</span><?php endif; ?>
                    </div>
                </div>
                <div>
                    <h3 class="mb-3 text-sm font-semibold text-rose-200">Skills to improve <span id="missingSkillHeadingCount" class="text-slate-500">(<?php echo count($missing); ?>)</span></h3>
                    <div id="missingSkills" class="flex flex-wrap gap-2">
                        <?php if ($missing): foreach ($missing as $skill): ?><span class="rounded-full border border-rose-200/15 bg-rose-200/[.05] px-3 py-1.5 text-xs text-rose-100"><?php echo htmlspecialchars($skill); ?></span><?php endforeach; else: ?><span class="text-sm text-slate-500">No missing skills in this analysis.</span><?php endif; ?>
                    </div>
                </div>
            </div>

            <div id="recommendationsBlock" class="<?php echo $suggestions ? 'mt-6 border-t border-white/[.07] pt-5' : 'hidden'; ?>">
                    <div class="mb-3 flex items-center gap-2"><span class="grid h-7 w-7 place-items-center rounded-lg bg-violet-200/[.08] text-violet-200" aria-hidden="true">✦</span><h3 class="text-sm font-semibold text-white">Recommendations</h3><span id="recommendationSource" class="text-xs text-slate-500">from your latest analysis</span></div>
                    <ul id="recommendationsList" class="grid gap-2 md:grid-cols-2">
                        <?php foreach (array_slice($suggestions, 0, 6) as $suggestion): ?><li class="flex items-start gap-2.5 rounded-xl border border-white/[.06] bg-white/[.025] p-3 text-sm leading-5 text-slate-300"><span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-violet-300" aria-hidden="true"></span><?php echo htmlspecialchars($suggestion); ?></li><?php endforeach; ?>
                    </ul>
            </div>
        </section>

        <?php if ($totalAnalyses > 1): ?>
        <section aria-labelledby="past-analyses-heading">
            <div class="mb-4 flex items-end justify-between gap-3"><div><h2 id="past-analyses-heading" class="text-xl font-semibold text-white">Past analyses</h2><p class="mt-1 text-sm text-slate-500">Select any result to view its full report above.</p></div><span class="text-xs text-slate-500"><?php echo number_format($totalAnalyses); ?> total</span></div>
            <div id="pastAnalysesList" class="grid gap-3">
                <?php foreach (array_slice($results, 1) as $index => $item): ?>
                    <article data-analysis-index="<?php echo $index + 1; ?>" tabindex="0" role="group" aria-label="Analysis: <?php echo htmlspecialchars($item['file_name'], ENT_QUOTES); ?>" class="past-analysis surface-card cursor-pointer rounded-2xl p-4 transition duration-300 hover:-translate-y-0.5 hover:border-teal-200/25 hover:bg-white/[.035] focus:outline-none focus:ring-2 focus:ring-teal-200/60 sm:p-5">
                        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                            <div class="min-w-0">
                                <div class="flex min-w-0 items-center gap-3"><h3 class="truncate font-semibold text-slate-100"><?php echo htmlspecialchars($item['file_name']); ?></h3><button type="button" data-select-analysis class="shrink-0 rounded-lg border border-teal-200/15 bg-teal-200/[.06] px-2.5 py-1.5 text-xs font-semibold text-teal-100 transition hover:bg-teal-200/[.14]">View analysis</button></div>
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
const keywordChart = new Chart(document.getElementById('keywordChart'), {
    type: 'doughnut',
    data: { labels: ['Matched', 'Missing'], datasets: [{ data: [<?php echo count($matched); ?>, <?php echo count($missing); ?>], backgroundColor: ['rgba(45, 212, 191, .9)', 'rgba(251, 113, 133, .82)'], hoverBackgroundColor: ['#2dd4bf', '#fb7185'], borderColor: 'rgba(17, 24, 39, .95)', borderWidth: 4, hoverOffset: 6, spacing: 2 }] },
    options: { responsive: true, maintainAspectRatio: false, cutout: '76%', plugins: { legend: { display: false }, tooltip: { ...tooltipStyle, callbacks: { label: context => ` ${context.label}: ${context.raw}` } } } }
});

const analysisBaseUrl = <?php echo json_encode(BASE_URL, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
const allAnalyses = <?php echo json_encode($results, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
let activeAnalysis = allAnalyses[0];
let pastAnalyses = allAnalyses.slice(1);
let hasSelectedAnalysis = false;

const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[char]);
const splitList = (value, separator = ',') => String(value ?? '').split(separator).map(item => item.trim()).filter(Boolean);
const analysisDate = value => {
    const date = new Date(String(value).replace(' ', 'T'));
    return Number.isNaN(date.getTime()) ? String(value ?? '') : new Intl.DateTimeFormat(undefined, { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }).format(date);
};
const resumeUrl = item => `${analysisBaseUrl}modules/resume/view.php?id=${encodeURIComponent(item.resume_id)}`;
const reportUrl = item => `${analysisBaseUrl}modules/analysis/generate_pdf.php?id=${encodeURIComponent(item.id)}`;

function renderPastAnalyses() {
    const list = document.getElementById('pastAnalysesList');
    list.innerHTML = pastAnalyses.map(item => `
        <article data-row-id="${escapeHtml(item.id)}" tabindex="0" role="group" aria-label="Analysis: ${escapeHtml(item.file_name)}" class="past-analysis surface-card cursor-pointer rounded-2xl p-4 transition duration-300 hover:-translate-y-0.5 hover:border-teal-200/25 hover:bg-white/[.035] focus:outline-none focus:ring-2 focus:ring-teal-200/60 sm:p-5">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="min-w-0">
                    <div class="flex min-w-0 items-center gap-3"><h3 class="truncate font-semibold text-slate-100">${escapeHtml(item.file_name)}</h3><button type="button" data-select-analysis class="shrink-0 rounded-lg border border-teal-200/15 bg-teal-200/[.06] px-2.5 py-1.5 text-xs font-semibold text-teal-100 transition hover:bg-teal-200/[.14]">View analysis</button></div>
                    <p class="mt-1 text-xs text-slate-500">${escapeHtml(item.position_name || 'Custom job description')} · ${escapeHtml(analysisDate(item.created_at))}</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 sm:justify-end">
                    <span class="rounded-full border border-teal-200/10 bg-teal-200/[.05] px-3 py-1.5 text-xs font-semibold text-teal-100">${Number(item.score) || 0}% match</span>
                    <span class="text-xs text-slate-400">ATS ${Number(item.ats_score) || 0}%</span>
                    <a href="${escapeHtml(resumeUrl(item))}" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-teal-200 hover:text-teal-100">Resume ↗</a>
                    <a href="${escapeHtml(reportUrl(item))}" class="text-xs font-semibold text-sky-200 hover:text-sky-100">Report ↓</a>
                </div>
            </div>
            <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-slate-700/60"><div class="h-full rounded-full bg-gradient-to-r from-teal-300 to-cyan-300" style="width:${Math.max(0, Math.min(100, Number(item.score) || 0))}%"></div></div>
        </article>`).join('');
}

function renderSkills(targetId, values, emptyText, kind) {
    const target = document.getElementById(targetId);
    const classes = kind === 'matched'
        ? 'rounded-full border border-teal-200/15 bg-teal-200/[.06] px-3 py-1.5 text-xs text-teal-100'
        : 'rounded-full border border-rose-200/15 bg-rose-200/[.05] px-3 py-1.5 text-xs text-rose-100';
    target.innerHTML = values.length
        ? values.map(skill => `<span class="${classes}">${escapeHtml(skill)}</span>`).join('')
        : `<span class="text-sm text-slate-500">${emptyText}</span>`;
}

function showAnalysis(item, animate = true) {
    const matched = splitList(item.matched_keywords);
    const missing = splitList(item.missing_keywords);
    const suggestions = splitList(item.suggestions, ' | ').slice(0, 6);
    const score = Math.max(0, Math.min(100, Number(item.score) || 0));
    const ats = Math.max(0, Math.min(100, Number(item.ats_score) || 0));
    document.getElementById('activeAnalysisLabel').textContent = hasSelectedAnalysis ? 'Selected analysis' : 'Latest analysis';
    document.getElementById('activeFileName').textContent = item.file_name || 'Resume analysis';
    document.getElementById('activeMeta').textContent = `${item.position_name || 'Custom job description'} · ${analysisDate(item.created_at)}`;
    document.getElementById('activeResumeLink').href = resumeUrl(item);
    document.getElementById('activeReportLink').href = reportUrl(item);
    document.getElementById('activeMatchScore').textContent = `${score}%`;
    document.getElementById('activeAtsScore').textContent = `${ats}%`;
    document.getElementById('activeMatchBar').style.width = `${score}%`;
    document.getElementById('activeAtsBar').style.width = `${ats}%`;
    document.getElementById('selectedMatchScore').innerHTML = `${score}<span class="text-lg">%</span>`;
    document.getElementById('selectedAtsScore').textContent = `ATS compatibility ${ats}%`;
    document.getElementById('matchedSkillHeadingCount').textContent = `(${matched.length})`;
    document.getElementById('missingSkillHeadingCount').textContent = `(${missing.length})`;
    document.getElementById('matchedCount').textContent = matched.length;
    document.getElementById('missingCount').textContent = missing.length;
    document.getElementById('keywordChartCenter').textContent = `${matched.length}/${matched.length + missing.length}`;
    renderSkills('matchedSkills', matched, 'No required skills matched yet.', 'matched');
    renderSkills('missingSkills', missing, 'No missing skills in this analysis.', 'missing');
    document.getElementById('recommendationSource').textContent = hasSelectedAnalysis ? 'from this analysis' : 'from your latest analysis';
    document.getElementById('recommendationsBlock').className = suggestions.length ? 'mt-6 border-t border-white/[.07] pt-5' : 'hidden';
    document.getElementById('recommendationsList').innerHTML = suggestions.map(text => `<li class="flex items-start gap-2.5 rounded-xl border border-white/[.06] bg-white/[.025] p-3 text-sm leading-5 text-slate-300"><span class="mt-2 h-1.5 w-1.5 shrink-0 rounded-full bg-violet-300" aria-hidden="true"></span>${escapeHtml(text)}</li>`).join('');
    keywordChart.data.datasets[0].data = [matched.length, missing.length];
    keywordChart.update();
    if (animate && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        const panel = document.getElementById('activeAnalysis');
        panel.animate([{ opacity: .58, transform: 'translateY(10px) scale(.995)' }, { opacity: 1, transform: 'translateY(0) scale(1)' }], { duration: 380, easing: 'cubic-bezier(.2,.75,.25,1)' });
    }
}

function selectAnalysis(id) {
    const selectedIndex = pastAnalyses.findIndex(item => String(item.id) === String(id));
    if (selectedIndex < 0) return;
    const list = document.getElementById('pastAnalysesList');
    const previousPositions = new Map([...list.children].map(row => [row.dataset.rowId, row.getBoundingClientRect()]));
    const [selected] = pastAnalyses.splice(selectedIndex, 1);
    pastAnalyses.unshift(activeAnalysis);
    activeAnalysis = selected;
    hasSelectedAnalysis = true;
    renderPastAnalyses();
    showAnalysis(activeAnalysis);
    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        [...list.children].forEach(row => {
            const previous = previousPositions.get(row.dataset.rowId);
            if (!previous) return;
            const current = row.getBoundingClientRect();
            const offset = previous.top - current.top;
            if (offset) row.animate([{ transform: `translateY(${offset}px)` }, { transform: 'translateY(0)' }], { duration: 420, easing: 'cubic-bezier(.2,.75,.25,1)' });
        });
    }
}

const pastList = document.getElementById('pastAnalysesList');
if (pastList) {
    renderPastAnalyses();
    pastList.addEventListener('click', event => {
        if (event.target.closest('a')) return;
        const row = event.target.closest('[data-row-id]');
        if (row) selectAnalysis(row.dataset.rowId);
    });
    pastList.addEventListener('keydown', event => {
        if (event.target.closest('a, button')) return;
        if (event.key === 'Enter' || event.key === ' ') {
            const row = event.target.closest('[data-row-id]');
            if (row) {
                event.preventDefault();
                selectAnalysis(row.dataset.rowId);
            }
        }
    });
}
</script>
<?php endif; ?>
<?php require_once __DIR__ . "/../../templates/footer.php"; ?>
