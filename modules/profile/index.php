<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/audit.php';

$userId = (int)($_SESSION['user_id'] ?? 0);
if ($userId < 1) {
    header('Location: ' . BASE_URL . 'login');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postedToken = $_POST['csrf_token'] ?? '';
    if (!is_string($postedToken) || !hash_equals($_SESSION['csrf_token'], $postedToken)) {
        $_SESSION['profile_password_error'] = 'Your session expired. Refresh the page and try again.';
    } else {
        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_new_password'] ?? '');

        if ($currentPassword === '' || $newPassword === '' || $confirmPassword === '') {
            $_SESSION['profile_password_error'] = 'Enter your current password and both new password fields.';
        } elseif (strlen($newPassword) < 12) {
            $_SESSION['profile_password_error'] = 'Your new password must contain at least 12 characters.';
        } elseif (!hash_equals($newPassword, $confirmPassword)) {
            $_SESSION['profile_password_error'] = 'The new passwords do not match.';
        } else {
            $passwordQuery = $pdo->prepare('SELECT password FROM users WHERE id = ?');
            $passwordQuery->execute([$userId]);
            $storedPassword = (string)$passwordQuery->fetchColumn();

            if ($storedPassword === '' || !password_verify($currentPassword, $storedPassword)) {
                $_SESSION['profile_password_error'] = 'Your current password does not match the password on your account.';
            } elseif (password_verify($newPassword, $storedPassword)) {
                $_SESSION['profile_password_error'] = 'Choose a new password that is different from your current password.';
            } else {
                $updatePassword = $pdo->prepare('UPDATE users SET password = ?, failed_login_count = 0, failed_login_until = NULL WHERE id = ?');
                $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
                $updatePassword->execute([$newHash, $userId]);
                $_SESSION['auth_password_hash'] = $newHash;
                session_regenerate_id(true);
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                audit_log($pdo, 'account.password_changed', 'The user changed their password from the profile page.', 'user', $userId);
                $_SESSION['profile_password_notice'] = 'Your password has been updated successfully.';
            }
        }
    }

    header('Location: ' . BASE_URL . 'profile');
    exit;
}

$passwordNotice = (string)($_SESSION['profile_password_notice'] ?? '');
$passwordError = (string)($_SESSION['profile_password_error'] ?? '');
unset($_SESSION['profile_password_notice'], $_SESSION['profile_password_error']);

$profileQuery = $pdo->prepare('SELECT id, name, email, created_at FROM users WHERE id = ?');
$profileQuery->execute([$userId]);
$profile = $profileQuery->fetch();
if (!$profile) {
    end_resume_session();
    header('Location: ' . BASE_URL . 'login');
    exit;
}

$resumeQuery = $pdo->prepare(
    'SELECT r.id, r.file_name, r.created_at,
            COUNT(ar.id) AS analysis_count,
            (SELECT latest.score FROM analysis_results latest WHERE latest.resume_id = r.id ORDER BY latest.id DESC LIMIT 1) AS latest_match,
            (SELECT latest.ats_score FROM analysis_results latest WHERE latest.resume_id = r.id ORDER BY latest.id DESC LIMIT 1) AS latest_ats
     FROM resumes r
     LEFT JOIN analysis_results ar ON ar.resume_id = r.id
     WHERE r.user_id = ?
     GROUP BY r.id, r.file_name, r.created_at
     ORDER BY r.id DESC'
);
$resumeQuery->execute([$userId]);
$resumes = $resumeQuery->fetchAll();

$analysisQuery = $pdo->prepare(
    'SELECT ar.id, ar.resume_id, ar.score, ar.ats_score, ar.created_at, r.file_name
     FROM analysis_results ar
     JOIN resumes r ON r.id = ar.resume_id
     WHERE r.user_id = ?
     ORDER BY ar.id ASC'
);
$analysisQuery->execute([$userId]);
$analyses = $analysisQuery->fetchAll();

$resumeCount = count($resumes);
$analysisCount = count($analyses);
$averageMatch = $analysisCount ? (int)round(array_sum(array_map('intval', array_column($analyses, 'score'))) / $analysisCount) : 0;
$averageAts = $analysisCount ? (int)round(array_sum(array_map('intval', array_column($analyses, 'ats_score'))) / $analysisCount) : 0;

$analysisLabels = [];
$analysisMatches = [];
foreach ($analyses as $analysis) {
    $analysisLabels[] = date('d M', strtotime($analysis['created_at'])) . ' · ' . $analysis['file_name'];
    $analysisMatches[] = (int)$analysis['score'];
}
$resumeLabels = array_map(static fn($resume) => $resume['file_name'], $resumes);
$resumeMatches = array_map(static fn($resume) => $resume['latest_match'] === null ? null : (int)$resume['latest_match'], $resumes);
$resumeAts = array_map(static fn($resume) => $resume['latest_ats'] === null ? null : (int)$resume['latest_ats'], $resumes);
$jsonFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;

require_once __DIR__ . '/../../templates/header.php';
?>
<main class="mx-auto w-full max-w-7xl px-4 pb-14 pt-6 sm:px-6 lg:px-8">
    <section class="surface-card mb-6 flex flex-col gap-5 rounded-3xl p-6 sm:flex-row sm:items-center sm:p-8">
        <div class="grid h-16 w-16 shrink-0 place-items-center rounded-2xl border border-teal-200/20 bg-gradient-to-br from-teal-200/15 to-sky-300/10 text-2xl font-bold text-teal-100" aria-hidden="true">
            <?php echo htmlspecialchars(strtoupper(substr($profile['name'], 0, 1)), ENT_QUOTES, 'UTF-8'); ?>
        </div>
        <div class="min-w-0 flex-1">
            <p class="text-xs font-semibold uppercase tracking-[.18em] text-teal-200">Your profile</p>
            <h1 class="mt-1 truncate text-3xl font-bold text-white"><?php echo htmlspecialchars($profile['name'], ENT_QUOTES, 'UTF-8'); ?></h1>
            <p class="mt-1 break-all text-sm text-slate-400"><?php echo htmlspecialchars($profile['email'], ENT_QUOTES, 'UTF-8'); ?></p>
        </div>
        <a href="<?php echo BASE_URL; ?>upload" class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-[#9af2d5] to-[#67d9f3] px-4 py-3 text-sm font-bold text-[#06131a]">Upload resume <span aria-hidden="true">→</span></a>
    </section>

    <section class="surface-card mb-8 flex flex-col gap-4 rounded-2xl p-5 sm:flex-row sm:items-center sm:justify-between sm:p-7" aria-labelledby="change-password-heading">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[.16em] text-teal-200">Account security</p>
            <h2 id="change-password-heading" class="mt-1 text-xl font-bold text-white">Password</h2>
            <p class="mt-1 text-sm text-slate-400">Change your password using your current password.</p>
            <?php if ($passwordNotice !== ''): ?>
                <p class="mt-3 text-sm text-teal-100" role="status" aria-live="polite">✓ <?php echo htmlspecialchars($passwordNotice, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
        </div>
        <button id="openPasswordModal" type="button" class="shrink-0 rounded-xl bg-gradient-to-r from-[#9af2d5] to-[#67d9f3] px-5 py-3 text-sm font-bold text-[#06131a]">Reset password</button>
    </section>

    <dialog id="passwordModal" class="m-auto w-[calc(100%-2rem)] max-w-xl rounded-2xl border border-white/10 bg-[#101a2b] p-0 text-white shadow-2xl shadow-black/50 backdrop:bg-slate-950/80 backdrop:backdrop-blur-sm">
        <div class="flex items-start justify-between gap-4 border-b border-white/10 p-5 sm:p-6">
            <div><p class="text-xs font-semibold uppercase tracking-[.16em] text-teal-200">Account security</p><h2 class="mt-1 text-xl font-bold">Reset password</h2><p class="mt-1 text-sm text-slate-400">Enter your current password, then choose a new one.</p></div>
            <form method="dialog"><button type="submit" aria-label="Close" class="grid h-9 w-9 shrink-0 place-items-center rounded-lg border border-white/10 text-slate-300 hover:bg-white/[.06]">×</button></form>
        </div>
        <div class="p-5 sm:p-6">
            <?php if ($passwordError !== ''): ?>
                <div class="mb-5 rounded-xl border border-rose-200/20 bg-rose-200/[.06] px-4 py-3 text-sm text-rose-100" role="alert" aria-live="assertive">! <?php echo htmlspecialchars($passwordError, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <form action="<?php echo BASE_URL; ?>profile" method="POST" autocomplete="off" class="grid gap-4">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <div>
                    <label for="currentPassword" class="mb-1.5 block text-sm font-medium text-slate-300">Current password</label>
                    <div class="relative"><input id="currentPassword" name="current_password" type="password" autocomplete="off" required class="w-full rounded-xl border border-white/10 bg-slate-950/40 px-4 py-3 pr-16 text-white placeholder:text-slate-500" placeholder="Enter current password"><button type="button" data-password-toggle aria-controls="currentPassword" aria-label="Show password" class="absolute inset-y-0 right-3 my-auto h-9 rounded-lg px-2 text-sm font-semibold text-teal-200 hover:bg-white/[.06]">Show</button></div>
                </div>
                <div>
                    <label for="newPassword" class="mb-1.5 block text-sm font-medium text-slate-300">New password</label>
                    <div class="relative"><input id="newPassword" name="new_password" type="password" minlength="12" autocomplete="new-password" required class="w-full rounded-xl border border-white/10 bg-slate-950/40 px-4 py-3 pr-16 text-white placeholder:text-slate-500" placeholder="At least 12 characters"><button type="button" data-password-toggle aria-controls="newPassword" aria-label="Show password" class="absolute inset-y-0 right-3 my-auto h-9 rounded-lg px-2 text-sm font-semibold text-teal-200 hover:bg-white/[.06]">Show</button></div>
                </div>
                <div>
                    <label for="confirmNewPassword" class="mb-1.5 block text-sm font-medium text-slate-300">Confirm new password</label>
                    <div class="relative"><input id="confirmNewPassword" name="confirm_new_password" type="password" minlength="12" autocomplete="new-password" required class="w-full rounded-xl border border-white/10 bg-slate-950/40 px-4 py-3 pr-16 text-white placeholder:text-slate-500" placeholder="Re-enter new password"><button type="button" data-password-toggle aria-controls="confirmNewPassword" aria-label="Show password" class="absolute inset-y-0 right-3 my-auto h-9 rounded-lg px-2 text-sm font-semibold text-teal-200 hover:bg-white/[.06]">Show</button></div>
                </div>
                <button type="submit" class="mt-1 rounded-xl bg-gradient-to-r from-[#9af2d5] to-[#67d9f3] px-5 py-3 text-sm font-bold text-[#06131a]">Save new password</button>
            </form>
        </div>
    </dialog>

    <section class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4" aria-label="Profile statistics">
        <article class="stat-card"><h3>Resumes uploaded</h3><p class="stat-value"><?php echo number_format($resumeCount); ?></p></article>
        <article class="stat-card"><h3>Analyses completed</h3><p class="stat-value"><?php echo number_format($analysisCount); ?></p></article>
        <article class="stat-card"><h3>Average match score</h3><p class="stat-value"><?php echo $averageMatch; ?>%</p></article>
        <article class="stat-card"><h3>Average ATS score</h3><p class="stat-value"><?php echo $averageAts; ?>%</p></article>
    </section>

    <section class="mb-8" aria-labelledby="profile-resumes-heading">
        <div class="mb-4 flex items-end justify-between gap-3">
            <div><p class="text-xs font-semibold uppercase tracking-[.16em] text-teal-200">Your documents</p><h2 id="profile-resumes-heading" class="mt-1 text-2xl font-bold text-white">Uploaded resumes</h2></div>
            <span class="text-sm text-slate-500"><?php echo number_format($resumeCount); ?> total</span>
        </div>
        <?php if ($resumes): ?>
            <div class="app-table-wrap">
                <table class="w-full text-left">
                    <thead><tr><th class="px-4">Resume</th><th class="px-4">Uploaded</th><th class="px-4">Analyses</th><th class="px-4">Latest match</th><th class="px-4">Latest ATS</th><th class="px-4">Action</th></tr></thead>
                    <tbody>
                    <?php foreach ($resumes as $resume): ?>
                        <tr class="border-b border-white/10">
                            <td class="max-w-[20rem] truncate px-4 font-medium text-slate-100"><?php echo htmlspecialchars($resume['file_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="px-4 text-sm text-slate-400"><?php echo htmlspecialchars(date('d M Y', strtotime($resume['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="px-4"><?php echo number_format((int)$resume['analysis_count']); ?></td>
                            <td class="px-4 font-semibold text-teal-100"><?php echo $resume['latest_match'] === null ? '—' : (int)$resume['latest_match'] . '%'; ?></td>
                            <td class="px-4 font-semibold text-cyan-100"><?php echo $resume['latest_ats'] === null ? '—' : (int)$resume['latest_ats'] . '%'; ?></td>
                            <td class="px-4"><a href="<?php echo BASE_URL; ?>modules/resume/view.php?id=<?php echo (int)$resume['id']; ?>" target="_blank" rel="noopener noreferrer" class="inline-flex rounded-lg border border-teal-200/15 bg-teal-200/[.06] px-3 py-2 text-sm font-semibold text-teal-100 hover:bg-teal-200/[.12]">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="surface-card rounded-2xl px-6 py-10 text-center">
                <p class="font-semibold text-white">No resumes uploaded yet</p>
                <p class="mt-2 text-sm text-slate-400">Upload a PDF to see your resume and analysis details here.</p>
                <a href="<?php echo BASE_URL; ?>upload" class="mt-5 inline-flex rounded-xl bg-gradient-to-r from-[#9af2d5] to-[#67d9f3] px-4 py-2.5 text-sm font-bold text-[#06131a]">Upload your first resume</a>
            </div>
        <?php endif; ?>
    </section>

    <section class="mb-8" aria-labelledby="profile-analysis-heading">
        <div class="mb-4 flex items-end justify-between gap-3">
            <div><p class="text-xs font-semibold uppercase tracking-[.16em] text-cyan-200">Scores and results</p><h2 id="profile-analysis-heading" class="mt-1 text-2xl font-bold text-white">Analysis history</h2></div>
            <span class="text-sm text-slate-500"><?php echo number_format($analysisCount); ?> results</span>
        </div>
        <?php if ($analyses): ?>
            <div class="app-table-wrap">
                <table class="w-full text-left">
                    <thead><tr><th class="px-4">Resume</th><th class="px-4">Date</th><th class="px-4">Match score</th><th class="px-4">ATS score</th><th class="px-4">Resume</th></tr></thead>
                    <tbody>
                    <?php foreach (array_reverse($analyses) as $analysis): ?>
                        <tr class="border-b border-white/10">
                            <td class="max-w-[20rem] truncate px-4 font-medium text-slate-100"><?php echo htmlspecialchars($analysis['file_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="px-4 text-sm text-slate-400"><?php echo htmlspecialchars(date('d M Y, h:i A', strtotime($analysis['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                            <td class="px-4 font-semibold text-teal-100"><?php echo (int)$analysis['score']; ?>%</td>
                            <td class="px-4 font-semibold text-cyan-100"><?php echo (int)$analysis['ats_score']; ?>%</td>
                            <td class="px-4"><a href="<?php echo BASE_URL; ?>modules/resume/view.php?id=<?php echo (int)$analysis['resume_id']; ?>" target="_blank" rel="noopener noreferrer" class="text-sm font-semibold text-teal-200 hover:text-teal-100">View resume ↗</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="surface-card rounded-2xl px-6 py-8 text-center text-sm text-slate-400">Your analysis results will appear here after you analyze a resume.</div>
        <?php endif; ?>
    </section>

    <section aria-labelledby="profile-chart-heading">
        <div class="mb-4"><p class="text-xs font-semibold uppercase tracking-[.16em] text-violet-200">Your progress</p><h2 id="profile-chart-heading" class="mt-1 text-2xl font-bold text-white">Resume performance</h2></div>
        <div class="grid gap-5 xl:grid-cols-2">
            <article class="surface-card rounded-2xl p-5 sm:p-6">
                <h3 class="font-semibold text-white">Match score across analyses</h3>
                <p class="mt-1 text-xs text-slate-500">Each result for all your uploaded resumes</p>
                <div class="relative mt-4 h-[280px]"><canvas id="profileAnalysisChart" role="img" aria-label="Match scores across all resume analyses"></canvas></div>
            </article>
            <article class="surface-card rounded-2xl p-5 sm:p-6">
                <h3 class="font-semibold text-white">Latest scores by resume</h3>
                <p class="mt-1 text-xs text-slate-500">Most recent match and ATS score for each resume</p>
                <div class="relative mt-4 h-[280px]"><canvas id="profileResumeChart" role="img" aria-label="Latest match and ATS scores for each resume"></canvas></div>
            </article>
        </div>
    </section>
</main>
<script>
const passwordModal = document.getElementById('passwordModal');
const currentPasswordInput = document.getElementById('currentPassword');
document.getElementById('openPasswordModal')?.addEventListener('click', () => {
    passwordModal?.showModal();
});
passwordModal?.addEventListener('click', event => {
    if (event.target === passwordModal) passwordModal.close();
});
<?php if ($passwordError !== ''): ?>
passwordModal?.showModal();
<?php endif; ?>
const profileChartFont = { family: 'Inter, ui-sans-serif, system-ui, sans-serif' };
const profileTooltip = { backgroundColor: 'rgba(8,13,27,.96)', borderColor: 'rgba(148,163,184,.2)', borderWidth: 1, titleColor: '#f8fafc', bodyColor: '#cbd5e1', padding: 11, bodyFont: profileChartFont };
new Chart(document.getElementById('profileAnalysisChart'), {
    type: 'line',
    data: { labels: <?php echo json_encode($analysisLabels, $jsonFlags); ?>, datasets: [{ label: 'Match score', data: <?php echo json_encode($analysisMatches, $jsonFlags); ?>, borderColor: '#67e8d3', backgroundColor: 'rgba(103,232,211,.12)', pointBackgroundColor: '#9af2d5', pointRadius: 3, borderWidth: 2, tension: .35, fill: true }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false }, tooltip: profileTooltip }, scales: { x: { grid: { display: false }, ticks: { color: '#8090a8', maxTicksLimit: 7, font: { ...profileChartFont, size: 10 } } }, y: { min: 0, max: 100, ticks: { color: '#71809a', stepSize: 25, callback: value => value + '%' }, grid: { color: 'rgba(148,163,184,.09)' } } } }
});
new Chart(document.getElementById('profileResumeChart'), {
    type: 'bar',
    data: { labels: <?php echo json_encode($resumeLabels, $jsonFlags); ?>, datasets: [{ label: 'Match', data: <?php echo json_encode($resumeMatches, $jsonFlags); ?>, backgroundColor: 'rgba(45,212,191,.78)', borderRadius: 6 }, { label: 'ATS', data: <?php echo json_encode($resumeAts, $jsonFlags); ?>, backgroundColor: 'rgba(56,189,248,.78)', borderRadius: 6 }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { labels: { color: '#aab6c8', usePointStyle: true, pointStyle: 'circle' } }, tooltip: profileTooltip }, scales: { x: { grid: { display: false }, ticks: { color: '#8090a8', maxTicksLimit: 6, font: { ...profileChartFont, size: 10 } } }, y: { min: 0, max: 100, ticks: { color: '#71809a', stepSize: 25, callback: value => value + '%' }, grid: { color: 'rgba(148,163,184,.09)' } } } }
});
</script>
<script src="<?php echo BASE_URL; ?>assets/js/password-toggle.js" defer></script>
<?php require_once __DIR__ . '/../../templates/footer.php'; ?>
