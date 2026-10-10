<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/mailer.php';
require_once __DIR__ . '/../../config/audit.php';
require_once __DIR__ . '/../../config/rate_limit.php';

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
    $ip = resume_client_ip();
    $ipBucket = rate_limit_key('password-reset-ip', $ip);
    $identityBucket = rate_limit_key('password-reset-identity-ip', strtolower($email) . '|' . $ip);
    if (rate_limit_is_blocked($pdo, $ipBucket) || rate_limit_is_blocked($pdo, $identityBucket)) {
        $_SESSION['password_reset_email'] = $email;
        $_SESSION['password_reset_notice'] = 'If an account matches this address, a reset code will be sent. If you do not receive one, check spam or contact the administrator.';
        header('Location: ' . BASE_URL . 'index.php?page=reset_password');
        exit;
    }
    rate_limit_record_failure($pdo, $ipBucket, 15, 60, 60);
    rate_limit_record_failure($pdo, $identityBucket, 4, 60, 60);

    $userQuery = $pdo->prepare('SELECT id, name, email FROM users WHERE email = ? LIMIT 1');
    $userQuery->execute([$email]);
    $user = $userQuery->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        $_SESSION['password_reset_email'] = $email;
        $_SESSION['password_reset_notice'] = 'If an account matches this address, a reset code will be sent. If you do not receive one, check spam or contact the administrator.';
        header('Location: ' . BASE_URL . 'index.php?page=reset_password');
        exit;
    }

    $userId = (int)$user['id'];
    $cooldown = $pdo->prepare('SELECT 1 FROM password_reset_otps WHERE user_id = ? AND created_at > DATE_SUB(NOW(), INTERVAL 60 SECOND)');
    $cooldown->execute([$userId]);
    if ($cooldown->fetchColumn()) {
        $_SESSION['password_reset_email'] = $user['email'];
        $_SESSION['password_reset_notice'] = 'If an account matches this address, a reset code will be sent. If you do not receive one, check spam or contact the administrator.';
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
    $_SESSION['password_reset_notice'] = 'If an account matches this address, a reset code will be sent. If you do not receive one, check spam or contact the administrator.';
    audit_log($pdo, 'auth.password_reset_requested', 'A password reset code was emailed.', 'user', $userId);
    header('Location: ' . BASE_URL . 'index.php?page=reset_password');
    exit;
} catch (PDOException $exception) {
    error_log('Password reset request failed: ' . $exception->getMessage());
    $_SESSION['password_reset_email'] = $email;
    $_SESSION['password_reset_notice'] = 'If an account matches this address, a reset code will be sent. If you do not receive one, check spam or contact the administrator.';
    header('Location: ' . BASE_URL . 'index.php?page=reset_password');
    exit;
} catch (Throwable $exception) {
    error_log('Password reset email failed: ' . $exception->getMessage());
    $_SESSION['password_reset_email'] = $email;
    $_SESSION['password_reset_notice'] = 'If an account matches this address, a reset code will be sent. If you do not receive one, check spam or contact the administrator.';
    header('Location: ' . BASE_URL . 'index.php?page=reset_password');
    exit;
}
