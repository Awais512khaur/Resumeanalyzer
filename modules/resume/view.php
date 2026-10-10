<?php
session_start();

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/audit.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login");
    exit;
}

$resumeId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$resumeId || $resumeId < 1) {
    http_response_code(400);
    exit('Invalid resume.');
}

$statement = $pdo->prepare("SELECT id, user_id, file_name, file_path FROM resumes WHERE id = ?");
$statement->execute([$resumeId]);
$resume = $statement->fetch();
if (!$resume || (($_SESSION['role'] ?? '') !== 'admin' && (int)$resume['user_id'] !== (int)$_SESSION['user_id'])) {
    http_response_code(404);
    exit('Resume not found.');
}

$safeName = basename((string)$resume['file_path']);
$filePath = __DIR__ . "/../../uploads/" . $safeName;
if (!is_file($filePath)) {
    http_response_code(404);
    exit('Resume file not found.');
}

audit_log($pdo, 'resume.viewed', 'Resume opened: ' . $resume['file_name'], 'resume', (int)$resume['id']);
header('Content-Type: application/pdf');
header('Content-Length: ' . filesize($filePath));
header('Content-Disposition: inline; filename="' . str_replace(['"', "\r", "\n"], '', basename((string)$resume['file_name'])) . '"');
header('X-Content-Type-Options: nosniff');
readfile($filePath);
exit;
