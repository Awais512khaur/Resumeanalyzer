<?php
require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/audit.php";

if (($_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    exit('Administrator access is required.');
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . 'admin_users');
    exit;
}

$postedToken = $_POST['csrf_token'] ?? '';
if (!isset($_SESSION['csrf_token']) || !is_string($postedToken) || !hash_equals($_SESSION['csrf_token'], $postedToken)) {
    http_response_code(403);
    exit('Your session expired. Refresh the user list and try again.');
}

$userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$page = max(1, (int)($_POST['p'] ?? 1));
$name = trim((string)($_POST['name'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$passwordConfirmation = (string)($_POST['password_confirmation'] ?? '');
$role = (string)($_POST['role'] ?? 'user');
$redirect = BASE_URL . 'index.php?page=admin_users&p=' . $page;

$fail = static function ($message) use ($redirect) {
    $_SESSION['user_admin_error'] = $message;
    header('Location: ' . $redirect);
    exit;
};

if ($name === '' || strlen($name) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 150) {
    $fail('Enter a name and a valid email address.');
}

try {
    if ($userId && $userId > 0) {
        $statement = $pdo->prepare('SELECT id, name, email, role, status FROM users WHERE id = ?');
        $statement->execute([$userId]);
        $target = $statement->fetch(PDO::FETCH_ASSOC);
        if (!$target) {
            $fail('That user could not be found.');
        }

        $isSelf = $userId === (int)$_SESSION['user_id'];
        $newRole = $isSelf ? $target['role'] : $role;
        if (!in_array($newRole, ['user', 'admin'], true)) {
            $fail('Choose a valid account role.');
        }
        // Admin accounts cannot be locked from the user-management controls.
        $newStatus = $newRole === 'admin' ? 1 : (int)$target['status'];

        $duplicate = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
        $duplicate->execute([$email, $userId]);
        if ($duplicate->fetchColumn()) {
            $fail('Email already exist');
        }
        if ($password !== '' && strlen($password) < 12) {
            $fail('A new password must contain at least 12 characters.');
        }
        if ($password !== '' && !hash_equals($password, $passwordConfirmation)) {
            $fail('The passwords do not match. Please confirm the password.');
        }

        $sql = 'UPDATE users SET name = ?, email = ?, role = ?, status = ?';
        $values = [$name, $email, $newRole, $newStatus];
        if ($password !== '') {
            $sql .= ', password = ?, failed_login_count = 0, failed_login_until = NULL';
            $values[] = password_hash($password, PASSWORD_BCRYPT);
        }
        $sql .= ' WHERE id = ?';
        $values[] = $userId;
        $pdo->prepare($sql)->execute($values);

        if ($isSelf) {
            $_SESSION['user_name'] = $name;
        }
        audit_log($pdo, 'user.updated', 'Account details updated for ' . $name . ' (' . $email . ').', 'user', (int)$userId);
        $_SESSION['user_admin_notice'] = 'Updated ' . $name . ' successfully.';
    } else {
        if (!in_array($role, ['user', 'admin'], true)) {
            $fail('Choose a valid account role.');
        }
        if (strlen($password) < 12) {
            $fail('Set a password with at least 12 characters for the new account.');
        }
        if (!hash_equals($password, $passwordConfirmation)) {
            $fail('The passwords do not match. Please confirm the password.');
        }
        $duplicate = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $duplicate->execute([$email]);
        if ($duplicate->fetchColumn()) {
            $fail('Email already exist');
        }

        $pdo->prepare('INSERT INTO users (name, email, password, role, status) VALUES (?, ?, ?, ?, 1)')
            ->execute([$name, $email, password_hash($password, PASSWORD_BCRYPT), $role]);
        $newUserId = (int)$pdo->lastInsertId();
        audit_log($pdo, 'user.created_by_admin', 'Account created by an administrator: ' . $name . ' (' . $email . ').', 'user', $newUserId);
        $_SESSION['user_admin_notice'] = 'Created ' . $name . ' successfully.';
    }
} catch (PDOException $exception) {
    error_log('Admin user save failed: ' . $exception->getMessage());
    $fail('The user could not be saved. Check that the email address is unique and try again.');
}

header('Location: ' . $redirect);
exit;
