<?php
// 🔒 PREVENT BACK CACHE
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/position_skills.php";
require_once __DIR__ . "/../../templates/header.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login");
    exit;
}

// OPTIONAL: PRESELECT RESUME
$resume_id = $_GET['resume_id'] ?? '';

// GET USER RESUMES
$stmt = $pdo->prepare("SELECT id, file_name FROM resumes WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$_SESSION['user_id']]);
$resumes = $stmt->fetchAll();

// GET POSITIONS
$stmt = $pdo->query("SELECT id, name, description, required_skills FROM positions ORDER BY name ASC");
$positions = $stmt->fetchAll();
foreach ($positions as &$position) {
    $position['skills_list'] = get_position_required_skills($position);
}
unset($position);
?>

<div class="max-w-4xl mx-auto px-4 py-4">

    <div class="flex items-center justify-between mb-6 flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold flex items-center gap-2 text-white">
                Resume Analyzer
                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-sm">
                    ✨ ATS & AI Powered
                </span>
            </h1>
            <p class="text-slate-300 text-sm mt-1">Match resumes against job descriptions, bullet points, or skill lists with case-insensitive ATS keyword parsing and Gemini AI insights.</p>
        </div>
    </div>

    <form id="analyzeForm" action="<?php echo BASE_URL; ?>modules/analysis/process_analysis.php" method="POST" onsubmit="return handleAnalyzeSubmit()" class="space-y-5">
        <input type="hidden" name="ct" id="csrfB64" value="">
        <input type="hidden" name="jd" id="jobDescriptionB64" value="">
        <input type="hidden" name="ri" id="resumeIdB64" value="">
        <input type="hidden" name="pi" id="positionIdB64" value="">

        <!-- SELECT RESUME -->
        <div>
            <label class="block text-sm font-semibold mb-1.5 text-slate-200">Select Resume <span class="text-rose-400">*</span></label>
            <select id="resumeSelect" required class="w-full p-3 rounded-xl bg-white/10 border border-white/15 text-white focus:outline-none focus:ring-2 focus:ring-teal-400 transition">
                <option value="" class="text-black">Choose a resume to analyze</option>

                <?php foreach ($resumes as $resume): ?>
                    <option 
                        class="text-black"
                        value="<?php echo (int)$resume['id']; ?>"
                        <?php echo ($resume_id == $resume['id']) ? 'selected' : ''; ?>
                    >
                        <?php echo htmlspecialchars($resume['file_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if (empty($resumes)): ?>
                <p class="text-xs text-amber-200 mt-1.5">You haven't uploaded any resumes yet. <a href="<?php echo BASE_URL; ?>upload" class="underline hover:text-white">Upload a resume first</a>.</p>
            <?php endif; ?>
        </div>

        <!-- TARGET POSITION DROPDOWN (OPTIONAL) -->
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label class="block text-sm font-semibold text-slate-200">Target Position (Optional)</label>
                <span class="text-xs text-slate-400">Pre-loads standard role skills</span>
            </div>
            <select id="positionSelect" class="w-full p-3 rounded-xl bg-white/10 border border-white/15 text-white focus:outline-none focus:ring-2 focus:ring-teal-400 transition">
                <option value="" class="text-black">None (Custom Job Requirements)</option>

                <?php foreach ($positions as $pos): ?>
                    <option class="text-black" value="<?php echo (int)$pos['id']; ?>"
                        data-required-skills="<?php echo htmlspecialchars(json_encode($pos['skills_list'], JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>">
                        <?php echo htmlspecialchars($pos['name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- SKILLS PREVIEW FOR SELECTED POSITION -->
        <div id="requiredSkillsPreview" class="hidden rounded-xl border border-teal-200/20 bg-teal-500/[.07] p-4">
            <p class="text-xs font-bold uppercase tracking-wider text-teal-200">Skills defined for selected position</p>
            <ul id="requiredSkillsText" class="mt-2.5 flex flex-wrap gap-2"></ul>
        </div>

        <!-- JOB DESCRIPTION / REQUIRED SKILLS TEXTAREA -->
        <div>
            <div class="flex items-center justify-between mb-1.5 flex-wrap gap-1">
                <label class="block text-sm font-semibold text-slate-200">
                    Job Description / Required Skills <span class="text-rose-400">*</span>
                </label>
                <span id="charCounter" class="text-xs text-slate-400">0 characters</span>
            </div>
            <p class="text-xs text-slate-300 mb-2 leading-relaxed">
                Paste a job description, sentences, bullet points, or enter required skills. The analyzer automatically identifies skills case-insensitively.
            </p>
            <textarea id="jobDescription" rows="7" required
                placeholder="Paste job description or requirements here... For example:
• Looking for a developer with 3+ years experience in PHP, MySQL, and JavaScript
• Strong knowledge of React, Tailwind CSS, and RESTful APIs
• Experience deploying with Docker on AWS cloud"
                class="w-full p-3.5 rounded-xl bg-white/10 border border-white/15 text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-teal-400 font-mono text-sm leading-relaxed transition resize-y"></textarea>
        </div>

        <button type="submit" id="analyzeBtn"
            class="w-full bg-gradient-to-r from-teal-500 to-cyan-500 hover:from-teal-400 hover:to-cyan-400 text-slate-950 p-4 rounded-xl font-bold flex items-center justify-center gap-2 transition cursor-pointer shadow-lg shadow-teal-900/30 hover:shadow-teal-900/50 hover:-translate-y-0.5">
            <span id="btnIcon" class="text-lg">⚡</span>
            <span id="btnText" class="tracking-wide text-base">Analyze Resume & Generate AI Insights</span>
        </button>

    </form>

</div>

<script>
const positionSelect = document.getElementById('positionSelect');
const skillsPreview = document.getElementById('requiredSkillsPreview');
const skillsText = document.getElementById('requiredSkillsText');
const jobDescription = document.getElementById('jobDescription');
const charCounter = document.getElementById('charCounter');
let autoFilledSkills = '';

function updateCharCount() {
    const len = jobDescription.value.length;
    charCounter.textContent = `${len.toLocaleString()} characters`;
}

jobDescription.addEventListener('input', function() {
    this.setCustomValidity('');
    updateCharCount();
});

positionSelect.addEventListener('change', function () {
    const selectedOption = this.options[this.selectedIndex];
    const skills = JSON.parse(selectedOption?.dataset?.requiredSkills || '[]');
    const skillsString = skills.join(', ');

    skillsText.replaceChildren();
    if (this.value && skills.length) {
        skills.forEach(skill => {
            const item = document.createElement('li');
            item.className = 'rounded-full border border-teal-200/20 bg-teal-200/[.10] px-3 py-1 text-xs font-medium text-teal-100';
            item.textContent = skill;
            skillsText.appendChild(item);
        });
        skillsPreview.classList.remove('hidden');
    } else {
        skillsPreview.classList.add('hidden');
    }

    if (skillsString && (jobDescription.value.trim() === '' || jobDescription.value === autoFilledSkills)) {
        jobDescription.value = skillsString;
        autoFilledSkills = skillsString;
        updateCharCount();
    }
});

function b64(str) {
    try { return btoa(unescape(encodeURIComponent(str))); }
    catch(e) { return btoa(str); }
}

function handleAnalyzeSubmit() {
    if (!jobDescription.value.trim()) {
        jobDescription.focus();
        jobDescription.setCustomValidity('Please enter or paste job requirements/skills.');
        jobDescription.reportValidity();
        return false;
    }
    jobDescription.setCustomValidity('');

    // Base64-encode ALL form fields to bypass InfinityFree mod_security WAF
    document.getElementById('csrfB64').value = b64('<?php echo $_SESSION['csrf_token']; ?>');
    document.getElementById('jobDescriptionB64').value = b64(jobDescription.value);
    document.getElementById('resumeIdB64').value = b64(document.getElementById('resumeSelect').value);
    document.getElementById('positionIdB64').value = b64(positionSelect.value || '0');

    const btn = document.getElementById('analyzeBtn');
    const btnText = document.getElementById('btnText');
    const btnIcon = document.getElementById('btnIcon');
    
    btn.disabled = true;
    btn.classList.add('opacity-75', 'cursor-wait');
    btnIcon.innerHTML = `<svg class="animate-spin h-5 w-5 text-slate-950" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>`;
    btnText.textContent = 'Parsing Requirements & Analyzing with Gemini AI...';
    return true;
}

// Reset button and prevent Firefox bfcache issues
window.addEventListener('pageshow', function (event) {
    const btn = document.getElementById('analyzeBtn');
    const btnText = document.getElementById('btnText');
    const btnIcon = document.getElementById('btnIcon');
    if (btn) {
        btn.disabled = false;
        btn.classList.remove('opacity-75', 'cursor-wait');
    }
    if (btnText) btnText.textContent = 'Analyze Resume & Generate AI Insights';
    if (btnIcon) btnIcon.textContent = '⚡';
    if (event.persisted) {
        window.location.reload();
    }
});
</script>

<?php require_once __DIR__ . "/../../templates/footer.php"; ?>