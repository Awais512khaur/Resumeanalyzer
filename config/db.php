<?php
require_once __DIR__ . '/session.php';

$host = getenv('DB_HOST') ?: 'localhost';
$port = getenv('DB_PORT') ?: '3306';
$dbname = getenv('DB_NAME') ?: 'resume_analyzer';
$username = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';

if (getenv('APP_ENV') === 'production' && ($username === 'root' || $password === '')) {
    error_log('Production database configuration must use a non-root user and a non-empty password.');
    http_response_code(500);
    exit('The application database is unavailable. Please try again later.');
}

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);

    // Set error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch associative array by default
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

    if (isset($_SESSION['user_id'])) {
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        $currentUser = $pdo->prepare('SELECT name, password, role, status FROM users WHERE id = ?');
        $currentUser->execute([(int)$_SESSION['user_id']]);
        $account = $currentUser->fetch();
        $sessionHash = (string)($_SESSION['auth_password_hash'] ?? '');
        if (!$account || (int)$account['status'] !== 1 || $sessionHash === '' || !hash_equals((string)$account['password'], $sessionHash)) {
            end_resume_session();
            session_start();
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        } else {
            if (($_SESSION['role'] ?? '') !== $account['role']) {
                session_regenerate_id(true);
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            }
            $_SESSION['user_name'] = $account['name'];
            $_SESSION['role'] = $account['role'];
            $_SESSION['auth_password_hash'] = $account['password'];
        }
    }

} catch (PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    die("The application database is unavailable. Please try again later.");
}
