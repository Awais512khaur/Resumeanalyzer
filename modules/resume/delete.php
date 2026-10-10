<?php
session_start();

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/audit.php";

if (!isset($_GET['id'])) {
    header("Location: " . BASE_URL . "resumes");
    exit;
}

$id = $_GET['id'];

// GET FILE
$stmt = $pdo->prepare("SELECT * FROM resumes WHERE id = ?");
$stmt->execute([$id]);
$resume = $stmt->fetch();

if ($resume && (int)$resume['user_id'] !== (int)($_SESSION['user_id'] ?? 0) && ($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('You do not have permission to delete this resume.');
}

if ($resume) {
    $filePath = __DIR__ . "/../../" . $resume['file_path'];

    if (file_exists($filePath)) {
        unlink($filePath); // delete file
    }

    // delete from DB
    $stmt = $pdo->prepare("DELETE FROM resumes WHERE id = ?");
    $stmt->execute([$id]);
    audit_log($pdo, 'resume.deleted', 'Resume deleted: ' . $resume['file_name'], 'resume', (int)$id);
}

header("Location: " . BASE_URL . "resumes");
exit;
