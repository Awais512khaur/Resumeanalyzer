<?php
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login");
    exit;
}
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../templates/header.php";

$user_role = $_SESSION['role'] ?? 'user';
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'] ?? 'there';

$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM resumes WHERE user_id = ?");
$stmt->execute([$user_id]);
$total_resumes = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->prepare("SELECT COUNT(*) AS total FROM analysis_results ar JOIN resumes r ON ar.resume_id = r.id WHERE r.user_id = ?");
$stmt->execute([$user_id]);
$total_analysis = $stmt->fetch()['total'] ?? 0;

$stmt = $pdo->prepare("SELECT AVG(score) AS avg_score FROM analysis_results ar JOIN resumes r ON ar.resume_id = r.id WHERE r.user_id = ?");
$stmt->execute([$user_id]);
$avg_score = round($stmt->fetch()['avg_score'] ?? 0);
?>
<main class="mx-auto w-full max-w-7xl px-4 pb-12 sm:px-6 lg:px-8">
  <section class="relative mb-8 overflow-hidden rounded-3xl border border-teal-200/10 bg-gradient-to-br from-[#17283b] via-[#111d32] to-[#10192c] px-6 py-8 shadow-2xl sm:px-9 sm:py-10">
    <div class="pointer-events-none absolute -right-12 -top-24 h-72 w-72 rounded-full bg-teal-300/10 blur-3xl" aria-hidden="true"></div>
    <div class="pointer-events-none absolute right-24 top-12 h-36 w-36 rounded-full border border-teal-100/10" aria-hidden="true"></div>
    <div class="relative max-w-2xl">
      <p class="mb-3 inline-flex items-center gap-2 text-xs font-bold uppercase tracking-[.2em] text-teal-200"><span class="h-1.5 w-1.5 rounded-full bg-teal-200 shadow-[0_0_12px_#56e6c4]"></span> Your ResumeAI workspace</p>
      <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Welcome back, <?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?>.</h1>
      <p class="mt-3 max-w-xl leading-7 text-slate-400">Make your next application stronger with clear resume insights and role-specific feedback.</p>
      <a href="<?php echo BASE_URL; ?>upload" class="mt-6 inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#9af2d5] to-[#67d9f3] px-5 py-3 font-bold text-[#06131a] shadow-lg shadow-teal-900/20 transition hover:-translate-y-0.5 hover:shadow-teal-500/20">Start with a resume <span aria-hidden="true">→</span></a>
    </div>
  </section>

  <section aria-label="Resume statistics" class="mb-9 grid gap-4 sm:grid-cols-3">
    <article class="stat-card"><div class="flex items-start justify-between"><h2>Total resumes</h2><span class="grid h-9 w-9 place-items-center rounded-xl border border-cyan-200/10 bg-cyan-200/[.06] text-cyan-200" aria-hidden="true">▤</span></div><p><?php echo (int)$total_resumes; ?></p><span class="mt-1 block text-xs text-slate-500">Uploaded to your workspace</span></article>
    <article class="stat-card"><div class="flex items-start justify-between"><h2>Analyses completed</h2><span class="grid h-9 w-9 place-items-center rounded-xl border border-indigo-200/10 bg-indigo-200/[.06] text-indigo-200" aria-hidden="true">✳</span></div><p><?php echo (int)$total_analysis; ?></p><span class="mt-1 block text-xs text-slate-500">Resume reviews so far</span></article>
    <article class="stat-card"><div class="flex items-start justify-between"><h2>Average match score</h2><span class="grid h-9 w-9 place-items-center rounded-xl border border-teal-200/10 bg-teal-200/[.06] text-teal-200" aria-hidden="true">↗</span></div><p class="!text-teal-200"><?php echo (int)$avg_score; ?><span class="text-lg">%</span></p><span class="mt-1 block text-xs text-slate-500">Across your resume analyses</span></article>
  </section>

  <section aria-labelledby="quick-actions-heading">
    <div class="mb-4 flex items-end justify-between gap-3"><div><p class="text-xs font-bold uppercase tracking-[.18em] text-teal-200/80">Keep moving</p><h2 id="quick-actions-heading" class="mt-1 text-xl font-bold">Quick actions</h2></div></div>
    <div class="grid gap-4 md:grid-cols-3">
      <?php if ($user_role === 'admin'): ?>
      <a href="<?php echo BASE_URL; ?>admin_positions" class="card group"><span class="mb-4 grid h-11 w-11 place-items-center rounded-xl border border-indigo-200/10 bg-indigo-200/[.06] text-xl text-indigo-200">⌘</span><h3 class="text-lg font-semibold">Manage positions</h3><p class="mt-2 text-sm leading-6 text-slate-400">Add and maintain the roles used for resume matching.</p><span class="mt-4 inline-block text-sm font-semibold text-teal-200">Open positions <span aria-hidden="true">→</span></span></a>
      <?php endif; ?>
      <a href="<?php echo BASE_URL; ?>upload" class="card group"><span class="mb-4 grid h-11 w-11 place-items-center rounded-xl border border-teal-200/10 bg-teal-200/[.06] text-xl text-teal-200">↑</span><h3 class="text-lg font-semibold">Upload a resume</h3><p class="mt-2 text-sm leading-6 text-slate-400">Add a new resume to your workspace and get ready to review it.</p><span class="mt-4 inline-block text-sm font-semibold text-teal-200">Upload resume <span aria-hidden="true">→</span></span></a>
      <a href="<?php echo BASE_URL; ?>analyze" class="card group"><span class="mb-4 grid h-11 w-11 place-items-center rounded-xl border border-cyan-200/10 bg-cyan-200/[.06] text-xl text-cyan-200">✦</span><h3 class="text-lg font-semibold">Analyze for a role</h3><p class="mt-2 text-sm leading-6 text-slate-400">Compare resume skills with the position you want.</p><span class="mt-4 inline-block text-sm font-semibold text-teal-200">Start analysis <span aria-hidden="true">→</span></span></a>
      <?php if ($user_role === 'user'): ?>
      <a href="<?php echo BASE_URL; ?>history" class="card group"><span class="mb-4 grid h-11 w-11 place-items-center rounded-xl border border-sky-200/10 bg-sky-200/[.06] text-xl text-sky-200">◷</span><h3 class="text-lg font-semibold">Review your history</h3><p class="mt-2 text-sm leading-6 text-slate-400">See previous match scores and recommendations.</p><span class="mt-4 inline-block text-sm font-semibold text-teal-200">View history <span aria-hidden="true">→</span></span></a>
      <?php else: ?>
      <a href="<?php echo BASE_URL; ?>admin_analysis" class="card group"><span class="mb-4 grid h-11 w-11 place-items-center rounded-xl border border-sky-200/10 bg-sky-200/[.06] text-xl text-sky-200">▥</span><h3 class="text-lg font-semibold">System analytics</h3><p class="mt-2 text-sm leading-6 text-slate-400">Explore platform activity and score distributions.</p><span class="mt-4 inline-block text-sm font-semibold text-teal-200">View analytics <span aria-hidden="true">→</span></span></a>
      <?php endif; ?>
      <a href="<?php echo BASE_URL; ?>resumes" class="card group"><span class="mb-4 grid h-11 w-11 place-items-center rounded-xl border border-violet-200/10 bg-violet-200/[.06] text-xl text-violet-200">▤</span><h3 class="text-lg font-semibold">Your resume library</h3><p class="mt-2 text-sm leading-6 text-slate-400">Manage the resumes you have uploaded.</p><span class="mt-4 inline-block text-sm font-semibold text-teal-200">View resumes <span aria-hidden="true">→</span></span></a>
    </div>
  </section>
</main>
<style>
.stat-card { min-height:9.5rem; }
@media (prefers-reduced-motion: no-preference) { main section { animation: dashboard-enter .55s ease-out both; } main section:nth-child(2) { animation-delay:.08s; } main section:nth-child(3) { animation-delay:.16s; } }
@keyframes dashboard-enter { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
</style>
<?php require_once __DIR__ . "/../../templates/footer.php"; ?>
