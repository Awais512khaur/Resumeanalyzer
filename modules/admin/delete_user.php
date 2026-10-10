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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "admin_users");
    exit;
}

$postedToken = $_POST['csrf_token'] ?? '';
if (!isset($_SESSION['csrf_token']) || !is_string($postedToken) || !hash_equals($_SESSION['csrf_token'], $postedToken)) {
    http_response_code(403);
    exit('Your session expired. Refresh the user list and try again.');
}

$user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$page = max(1, (int)($_POST['p'] ?? 1));
if (!$user_id || $user_id < 1) {
    header("Location: " . BASE_URL . "admin_users");
    exit;
}

$targetStatement = $pdo->prepare("SELECT name, email, role FROM users WHERE id = ?");
$targetStatement->execute([$user_id]);
$targetUser = $targetStatement->fetch();

// Admin accounts are protected from deletion.
if (!$targetUser || $targetUser['role'] === 'admin') {
    header("Location: " . BASE_URL . "index.php?page=admin_users&p=" . $page);
    exit;
}

// DELETE USER
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$user_id]);
if ($targetUser) {
    audit_log($pdo, 'user.deleted', 'Account deleted: ' . $targetUser['name'] . ' (' . $targetUser['email'] . ').', 'user', (int)$user_id);
    $_SESSION['user_admin_notice'] = 'Deleted ' . $targetUser['name'] . ' successfully.';
}

header("Location: " . BASE_URL . "index.php?page=admin_users&p=" . $page);
exit;
