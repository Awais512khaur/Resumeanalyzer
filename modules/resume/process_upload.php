<?php
require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/audit.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "upload");
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

if (!isset($_FILES['resume']) || (int)$_FILES['resume']['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($_FILES['resume']['tmp_name'])) {
    $_SESSION['error'] = "File upload failed";
    header("Location: " . BASE_URL . "upload");
    exit;
}

$maxResumeBytes = 10 * 1024 * 1024;
if ((int)$_FILES['resume']['size'] < 1 || (int)$_FILES['resume']['size'] > $maxResumeBytes) {
    $_SESSION['error'] = 'Choose a PDF no larger than 10 MB.';
    header('Location: ' . BASE_URL . 'upload');
    exit;
}

// Validate type
$fileInfo = new finfo(FILEINFO_MIME_TYPE);
$fileType = $fileInfo->file($_FILES['resume']['tmp_name']);
$signature = file_get_contents($_FILES['resume']['tmp_name'], false, null, 0, 5);
if ($fileType !== 'application/pdf' || $signature !== '%PDF-') {
    $_SESSION['error'] = "Only PDF files allowed";
    header("Location: " . BASE_URL . "upload");
    exit;
}

// Reject an identical PDF already stored for this user, even if its filename differs.
$uploadHash = hash_file('sha256', $_FILES['resume']['tmp_name']);
$existingResumes = $pdo->prepare("SELECT file_path FROM resumes WHERE user_id = ?");
$existingResumes->execute([(int)($_SESSION['user_id'] ?? 0)]);
foreach ($existingResumes->fetchAll(PDO::FETCH_COLUMN) as $existingFilePath) {
    $existingFile = __DIR__ . "/../../uploads/" . basename((string)$existingFilePath);
    $existingHash = is_file($existingFile) ? hash_file('sha256', $existingFile) : false;
    if ($uploadHash && $existingHash && hash_equals($uploadHash, $existingHash)) {
        $_SESSION['upload_toast'] = 'This resume has already been uploaded. You can find it in your resumes.';
        header("Location: " . BASE_URL . "upload");
        exit;
    }
}

// Create uploads folder
$uploadDir = __DIR__ . "/../../uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0750, true);
}

// File name
$originalName = basename(str_replace('\\', '/', (string)$_FILES['resume']['name']));
$originalName = trim((string)preg_replace('/[\x00-\x1F\x7F]/u', '', $originalName));
$fileName = mb_substr($originalName !== '' ? $originalName : 'Resume.pdf', 0, 255);
$storageName = bin2hex(random_bytes(16)) . '.pdf';
$filePath = $uploadDir . $storageName;

// Move file
if (!move_uploaded_file($_FILES['resume']['tmp_name'], $filePath)) {
    $_SESSION['error'] = 'The resume could not be saved. Please try again.';
    header('Location: ' . BASE_URL . 'upload');
    exit;
}

// Save in DB
$stmt = $pdo->prepare("INSERT INTO resumes (user_id, file_name, file_path, file_size) VALUES (?, ?, ?, ?)");
$stmt->execute([(int)$_SESSION['user_id'], $fileName, 'uploads/' . $storageName, (int)$_FILES['resume']['size']]);
$resumeId = (int)$pdo->lastInsertId();
audit_log($pdo, 'resume.uploaded', 'Resume uploaded: ' . $fileName, 'resume', $resumeId);

// ✅ FIXED REDIRECT
header("Location: " . BASE_URL . "resumes");
exit;
