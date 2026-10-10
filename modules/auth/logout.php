<?php
session_start();

// Unset all session variables
$_SESSION = [];

// Destroy session
session_destroy();

// Load config
require_once __DIR__ . "/../../config/config.php";

// 🔥 PREVENT BACK BUTTON CACHE
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Redirect to login
header("Location: " . BASE_URL . "login");
exit;