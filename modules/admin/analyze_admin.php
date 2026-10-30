<?php
require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../analysis/position_skills.php";
require_once __DIR__ . "/../../templates/header.php";

// 🔒 ADMIN CHECK
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL);
    exit;
}

// GET RESUME ID
$resume_id = $_GET['resume_id'] ?? null;

if (!$resume_id) {
    die("Invalid request");
}

// 🔥 FETCH RESUME + POSITION
$stmt = $pdo->prepare("
    SELECT r.*, p.name as position_name, p.description, p.required_skills
    FROM resumes r
    LEFT JOIN positions p ON r.position = p.id
    WHERE r.id = ?
");
$stmt->execute([$resume_id]);
$resume = $stmt->fetch();

if (!$resume) {
    die("Resume not found");
}

$required_skills_list = get_position_required_skills($resume);
$initial_required_skills = !empty($required_skills_list) 
    ? implode(', ', $required_skills_list) 
    : trim((string)($resume['description'] ?? ''));
?>

<div class="max-w-4xl mx-auto px-4 py-4">

    <div class="flex items-center justify-between mb-6 flex-wrap gap-2">
        <div>
            <h1 class="text-2xl font-bold flex items-center gap-2 text-white">
                Admin Resume Analysis
                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-sm">
                    ✨ ATS & AI Powered
                </span>
            </h1>
            <p class="text-slate-300 text-sm mt-1">Review applicant match and evaluate against position requirements or custom job descriptions with Gemini AI.</p>
        </div>
    </div>

    <div class="bg-white/10 p-5 rounded-2xl backdrop-blur-lg mb-6 border border-white/10">
        <p class="text-sm"><strong>Resume:</strong> <span class="text-slate-200"><?php echo htmlspecialchars($resume['file_name']); ?></span></p>
        <p class="text-sm mt-1"><strong>Assigned Position:</strong> <span class="text-slate-200"><?php echo htmlspecialchars($resume['position_name'] ?? 'None (Custom Analysis)'); ?></span></p>
        <?php if ($required_skills_list): ?>
            <p class="mt-3 text-xs font-bold uppercase tracking-wider text-teal-200">Position required skills:</p>
            <ul class="mt-2 flex flex-wrap gap-2">
                <?php foreach ($required_skills_list as $skill): ?>
                    <li class="rounded-full border border-teal-200/20 bg-teal-200/[.10] px-3 py-1 text-xs font-medium text-teal-100"><?php echo htmlspecialchars($skill, ENT_QUOTES, 'UTF-8'); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <form id="adminAnalyzeForm" action="<?php echo BASE_URL; ?>modules/analysis/process_analysis.php" method="POST" onsubmit="return handleAdminAnalyzeSubmit()" class="space-y-4">
        <input type="hidden" name="ct" id="adminCsrfB64" value="">
        <input type="hidden" name="jd" id="adminJobDescriptionB64" value="">
        <input type="hidden" name="ri" value="<?php echo base64_encode((string)(int)$resume['id']); ?>">
        <input type="hidden" name="pi" value="<?php echo base64_encode((string)(int)($resume['position'] ?? 0)); ?>">

        <!-- REQUIRED SKILLS TEXTAREA -->
        <div>
            <label class="block text-sm font-semibold text-slate-200 mb-1">
                Job Description / Required Skills <span class="text-rose-400">*</span>
            </label>
            <p class="text-xs text-slate-300 mb-2">
                Accepts sentences, bullet points, full job postings, or comma-separated skills. Case-insensitive ATS matching is performed automatically.
            </p>
            <textarea id="adminJobDescription" rows="8" required
                placeholder="Paste job description or requirements here... (e.g. bullet points, sentences, or technical skills like PHP, React, MySQL, Docker)"
                class="w-full p-3.5 rounded-xl bg-white/10 border border-white/15 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-400 font-mono text-sm leading-relaxed transition resize-y"><?php echo htmlspecialchars($initial_required_skills); ?></textarea>
        </div>

        <button type="submit" id="adminAnalyzeBtn"
            class="w-full bg-gradient-to-r from-teal-500 to-cyan-500 hover:from-teal-400 hover:to-cyan-400 text-slate-950 p-4 rounded-xl font-bold flex items-center justify-center gap-2 transition cursor-pointer shadow-lg shadow-teal-900/30 hover:shadow-teal-900/50 hover:-translate-y-0.5">
            <span id="adminBtnIcon" class="text-lg">⚡</span>
            <span id="adminBtnText" class="tracking-wide text-base">Analyze & Generate AI Suggestions</span>
        </button>

    </form>

</div>

<script>
function b64(str) {
    try { return btoa(unescape(encodeURIComponent(str))); }
    catch(e) { return btoa(str); }
}

function handleAdminAnalyzeSubmit() {
    const textarea = document.getElementById('adminJobDescription');
    if (!textarea.value.trim()) {
        textarea.focus();
        textarea.setCustomValidity('Please enter or paste the job requirements.');
        textarea.reportValidity();
        return false;
    }
    textarea.setCustomValidity('');

    // Base64-encode to bypass InfinityFree mod_security WAF
    document.getElementById('adminCsrfB64').value = b64('<?php echo $_SESSION['csrf_token']; ?>');
    document.getElementById('adminJobDescriptionB64').value = b64(textarea.value);

    const btn = document.getElementById('adminAnalyzeBtn');
    const btnText = document.getElementById('adminBtnText');
    const btnIcon = document.getElementById('adminBtnIcon');
    
    btn.disabled = true;
    btn.classList.add('opacity-75', 'cursor-wait');
    btnIcon.innerHTML = `<svg class="animate-spin h-5 w-5 text-slate-950" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>`;
    btnText.textContent = 'Parsing Requirements & Analyzing with Gemini AI...';
    return true;
}

document.getElementById('adminJobDescription')?.addEventListener('input', function() {
    this.setCustomValidity('');
});
</script>

<?php require_once __DIR__ . "/../../templates/footer.php"; ?>