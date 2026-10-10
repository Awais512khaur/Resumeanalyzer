<?php
session_start();

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
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
    SELECT r.*, p.name as position_name, p.description
    FROM resumes r
    LEFT JOIN positions p ON r.position = p.id
    WHERE r.id = ?
");
$stmt->execute([$resume_id]);
$resume = $stmt->fetch();

if (!$resume) {
    die("Resume not found");
}

// AUTO JOB DESCRIPTION FROM POSITION
$job_description = $resume['description'] ?? '';
?>

<div class="max-w-4xl mx-auto px-4">

    <div class="flex items-center justify-between mb-6 flex-wrap gap-2">
        <div>
            <h2 class="text-2xl font-bold flex items-center gap-2">
                Admin Resume Analysis
                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-0.5 rounded-full bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-sm">
                    ✨ AI Powered
                </span>
            </h2>
            <p class="text-gray-300 text-sm mt-1">Review applicant match and generate Gemini AI suggestions against position requirements.</p>
        </div>
    </div>

    <div class="bg-white/10 p-6 rounded-xl backdrop-blur-lg mb-6">
        <p><strong>Resume:</strong> <?php echo htmlspecialchars($resume['file_name']); ?></p>
        <p><strong>Position:</strong> <?php echo htmlspecialchars($resume['position_name'] ?? 'N/A'); ?></p>
    </div>

    <form id="adminAnalyzeForm" action="<?php echo BASE_URL; ?>modules/analysis/process_analysis.php" method="POST" onsubmit="return handleAdminAnalyzeSubmit()">

        <!-- HIDDEN RESUME ID -->
        <input type="hidden" name="resume_id" value="<?php echo $resume['id']; ?>">

        <!-- JOB DESCRIPTION AUTO FILLED -->
        <label class="block text-sm font-medium mb-1 text-gray-200">Job Description / Requirements:</label>
        <textarea name="job_description" rows="8"
            class="w-full p-3 rounded mb-4 bg-white/20 border border-white/10 text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
            required><?php echo htmlspecialchars($job_description); ?></textarea>

        <button type="submit" id="adminAnalyzeBtn"
            class="w-full bg-green-600 hover:bg-green-700 p-3.5 rounded-lg font-bold flex items-center justify-center gap-2 transition cursor-pointer shadow-lg">
            <span id="adminBtnIcon">⚡</span>
            <span id="adminBtnText">Analyze & Generate AI Suggestions</span>
        </button>

    </form>

</div>

<script>
function handleAdminAnalyzeSubmit() {
    const btn = document.getElementById('adminAnalyzeBtn');
    const btnText = document.getElementById('adminBtnText');
    const btnIcon = document.getElementById('adminBtnIcon');
    
    btn.disabled = true;
    btn.classList.add('opacity-75', 'cursor-wait');
    btnIcon.innerHTML = `<svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>`;
    btnText.textContent = 'Analyzing Resume with Gemini AI...';
    return true;
}
</script>

<?php require_once __DIR__ . "/../../templates/footer.php"; ?>