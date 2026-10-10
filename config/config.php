<?php

// AUTO DETECT BASE URL (works for localhost + live server)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

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

define('GEMINI_API_KEY', 'AIzaSyBz-ZLE6W4K0pXn5KeKCpca3Jr4duAU-ps');
define('BASE_URL', $protocol . $host . ($project_folder ? $project_folder . '/' : '/'));

// Ensure favicons exist
require_once __DIR__ . "/favicon_helper.php";