<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/audit.php';

$redirect = BASE_URL . 'index.php?page=reset_password';
$fail = static function (string $message) use ($redirect): void {
    $_SESSION['password_reset_error'] = $message;
    header('Location: ' . $redirect);
    exit;
};

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_SESSION['password_reset_user_id'])) {
    header('Location: ' . BASE_URL . 'index.php?page=forgot_password');
    exit;
}
$csrf = $_POST['csrf_token'] ?? '';
if (!is_string($csrf) || !isset($_SESSION['password_reset_csrf']) || !hash_equals($_SESSION['password_reset_csrf'], $csrf)) {
    $fail('Your session expired. Please try again.');
}
if (time() - (int)($_SESSION['password_reset_verified_at'] ?? 0) > 600) {
    unset($_SESSION['password_reset_user_id'], $_SESSION['password_reset_verified_at']);
    $_SESSION['password_reset_error'] = 'Your verification expired. Request a new code.';
    header('Location: ' . BASE_URL . 'index.php?page=forgot_password');
    exit;
}
$password = $_POST['password'] ?? '';
$confirmation = $_POST['password_confirmation'] ?? '';
if (!is_string($password) || !is_string($confirmation) || strlen($password) < 12) {
    $fail('Choose a password with at least 12 characters.');
}
if (!hash_equals($password, $confirmation)) {
    $fail('The passwords do not match. Please try again.');
}

$userId = (int)$_SESSION['password_reset_user_id'];
$pdo->beginTransaction();
try {
    $update = $pdo->prepare('UPDATE users SET password = ?, failed_login_count = 0, failed_login_until = NULL WHERE id = ?');
    $update->execute([password_hash($password, PASSWORD_DEFAULT), $userId]);
    if ($update->rowCount() !== 1) {
        throw new RuntimeException('The password reset account no longer exists.');
    }
    $pdo->prepare('DELETE FROM password_reset_otps WHERE user_id = ?')->execute([$userId]);
    $pdo->commit();
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Password reset save failed: ' . $exception->getMessage());
    $fail('We could not save your new password. Please try again.');
}

audit_log($pdo, 'auth.password_reset', 'Password was reset using an email code.', 'user', $userId);
unset($_SESSION['password_reset_email'], $_SESSION['password_reset_user_id'], $_SESSION['password_reset_verified_at'], $_SESSION['password_reset_csrf']);
$_SESSION['password_reset_success'] = 'Your password has been reset. Sign in with your new password.';
header('Location: ' . BASE_URL . 'login');
exit;
