<?php
session_start();

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "upload");
    exit;
}

if (!isset($_FILES['resume']) || $_FILES['resume']['error'] !== 0) {
    $_SESSION['error'] = "File upload failed";
    header("Location: " . BASE_URL . "upload");
    exit;
}

// Validate type
$fileType = mime_content_type($_FILES['resume']['tmp_name']);
if ($fileType !== "application/pdf") {
    $_SESSION['error'] = "Only PDF files allowed";
    header("Location: " . BASE_URL . "upload");
    exit;
}

// Create uploads folder
$uploadDir = __DIR__ . "/../../uploads/";
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// File name
$fileName = time() . "_" . basename($_FILES['resume']['name']);
$filePath = $uploadDir . $fileName;

// Move file
move_uploaded_file($_FILES['resume']['tmp_name'], $filePath);

// Save in DB
$stmt = $pdo->prepare("INSERT INTO resumes (user_id, file_name, file_path) VALUES (?, ?, ?)");
$stmt->execute([
    $_SESSION['user_id'],
    $fileName,
    "uploads/" . $fileName
]);

// ✅ FIXED REDIRECT
header("Location: " . BASE_URL . "resumes");
exit;