<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/mailer.php';
require_once __DIR__ . '/../../config/audit.php';

$redirect = BASE_URL . 'index.php?page=forgot_password';
$fail = static function (string $message) use ($redirect): void {
    $_SESSION['password_reset_error'] = $message;
    header('Location: ' . $redirect);
    exit;
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirect);
    exit;
}
$csrf = $_POST['csrf_token'] ?? '';
if (!is_string($csrf) || !isset($_SESSION['password_reset_csrf']) || !hash_equals($_SESSION['password_reset_csrf'], $csrf)) {
    $fail('Your session expired. Please try again.');
}
$email = trim((string)($_POST['email'] ?? ''));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $fail('Enter a valid email address.');
}

try {
    $userQuery = $pdo->prepare('SELECT id, name, email FROM users WHERE email = ? LIMIT 1');
    $userQuery->execute([$email]);
    $user = $userQuery->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        $fail('This email does not match any account in our system.');
    }

    $userId = (int)$user['id'];
    $cooldown = $pdo->prepare('SELECT 1 FROM password_reset_otps WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 60 SECOND)');
    $cooldown->execute([$userId]);
    if ($cooldown->fetchColumn()) {
        $_SESSION['password_reset_email'] = $user['email'];
        $_SESSION['password_reset_error'] = 'A reset code was sent recently. Please wait a minute before requesting another.';
        header('Location: ' . BASE_URL . 'index.php?page=reset_password');
        exit;
    }

    $code = str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $saveCode = $pdo->prepare(
        'INSERT INTO password_reset_otps (user_id, otp_hash, expires_at, created_at, attempts)
         VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE), NOW(), 0)
         ON DUPLICATE KEY UPDATE otp_hash = VALUES(otp_hash), expires_at = VALUES(expires_at), created_at = NOW(), attempts = 0'
    );
    $saveCode->execute([$userId, password_hash($code, PASSWORD_DEFAULT)]);

    try {
        send_password_reset_code($user['email'], $user['name'], $code);
    } catch (Throwable $exception) {
        $pdo->prepare('DELETE FROM password_reset_otps WHERE user_id = ?')->execute([$userId]);
        throw $exception;
    }

    $_SESSION['password_reset_email'] = $user['email'];
    unset($_SESSION['password_reset_user_id'], $_SESSION['password_reset_verified_at']);
    unset($_SESSION['password_reset_error']);
    audit_log($pdo, 'auth.password_reset_requested', 'A password reset code was emailed.', 'user', $userId);
    header('Location: ' . BASE_URL . 'index.php?page=reset_password');
    exit;
} catch (PDOException $exception) {
    error_log('Password reset request failed: ' . $exception->getMessage());
    $fail('Password reset is not ready yet. Please contact the administrator.');
} catch (Throwable $exception) {
    error_log('Password reset email failed: ' . $exception->getMessage());
    if (strpos($exception->getMessage(), 'SMTP settings are missing') !== false) {
        $fail('Email delivery is not configured. Set RESUME_SMTP_HOST, RESUME_SMTP_USERNAME, RESUME_SMTP_PASSWORD, and RESUME_MAIL_FROM_EMAIL in Windows, then restart XAMPP and Apache.');
    }
    if (strpos($exception->getMessage(), 'Email support is not installed') !== false) {
        $fail('Email support is missing. Run composer install in the project folder, then try again.');
    }
    $fail('We could not send the reset email. Check the SMTP host, port, encryption, credentials, and Apache error log.');
}
