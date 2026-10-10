<?php
session_start();

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/audit.php";

if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Administrator access is required.');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'admin_users');
    exit;
}

$postedToken = $_POST['csrf_token'] ?? '';
if (!isset($_SESSION['csrf_token']) || !is_string($postedToken) || !hash_equals($_SESSION['csrf_token'], $postedToken)) {
    http_response_code(403);
    exit('Your session expired. Refresh the user list and try again.');
}

$userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$page = max(1, (int)($_POST['p'] ?? 1));
if (!$userId || $userId < 1 || $userId === (int)$_SESSION['user_id']) {
    header('Location: ' . BASE_URL . 'index.php?page=admin_users&p=' . $page);
    exit;
}

$statement = $pdo->prepare("SELECT id, name, email, status, role FROM users WHERE id = ?");
$statement->execute([$userId]);
$target = $statement->fetch(PDO::FETCH_ASSOC);
if (!$target || $target['role'] !== 'user') {
    header('Location: ' . BASE_URL . 'index.php?page=admin_users&p=' . $page);
    exit;
}

$newStatus = (int)$target['status'] === 1 ? 0 : 1;
$update = $pdo->prepare("UPDATE users SET status = ? WHERE id = ? AND role = 'user'");
$update->execute([$newStatus, $userId]);

$action = $newStatus === 1 ? 'user.unlocked' : 'user.locked';
$state = $newStatus === 1 ? 'unlocked' : 'locked';
audit_log($pdo, $action, 'Account ' . $state . ': ' . $target['name'] . ' (' . $target['email'] . ').', 'user', (int)$userId);
$_SESSION['user_admin_notice'] = ucfirst($state) . ' ' . $target['name'] . "'s account successfully.";

header('Location: ' . BASE_URL . 'index.php?page=admin_users&p=' . $page);
exit;
