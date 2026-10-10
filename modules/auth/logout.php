<?php
session_start();

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/audit.php";

if (isset($_SESSION['user_id'])) {
    audit_log($pdo, 'auth.logout', 'User signed out.', 'user', (int)$_SESSION['user_id']);
}

// Unset all session variables
$_SESSION = [];

// Destroy session
session_destroy();

// 🔥 PREVENT BACK BUTTON CACHE
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect to login
header("Location: " . BASE_URL . "login");
exit;
