<?php
require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/audit.php";
require_once __DIR__ . "/engine.php";
require_once __DIR__ . "/ai_service.php";
require_once __DIR__ . "/position_skills.php";

// Extend execution time limit for AI API calls (InfinityFree may cap at 30s)
@set_time_limit(120);

// ── DECODE BASE64-ENCODED FORM FIELDS (bypass InfinityFree mod_security WAF) ──
// The analyze form encodes all fields with short names: ct, jd, ri, pi
$_DECODED = [];
foreach (['ct' => 'csrf_token', 'jd' => 'job_description', 'ri' => 'resume_id', 'pi' => 'position_id'] as $short => $full) {
    $raw = trim($_POST[$short] ?? '');
    if ($raw !== '') {
        $dec = base64_decode($raw, true);
        $_DECODED[$full] = ($dec !== false) ? $dec : $raw;
    }
}
// Fallback: also accept raw field names (for local/non-WAF servers or admin form)
foreach (['csrf_token', 'job_description', 'resume_id', 'position_id'] as $key) {
    if (!isset($_DECODED[$key]) || $_DECODED[$key] === '') {
        $_DECODED[$key] = $_POST[$key] ?? '';
    }
}
// Also support the old job_description_b64 field name
if (($_DECODED['job_description'] ?? '') === '') {
    $oldB64 = trim($_POST['job_description_b64'] ?? '');
    if ($oldB64 !== '') {
        $dec = base64_decode($oldB64, true);
        if ($dec !== false) $_DECODED['job_description'] = $dec;
    }
}

// VALIDATE INPUT
if (empty($_DECODED['resume_id'])) {
    header("Location: " . BASE_URL . "analyze");
    exit;
}
if (empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'login');
    exit;
}
if (!resume_csrf_is_valid($_DECODED['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Your session expired. Refresh the page and try again.');
}

// RESUME ID
$resume_id = filter_var($_DECODED['resume_id'], FILTER_VALIDATE_INT);
if (!$resume_id || $resume_id < 1) {
    http_response_code(400);
    exit('Invalid resume.');
}

// GET RESUME
$stmt = $pdo->prepare("SELECT * FROM resumes WHERE id = ?");
$stmt->execute([$resume_id]);
$resume = $stmt->fetch();

if (!$resume) {
    die("Resume not found");
}

// Users may analyze only their own resumes. Admin analysis can use any resume.
if (($_SESSION['role'] ?? 'user') !== 'admin' && (int)$resume['user_id'] !== (int)($_SESSION['user_id'] ?? 0)) {
    http_response_code(403);
    exit('You do not have permission to analyze this resume.');
}

// OPTIONAL POSITION
$position_id = null;
$postedPositionId = trim((string)($_DECODED['position_id'] ?? ''));
if ($postedPositionId !== '' && ctype_digit($postedPositionId) && (int)$postedPositionId > 0) {
    $position_id = (int)$postedPositionId;
} elseif (!empty($resume['position'])) {
    $position_id = (int)$resume['position'];
}

$position = null;
$positionSkills = [];
if ($position_id !== null) {
    $stmt = $pdo->prepare("SELECT id, name, description, required_skills FROM positions WHERE id = ?");
    $stmt->execute([$position_id]);
    $position = $stmt->fetch();
    if ($position) {
        $positionSkills = get_position_required_skills($position);
        // Persist associated position with resume
        $stmtUpdate = $pdo->prepare("UPDATE resumes SET position = ? WHERE id = ?");
        $stmtUpdate->execute([$position_id, $resume_id]);
    } else {
        $position_id = null;
    }
}

// JOB REQUIREMENTS INPUT (already decoded from Base64 above)
$original_job_description = trim((string)($_DECODED['job_description'] ?? ''));
if (strlen($original_job_description) > 30000) {
    http_response_code(413);
    exit('The job requirements input is too long. Keep it under 30,000 characters.');
}

// PRE-EXTRACT SKILLS (FROM SENTENCES, BULLET POINTS, OR COMMA/LINE LISTS)
$preExtractedSkills = ResumeAnalysisEngine::extractSkills($original_job_description, $positionSkills);

if (empty($preExtractedSkills) && $original_job_description === '') {
    http_response_code(400);
    exit('Please provide job requirements or select a position with required skills.');
}

// FILE PATH
$uploadRoot = realpath(__DIR__ . '/../../uploads');
$filePath = realpath(__DIR__ . '/../../' . (string)$resume['file_path']);
if (!$uploadRoot || !$filePath || !str_starts_with($filePath, $uploadRoot . DIRECTORY_SEPARATOR) || !is_file($filePath)) {
    die("Resume file not found");
}

// PDF TEXT EXTRACTION
$text = '';
$pdftotext = getenv('PDFTOTEXT_PATH') ?: __DIR__ . "/../../tools/poppler/Library/bin/pdftotext.exe";
if (!is_file($pdftotext) && function_exists('shell_exec')) {
    $pdftotext = trim((string)shell_exec('command -v pdftotext 2>/dev/null'));
}

if (is_file($pdftotext) && function_exists('shell_exec')) {
    $command = escapeshellarg($pdftotext) . ' -f 1 -l 20 ' . escapeshellarg($filePath) . ' -';
    $text = (string)shell_exec($command);
}

// Pure-PHP Parser Fallback (essential for shared hosting / InfinityFree where shell_exec is disabled)
if (!preg_match('/[a-z0-9]{2,}/i', (string)$text) && is_file(__DIR__ . '/../../vendor/autoload.php')) {
    try {
        require_once __DIR__ . '/../../vendor/autoload.php';
        if (class_exists(\Smalot\PdfParser\Parser::class)) {
            $pdfParser = new \Smalot\PdfParser\Parser();
            $parsedPdf = $pdfParser->parseFile($filePath);
            $extractedText = $parsedPdf->getText();
            if (is_string($extractedText) && trim($extractedText) !== '') {
                $text = $extractedText;
            }
        }
    } catch (\Throwable $e) {
        // Continue to OCR or fallback
    }
}

$stderrRedirect = PHP_OS_FAMILY === 'Windows' ? '2>NUL' : '2>/dev/null';

// Some resumes are image-only PDFs. If Poppler cannot extract readable text,
// use Tesseract OCR when it is installed and render each page through Poppler.
$hasReadableText = is_string($text) && preg_match('/[a-z0-9]{2,}/i', $text);
if (!$hasReadableText) {
    $popplerDir = dirname($pdftotext);
    $pdftoppm = getenv('PDFTOPPM_PATH') ?: $popplerDir . DIRECTORY_SEPARATOR . 'pdftoppm.exe';
    if (!is_file($pdftoppm) && function_exists('shell_exec')) {
        $pdftoppm = trim((string)shell_exec('command -v pdftoppm 2>/dev/null'));
    }
    $tesseractCandidates = array_filter([
        getenv('TESSERACT_PATH') ?: null,
        __DIR__ . "/../../tools/tesseract/tesseract.exe",
        'C:\\Program Files\\Tesseract-OCR\\tesseract.exe',
        'C:\\Program Files (x86)\\Tesseract-OCR\\tesseract.exe',
        function_exists('shell_exec') ? trim((string)shell_exec('command -v tesseract 2>/dev/null')) : null
    ]);
    $tesseract = null;
    foreach ($tesseractCandidates as $candidate) {
        if (is_file($candidate)) {
            $tesseract = $candidate;
            break;
        }
    }

    if ($tesseract && is_file($pdftoppm)) {
        $ocrDir = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'resume-ocr-' . bin2hex(random_bytes(8));
        if (mkdir($ocrDir, 0700)) {
            $imagePrefix = $ocrDir . DIRECTORY_SEPARATOR . 'page';
            $renderCommand = escapeshellarg($pdftoppm) . ' -f 1 -l 20 -png -r 180 ' . escapeshellarg($filePath) . ' ' . escapeshellarg($imagePrefix) . ' ' . $stderrRedirect;
            shell_exec($renderCommand);
            $ocrText = [];
            foreach (glob($imagePrefix . '-*.png') ?: [] as $pageImage) {
                $ocrCommand = escapeshellarg($tesseract) . ' ' . escapeshellarg($pageImage) . ' stdout -l eng ' . $stderrRedirect;
                $pageText = shell_exec($ocrCommand);
                if (is_string($pageText) && trim($pageText) !== '') {
                    $ocrText[] = $pageText;
                }
                unlink($pageImage);
            }
            rmdir($ocrDir);
            if ($ocrText) {
                $text = implode("\n", $ocrText);
                $hasReadableText = preg_match('/[a-z0-9]{2,}/i', $text);
            }
        }
    }
}

// Shared hosts may not have local OCR binaries; fall back to Gemini PDF OCR.
if (!$hasReadableText) {
    $text = extract_resume_pdf_text_with_gemini($filePath);
    $hasReadableText = is_string($text) && preg_match('/[a-z0-9]{2,}/i', $text);
}

if (!$hasReadableText) {
    require_once __DIR__ . '/../../templates/header.php';
    ?>
    <div class="max-w-xl mx-auto px-4 py-16 text-center">
        <div class="p-8 rounded-2xl bg-white/10 border border-white/15 text-white backdrop-blur-lg shadow-xl">
            <span class="text-4xl mb-3 block" aria-hidden="true">📄⚠️</span>
            <h2 class="text-xl font-bold mb-2">Scanned / Image-Based PDF Detected</h2>
            <p class="text-sm text-slate-300 mb-4 leading-relaxed">
                This resume file appears to be a scanned image or photograph without selectable digital text. Standard text-based PDFs (saved directly from Microsoft Word, Google Docs, or Canva) can be analyzed instantly.
            </p>
            <p class="text-xs text-amber-200/90 mb-6 leading-relaxed bg-amber-500/10 p-3 rounded-xl border border-amber-500/20">
                To transcribe scanned/image PDFs automatically, add your free Google Gemini API key to <code>.env</code> or <code>config/config.php</code>.
            </p>
            <div class="flex flex-wrap justify-center gap-3">
                <a href="<?php echo BASE_URL; ?>upload" class="px-5 py-2.5 rounded-xl bg-gradient-to-r from-teal-400 to-cyan-400 text-slate-950 font-bold text-sm hover:opacity-90 transition">Upload a Text PDF</a>
                <a href="<?php echo BASE_URL; ?>analyze" class="px-5 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 text-white font-semibold text-sm transition">Back to Analyze</a>
            </div>
        </div>
    </div>
    <?php
    require_once __DIR__ . '/../../templates/footer.php';
    exit;
}

$raw_resume_text = $text;

// 🤖 COMPREHENSIVE AI-DRIVEN RESUME ANALYSIS (GEMINI AI + ATS PARSING ENGINE)
$requirementsForAi = $original_job_description !== '' ? $original_job_description : implode(', ', $preExtractedSkills);
$aiResult = analyze_resume_with_ai($raw_resume_text, $requirementsForAi, $preExtractedSkills);

$score = $aiResult['score'];
$ats_score = $aiResult['ats_score'];
$matched = $aiResult['matched'];
$missing = $aiResult['missing'];
$suggestions = $aiResult['suggestions'];

// If AI returns empty matched/missing skills, fall back to extracted skills matching
if (empty($matched) && empty($missing) && !empty($preExtractedSkills)) {
    $fallbackMatch = ResumeAnalysisEngine::matchSkills($preExtractedSkills, $raw_resume_text);
    $matched = $fallbackMatch['matched'];
    $missing = $fallbackMatch['missing'];
}

// SAVE RESULT
$stmt = $pdo->prepare("
    INSERT INTO analysis_results 
    (resume_id, score, ats_score, matched_keywords, missing_keywords, suggestions)
    VALUES (?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $resume_id,
    $score,
    $ats_score,
    implode(",", $matched),
    implode(",", $missing),
    implode(" | ", $suggestions)
]);
$analysisId = (int)$pdo->lastInsertId();

$engineLabel = !empty($aiResult['is_ai_powered']) ? 'Gemini AI' : 'ATS Engine';
audit_log(
    $pdo,
    'resume.analyzed',
    "Resume analyzed via {$engineLabel} with Match Score {$score}% and ATS Score {$ats_score}%.",
    'analysis',
    $analysisId
);

// REDIRECT TO HISTORY REPORT
header("Location: " . BASE_URL . "history");
exit;
