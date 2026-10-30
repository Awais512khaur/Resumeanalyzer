<?php
require_once __DIR__ . '/session.php';

$localConfigFile = is_file(__DIR__ . '/local.php') ? __DIR__ . '/local.php' : (is_file(dirname(__DIR__) . '/local.php') ? dirname(__DIR__) . '/local.php' : __DIR__ . '/local.php');
$resumeLocalConfig = is_file($localConfigFile) ? require $localConfigFile : [];
$resumeLocalConfig = is_array($resumeLocalConfig) ? $resumeLocalConfig : [];

// Auto-load environment variables from .env if present
$envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
if (is_file($envFile)) {
    $envLines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            [$k, $v] = explode('=', $line, 2);
            $k = trim($k);
            $v = trim($v, " \t\n\r\0\x0B\"'");
            if (getenv($k) === false) {
                if (function_exists('putenv')) {
                    putenv("{$k}={$v}");
                }
                $_ENV[$k] = $v;
                $_SERVER[$k] = $v;
            }
        }
    }
}

// AUTO DETECT BASE URL (works for localhost + live server)
$forwardedProto = strtolower(trim(explode(',', $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')[0]));
$isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $forwardedProto === 'https';
$protocol = $isHttps ? "https://" : "http://";

$configuredUrl = trim((string)($resumeLocalConfig['APP_URL'] ?? (getenv('APP_URL') ?: '')));
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
if (!preg_match('/\A[a-z0-9.-]+(?::[0-9]{1,5})?\z/i', $host) || str_contains($host, '..')) {
    $host = 'localhost';
}

if (($resumeLocalConfig['APP_ENV'] ?? getenv('APP_ENV')) === 'production') {
    $parsedAppUrl = $configuredUrl !== '' ? parse_url($configuredUrl) : false;
    if (!is_array($parsedAppUrl) || ($parsedAppUrl['scheme'] ?? '') !== 'https' || empty($parsedAppUrl['host'])) {
        error_log('Production requires APP_URL to be an absolute HTTPS URL.');
        http_response_code(500);
        exit('The application is not configured securely. Please contact the administrator.');
    }
}

// Automatically detect project folder from directory structure
$project_root_dir = str_replace('\\', '/', dirname(__DIR__));
$doc_root = !empty($_SERVER['DOCUMENT_ROOT']) ? str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'])) : '';

if (!empty($doc_root) && stripos($project_root_dir, $doc_root) === 0) {
    $project_folder = substr($project_root_dir, strlen($doc_root));
} else {
    $project_folder = '/' . basename($project_root_dir);
}

$project_folder = '/' . trim($project_folder, '/');
if ($project_folder === '/') {
    $project_folder = '';
}

define('GEMINI_API_KEY', (string)($resumeLocalConfig['GEMINI_API_KEY'] ?? (getenv('GEMINI_API_KEY') ?: '')));
if ($configuredUrl !== '') {
    define('BASE_URL', rtrim($configuredUrl, '/') . '/');
} else {
    define('BASE_URL', $protocol . $host . ($project_folder ? $project_folder . '/' : '/'));
}

// Ensure favicons exist
require_once __DIR__ . "/favicon_helper.php";
