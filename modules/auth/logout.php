<?php
require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/audit.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !resume_csrf_is_valid($_POST['csrf_token'] ?? null)) {
    http_response_code(405);
    exit('Invalid logout request.');
}

if (isset($_SESSION['user_id'])) {
    audit_log($pdo, 'auth.logout', 'User signed out.', 'user', (int)$_SESSION['user_id']);
}

end_resume_session();

// 🔥 PREVENT BACK BUTTON CACHE
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect to login
header("Location: " . BASE_URL . "login");
exit;
