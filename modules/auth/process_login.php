<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/audit.php';
require_once __DIR__ . '/../../config/rate_limit.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'login');
    exit;
}
if (!resume_csrf_is_valid($_POST['csrf_token'] ?? null)) {
    $_SESSION['error'] = 'Your session expired. Please refresh and try again.';
    header('Location: ' . BASE_URL . 'login');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
if ($email === '' || $password === '') {
    $_SESSION['error'] = 'Please fill all fields';
    header('Location: ' . BASE_URL . 'login');
    exit;
}

try {
    $ipBucket = rate_limit_key('login-ip', resume_client_ip());
    if (rate_limit_is_blocked($pdo, $ipBucket)) {
        $_SESSION['error'] = 'Too many sign-in attempts from this network. Please wait and try again.';
        header('Location: ' . BASE_URL . 'login');
        exit;
    }

    $stmt = $pdo->prepare('SELECT id, name, email, password, role, status, failed_login_count, failed_login_until, lock_reason, locked_by_name FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        audit_log($pdo, 'auth.login_failed', 'Sign-in failed because the account was not found.');
        rate_limit_record_failure($pdo, $ipBucket, 20, 15, 15);
        $_SESSION['error'] = 'Invalid email or password';
        header('Location: ' . BASE_URL . 'login');
        exit;
    }

    if ((int)$user['status'] !== 1) {
        $message = $user['lock_reason'] === 'failed_attempts'
            ? 'Your account was locked after 10 incorrect password attempts. Contact an administrator to unlock it.'
            : 'Your account is locked' . ($user['locked_by_name'] ? ' by administrator ' . $user['locked_by_name'] : '') . '. Contact an administrator to unlock it.';
        audit_log($pdo, 'auth.login_blocked', 'Sign-in blocked because the account is locked.', 'user', (int)$user['id']);
        $_SESSION['error'] = $message;
        header('Location: ' . BASE_URL . 'login');
        exit;
    }

    if (!empty($user['failed_login_until']) && strtotime($user['failed_login_until']) > time()) {
        $minutes = max(1, (int)ceil((strtotime($user['failed_login_until']) - time()) / 60));
        $_SESSION['error'] = 'Too many incorrect passwords. Try again in ' . $minutes . ' minute' . ($minutes === 1 ? '' : 's') . '.';
        header('Location: ' . BASE_URL . 'login');
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        audit_log($pdo, 'auth.login_failed', 'Sign-in failed because the password did not match.', 'user', (int)$user['id'], null, $user['name']);
        rate_limit_record_failure($pdo, $ipBucket, 20, 15, 15);

        $pdo->beginTransaction();
        $lock = $pdo->prepare('SELECT failed_login_count FROM users WHERE id = ? FOR UPDATE');
        $lock->execute([(int)$user['id']]);
        $failures = (int)$lock->fetchColumn() + 1;
        if ($failures >= 10) {
            $pdo->prepare("UPDATE users SET failed_login_count = ?, failed_login_until = NULL, status = 0, lock_reason = 'failed_attempts', locked_by_name = NULL WHERE id = ?")
                ->execute([$failures, (int)$user['id']]);
            $pdo->commit();
            audit_log($pdo, 'user.locked_failed_attempts', 'Account locked after 10 incorrect password attempts.', 'user', (int)$user['id']);
            $_SESSION['error'] = 'Your account has been locked after 10 incorrect password attempts. Contact an administrator to unlock it.';
        } else {
            $cooldown = $failures === 5 ? ', failed_login_until = DATE_ADD(NOW(), INTERVAL 5 MINUTE)' : '';
            $pdo->prepare('UPDATE users SET failed_login_count = ?' . $cooldown . ' WHERE id = ?')
                ->execute([$failures, (int)$user['id']]);
            $pdo->commit();
            $_SESSION['error'] = $failures >= 5
                ? 'Too many incorrect passwords. Your sign-in is paused for 5 minutes.'
                : 'Invalid email or password';
        }
        header('Location: ' . BASE_URL . 'login');
        exit;
    }

    $pdo->prepare('UPDATE users SET failed_login_count = 0, failed_login_until = NULL WHERE id = ?')->execute([(int)$user['id']]);
    session_regenerate_id(true);
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['auth_password_hash'] = $user['password'];
    audit_log($pdo, 'auth.login', 'User signed in.', 'user', (int)$user['id']);
    header('Location: ' . BASE_URL . 'dashboard');
    exit;
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Login database operation failed: ' . $e->getMessage());
    $_SESSION['error'] = 'Something went wrong. Try again.';
    header('Location: ' . BASE_URL . 'login');
    exit;
}
