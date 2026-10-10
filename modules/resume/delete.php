<?php
session_start();

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";

if (!isset($_GET['id'])) {
    header("Location: " . BASE_URL . "resumes");
    exit;
}

$id = $_GET['id'];

// GET FILE
$stmt = $pdo->prepare("SELECT * FROM resumes WHERE id = ?");
$stmt->execute([$id]);
$resume = $stmt->fetch();

if ($resume) {
    $filePath = __DIR__ . "/../../" . $resume['file_path'];

    if (file_exists($filePath)) {
        unlink($filePath); // delete file
    }

    // delete from DB
    $stmt = $pdo->prepare("DELETE FROM resumes WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: " . BASE_URL . "resumes");
exit;