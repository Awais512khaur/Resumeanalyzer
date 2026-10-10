<?php

// AUTO DETECT BASE URL (works for localhost + live server)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Project folder (important for localhost)
$project_folder = "/resume-analyzer"; // change this if folder name changes
define('GEMINI_API_KEY', 'AIzaSyBz-ZLE6W4K0pXn5KeKCpca3Jr4duAU-ps');
define('BASE_URL', $protocol . $host . $project_folder . "/");