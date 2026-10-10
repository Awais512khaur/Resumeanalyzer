<?php
require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/audit.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !resume_csrf_is_valid($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Invalid request.');
}
if (!isset($_SESSION['user_id']) || !filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT)) {
    header("Location: " . BASE_URL . "resumes");
    exit;
}

$id = (int)$_POST['id'];

// GET FILE
$stmt = $pdo->prepare("SELECT * FROM resumes WHERE id = ?");
$stmt->execute([$id]);
$resume = $stmt->fetch();

if ($resume && (int)$resume['user_id'] !== (int)($_SESSION['user_id'] ?? 0) && ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('You do not have permission to delete this resume.');
}

if ($resume) {
    $storageRoot = realpath(__DIR__ . '/../../uploads');
    $filePath = realpath(__DIR__ . '/../../' . $resume['file_path']);
    if ($storageRoot && $filePath && str_starts_with($filePath, $storageRoot . DIRECTORY_SEPARATOR) && is_file($filePath)) {
        unlink($filePath);
    }

    // delete from DB
    $stmt = $pdo->prepare("DELETE FROM resumes WHERE id = ?");
    $stmt->execute([$id]);
    audit_log($pdo, 'resume.deleted', 'Resume deleted: ' . $resume['file_name'], 'resume', (int)$id);
}

header("Location: " . BASE_URL . "resumes");
exit;
