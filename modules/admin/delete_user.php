<?php
session_start();

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";

// 🔒 ADMIN CHECK
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL);
    exit;
}

// VALIDATE ID
if (!isset($_GET['id'])) {
    header("Location: " . BASE_URL . "admin_users");
    exit;
}

$user_id = $_GET['id'];

// PREVENT SELF DELETE
if ($user_id == $_SESSION['user_id']) {
    header("Location: " . BASE_URL . "admin_users");
    exit;
}

// DELETE USER
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$user_id]);

header("Location: " . BASE_URL . "admin_users");
exit;