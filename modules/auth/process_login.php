<?php
session_start();

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/audit.php";

// CHECK REQUEST METHOD
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "login");
    exit;
}

// GET INPUT
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// VALIDATION
if (empty($email) || empty($password)) {
    $_SESSION['error'] = "Please fill all fields";
    header("Location: " . BASE_URL . "login");
    exit;
}

try {
    // FETCH USER
    $stmt = $pdo->prepare("SELECT id, name, email, password, role, status FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // CHECK USER EXISTS
    if (!$user) {
        audit_log($pdo, 'auth.login_failed', 'Sign-in failed because the account was not found.');
        $_SESSION['error'] = "User not found";
        header("Location: " . BASE_URL . "login");
        exit;
    }

    // VERIFY PASSWORD
    if (!password_verify($password, $user['password'])) {
        audit_log($pdo, 'auth.login_failed', 'Sign-in failed because the password did not match.', 'user', (int)$user['id'], null, $user['name']);
        $_SESSION['error'] = "Invalid password";
        header("Location: " . BASE_URL . "login");
        exit;
    }

    if ((int)$user['status'] !== 1) {
        audit_log($pdo, 'auth.login_blocked', 'Sign-in blocked because the account is locked.', 'user', (int)$user['id']);
        $_SESSION['error'] = 'Your account is locked. Please contact an administrator to unlock your access.';
        header("Location: " . BASE_URL . "login");
        exit;
    }

    // STORE SESSION
    $_SESSION['user_id']   = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['role']      = $user['role'];
    audit_log($pdo, 'auth.login', 'User signed in.', 'user', (int)$user['id']);

    // 🔥 ROLE BASED REDIRECT
    if ($user['role'] === 'admin') {
        header("Location: " . BASE_URL . "dashboard");
    } else {
        header("Location: " . BASE_URL . "dashboard");
    }

    exit;

} catch (PDOException $e) {

    $_SESSION['error'] = "Something went wrong. Try again.";
    header("Location: " . BASE_URL . "login");
    exit;
}
