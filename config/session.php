<?php

function end_resume_session(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    $_SESSION = [];
    $cookie = session_get_cookie_params();
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $cookie['path'],
        'domain' => $cookie['domain'],
        'secure' => $cookie['secure'],
        'httponly' => $cookie['httponly'],
        'samesite' => $cookie['samesite'] ?? 'Lax',
    ]);
    session_destroy();
}

function resume_csrf_is_valid($token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    $cookieSecure = getenv('RESUME_COOKIE_SECURE') === '1'
        || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $cookieSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('RESUMEAISESSID');
    session_start();
}

$idleLimit = max(300, min((int)(getenv('RESUME_SESSION_IDLE_SECONDS') ?: 1800), 86400));
if (isset($_SESSION['user_id'], $_SESSION['last_activity']) && time() - (int)$_SESSION['last_activity'] > $idleLimit) {
    end_resume_session();
    session_start();
}
if (isset($_SESSION['user_id'])) {
    $_SESSION['last_activity'] = time();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
