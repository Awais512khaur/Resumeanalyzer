<?php
require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/audit.php";

// 🔒 ADMIN CHECK
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !resume_csrf_is_valid($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Invalid request.');
}
// VALIDATE
if (!filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT)) {
    header("Location: " . BASE_URL . "admin_resumes");
    exit;
}

$id = (int)$_POST['id'];

// GET FILE PATH
$stmt = $pdo->prepare("SELECT file_path, file_name FROM resumes WHERE id = ?");
$stmt->execute([$id]);
$resume = $stmt->fetch();

if ($resume) {

    $storageRoot = realpath(__DIR__ . '/../../uploads');
    $filePath = realpath(__DIR__ . '/../../' . $resume['file_path']);

    // DELETE FILE
    if ($storageRoot && $filePath && str_starts_with($filePath, $storageRoot . DIRECTORY_SEPARATOR) && is_file($filePath)) {
        unlink($filePath);
    }

    // DELETE DB RECORD
    $stmt = $pdo->prepare("DELETE FROM resumes WHERE id = ?");
    $stmt->execute([$id]);
    audit_log($pdo, 'resume.deleted_by_admin', 'Resume deleted by an administrator: ' . $resume['file_name'], 'resume', (int)$id);
}

header("Location: " . BASE_URL . "admin_resumes");
exit;
