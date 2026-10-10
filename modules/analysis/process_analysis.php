<?php
require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/audit.php";
require_once __DIR__ . "/ai_service.php";
require_once __DIR__ . "/position_skills.php";

// VALIDATE INPUT
if (!isset($_POST['resume_id'])) {
    header("Location: " . BASE_URL . "analyze");
    exit;
}
if (empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . 'login');
    exit;
}
if (!resume_csrf_is_valid($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Your session expired. Refresh the page and try again.');
}

// INPUT
$resume_id = filter_var($_POST['resume_id'], FILTER_VALIDATE_INT);
if (!$resume_id || $resume_id < 1) {
    http_response_code(400);
    exit('Invalid resume.');
}
$original_job_description = trim($_POST['job_description'] ?? '');
if (strlen($original_job_description) > 30000) {
    http_response_code(413);
    exit('The job description is too long. Keep it under 30,000 characters.');
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

// Use the explicitly selected role, or the resume's saved role for admin analysis.
$position_id = null;
if (array_key_exists('position_id', $_POST)) {
    $postedPositionId = trim((string)$_POST['position_id']);
    if ($postedPositionId !== '' && ctype_digit($postedPositionId) && (int)$postedPositionId > 0) {
        $position_id = (int)$postedPositionId;
    }
} elseif (!empty($resume['position'])) {
    $position_id = (int)$resume['position'];
}

$position = null;
if ($position_id !== null) {
    $stmt = $pdo->prepare("SELECT id, name, description, required_skills FROM positions WHERE id = ?");
    $stmt->execute([$position_id]);
    $position = $stmt->fetch();
    if (!$position) {
        $position_id = null;
    }
}

// Remember the selected role alongside this resume for later admin review.
$stmt = $pdo->prepare("UPDATE resumes SET position = ? WHERE id = ?");
$stmt->execute([$position_id, $resume_id]);

// FILE PATH
$uploadRoot = realpath(__DIR__ . '/../../uploads');
$filePath = realpath(__DIR__ . '/../../' . (string)$resume['file_path']);
if (!$uploadRoot || !$filePath || !str_starts_with($filePath, $uploadRoot . DIRECTORY_SEPARATOR) || !is_file($filePath)) {
    die("Resume file not found");
}

// PDF TEXT EXTRACTION
$pdftotext = getenv('PDFTOTEXT_PATH') ?: __DIR__ . "/../../tools/poppler/Library/bin/pdftotext.exe";
if (!is_file($pdftotext) && function_exists('shell_exec')) {
    $pdftotext = trim((string)shell_exec('command -v pdftotext 2>/dev/null'));
}

if (!file_exists($pdftotext)) {
    die("PDF text extraction is not configured on this server.");
}

$command = escapeshellarg($pdftotext) . ' -f 1 -l 20 ' . escapeshellarg($filePath) . ' -';
$text = shell_exec($command);
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

if (!$hasReadableText) {
    die("This PDF has no extractable text. Install Tesseract OCR or upload a text-based PDF, then analyze it again.");
}

$raw_resume_text = $text;

// Normalize punctuation consistently while keeping common skill characters (C++, C#, Node.js).
$normalizeForMatch = static function ($value) {
    $value = strtolower((string)$value);
    $value = preg_replace('/[^a-z0-9+#.]+/i', ' ', $value);
    return trim(preg_replace('/\s+/', ' ', $value));
};
$resume_text = $normalizeForMatch($text);
$normalizedResume = ' ' . $resume_text . ' ';

// When the selected position has skills, score directly against those skill phrases.
$requiredSkills = $position ? get_position_required_skills($position) : [];

if ($requiredSkills) {
    $matched = [];
    $missing = [];
    foreach ($requiredSkills as $skill) {
        $normalizedSkill = $normalizeForMatch($skill);
        $isMatched = $normalizedSkill !== '' && strpos($normalizedResume, ' ' . $normalizedSkill . ' ') !== false;
        // A resume often lists a major version (for example HTML5) while the role says HTML.
        if (!$isMatched && preg_match('/^[a-z][a-z0-9+#]*$/i', $normalizedSkill)) {
            $isMatched = (bool)preg_match('/(?:^|\s)' . preg_quote($normalizedSkill, '/') . '\d+(?:\s|$)/i', $resume_text);
        }
        if ($isMatched) {
            $matched[] = $skill;
        } else {
            $missing[] = $skill;
        }
    }
    $score = round((count($matched) / count($requiredSkills)) * 100);
    $analysis_job_context = $original_job_description . "\n\nRequired skills to evaluate: " . implode(', ', $requiredSkills);
} else {
    // Preserve the existing job-description keyword analysis when no skills are configured.
    $normalizedDescription = $normalizeForMatch($original_job_description);
    $stop_words = ["the","and","is","in","at","of","a","to","for","with","on","by","an","be","or","as","are","from","that","this","will"];
    $resume_words = array_diff(array_unique(explode(' ', $resume_text)), $stop_words);
    $job_words = array_diff(array_unique(explode(' ', $normalizedDescription)), $stop_words);
    $resume_words = array_values(array_filter($resume_words));
    $job_words = array_values(array_filter($job_words));
    $matched = array_values(array_intersect($resume_words, $job_words));
    $missing = array_values(array_diff($job_words, $resume_words));
    $score = count($job_words) > 0 ? round((count($matched) / count($job_words)) * 100) : 0;
    $analysis_job_context = $original_job_description;
}

// ATS SCORE
$ats_score = 0;

if (strlen($resume_text) > 1000) $ats_score += 20;
if (strpos($resume_text, "experience") !== false) $ats_score += 20;
if (strpos($resume_text, "skills") !== false) $ats_score += 20;
if (strpos($resume_text, "education") !== false) $ats_score += 20;
if ($score > 60) $ats_score += 20;

$ats_score = min(100, $ats_score);

// 🤖 GENERATE AI SUGGESTIONS
$suggestions = generate_ai_suggestions(
    $raw_resume_text,
    $analysis_job_context,
    $matched,
    $missing,
    $score,
    $ats_score
);

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
audit_log(
    $pdo,
    'resume.analyzed',
    'Resume analyzed with a match score of ' . (int)$score . '% and ATS score of ' . (int)$ats_score . '%.',
    'analysis',
    $analysisId
);

// REDIRECT
header("Location: " . BASE_URL . "history");
exit;
