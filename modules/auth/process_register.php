<?php
require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/audit.php";

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "register");
    exit;
}
if (!resume_csrf_is_valid($_POST['csrf_token'] ?? null)) {
    $_SESSION['error'] = 'Your session expired. Please refresh and try again.';
    header('Location: ' . BASE_URL . 'register');
    exit;
}

// Get input safely
$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$passwordConfirmation = $_POST['password_confirmation'] ?? '';

// Basic validation
if (empty($name) || empty($email) || empty($password) || $passwordConfirmation === '') {
    $_SESSION['error'] = "All fields are required";
    header("Location: " . BASE_URL . "register");
    exit;
}

if (strlen($password) < 12) {
    $_SESSION['error'] = "Use a password with at least 12 characters.";
    header("Location: " . BASE_URL . "register");
    exit;
}

if (!hash_equals($password, $passwordConfirmation)) {
    $_SESSION['error'] = "Passwords do not match. Please check both fields.";
    header("Location: " . BASE_URL . "register");
    exit;
}

// Email format validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email format";
    header("Location: " . BASE_URL . "register");
    exit;
}

// Check if email already exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->rowCount() > 0) {
    $_SESSION['error'] = "Email already exists";
    header("Location: " . BASE_URL . "register");
    exit;
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_BCRYPT);

// Insert user
$stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
$stmt->execute([$name, $email, $hashed_password]);
$newUserId = (int)$pdo->lastInsertId();

session_regenerate_id(true);
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$_SESSION['user_id']   = $newUserId;
$_SESSION['user_name'] = $name;
$_SESSION['role']      = 'user';
$_SESSION['auth_password_hash'] = $hashed_password;
audit_log($pdo, 'account.registered', 'A new account was registered.', 'user', $newUserId);

// Redirect to dashboard
header("Location: " . BASE_URL . "dashboard");
exit;
