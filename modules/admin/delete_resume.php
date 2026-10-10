<?php
session_start();

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/audit.php";

// 🔒 ADMIN CHECK
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL);
    exit;
}

// VALIDATE
if (!isset($_GET['id'])) {
    header("Location: " . BASE_URL . "admin_resumes");
    exit;
}

$id = $_GET['id'];

// GET FILE PATH
$stmt = $pdo->prepare("SELECT file_path, file_name FROM resumes WHERE id = ?");
$stmt->execute([$id]);
$resume = $stmt->fetch();

if ($resume) {

    $filePath = __DIR__ . "/../../" . $resume['file_path'];

    // DELETE FILE
    if (file_exists($filePath)) {
        unlink($filePath);
    }

    // DELETE DB RECORD
    $stmt = $pdo->prepare("DELETE FROM resumes WHERE id = ?");
    $stmt->execute([$id]);
    audit_log($pdo, 'resume.deleted_by_admin', 'Resume deleted by an administrator: ' . $resume['file_name'], 'resume', (int)$id);
}

header("Location: " . BASE_URL . "admin_resumes");
exit;
