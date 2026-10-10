<?php
session_start();
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';

$redirect = BASE_URL . 'index.php?page=reset_password';
$fail = static function (string $message) use ($redirect): void {
    $_SESSION['password_reset_error'] = $message;
    header('Location: ' . $redirect);
    exit;
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['password_reset_email'])) {
    header('Location: ' . BASE_URL . 'index.php?page=forgot_password');
    exit;
}
$csrf = $_POST['csrf_token'] ?? '';
if (!is_string($csrf) || !isset($_SESSION['password_reset_csrf']) || !hash_equals($_SESSION['password_reset_csrf'], $csrf)) {
    $fail('Your session expired. Please try again.');
}
$code = trim((string)($_POST['otp'] ?? ''));
if (!preg_match('/^\d{6}$/', $code)) {
    $fail('Enter the six-digit code from your email.');
}

try {
    $query = $pdo->prepare(
        'SELECT r.user_id, r.otp_hash, r.attempts, (r.expires_at > NOW()) AS is_unexpired
         FROM password_reset_otps r JOIN users u ON u.id = r.user_id
         WHERE u.email = ? LIMIT 1'
    );
    $query->execute([$_SESSION['password_reset_email']]);
    $reset = $query->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $exception) {
    error_log('Password reset verification failed: ' . $exception->getMessage());
    $fail('Password reset is unavailable. Please contact the administrator.');
}
if (!$reset) {
    $fail('That code is no longer valid. Request a new one.');
}
try {
    if ((int)$reset['is_unexpired'] !== 1 || (int)$reset['attempts'] >= 5) {
        $pdo->prepare('DELETE FROM password_reset_otps WHERE user_id = ?')->execute([(int)$reset['user_id']]);
        $fail('That code expired or had too many attempts. Request a new one.');
    }
    if (!password_verify($code, $reset['otp_hash'])) {
        $attempts = (int)$reset['attempts'] + 1;
        $pdo->prepare('UPDATE password_reset_otps SET attempts = ? WHERE user_id = ?')->execute([$attempts, (int)$reset['user_id']]);
        if ($attempts >= 5) {
            $pdo->prepare('DELETE FROM password_reset_otps WHERE user_id = ?')->execute([(int)$reset['user_id']]);
            $fail('Too many incorrect codes. Request a new one.');
        }
        $fail('That code is incorrect. Please try again.');
    }
} catch (PDOException $exception) {
    error_log('Password reset code update failed: ' . $exception->getMessage());
    $fail('Password reset is unavailable. Please contact the administrator.');
}

$_SESSION['password_reset_user_id'] = (int)$reset['user_id'];
$_SESSION['password_reset_verified_at'] = time();
unset($_SESSION['password_reset_error']);
header('Location: ' . $redirect);
exit;
