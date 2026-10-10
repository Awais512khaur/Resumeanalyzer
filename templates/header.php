<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 🔒 PREVENT BACK CACHE
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . "/../config/config.php";

$user_name = $_SESSION['user_name'] ?? 'Guest';
$user_role = $_SESSION['role'] ?? 'user';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Resume Analyzer</title>

<?php include_once __DIR__ . '/../assets/cdn/tailwind.php'; ?>
<?php include_once __DIR__ . '/../assets/CDN/chart.php'; ?>

<style>
body {
    background: linear-gradient(135deg, #0f172a, #1e293b);
}
</style>
</head>

<body class="text-white min-h-screen flex flex-col">

<!-- NAVBAR -->
<nav class="bg-white/10 backdrop-blur-lg shadow-lg fixed w-full top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">

        <!-- LOGO -->
        <a href="<?php echo BASE_URL; ?>dashboard" class="text-xl font-bold">
            ResumeAI
        </a>

        <!-- DESKTOP MENU -->
        <div class="hidden md:flex items-center space-x-6">

            <a href="<?php echo BASE_URL; ?>dashboard" class="hover:text-blue-400 transition">Dashboard</a>
            <a href="<?php echo BASE_URL; ?>resumes" class="hover:text-blue-400 transition">Resumes</a>
            <a href="<?php echo BASE_URL; ?>upload" class="hover:text-blue-400 transition">Upload</a>
            <a href="<?php echo BASE_URL; ?>history" class="hover:text-blue-400 transition">History</a>
            <!-- 🔥 ADMIN MENU -->
            <?php if ($user_role === 'admin'): ?>
                <a href="<?php echo BASE_URL; ?>index.php?page=admin_users">Users</a>
                <a href="<?php echo BASE_URL; ?>index.php?page=admin_resumes">All Resumes</a>
                <a href="<?php echo BASE_URL; ?>index.php?page=admin_analysis">Analytics</a>
            <?php endif; ?>
            <!-- USER -->
            <span class="bg-white/20 px-3 py-1 rounded-lg text-sm">
                👤 <?php echo htmlspecialchars($user_name); ?>
                 <?php if ($user_role === 'admin'): ?>
                 <span class="text-yellow-400 font-semibold">Admin</span>
                  <?php endif; ?>
            </span>

            <!-- LOGOUT -->
            <a href="<?php echo BASE_URL; ?>modules/auth/logout.php"
               class="bg-red-500 px-3 py-1 rounded hover:bg-red-600 transition">
                Logout
            </a>
        </div>

        <!-- MOBILE BUTTON -->
        <button id="menuBtn" class="md:hidden text-2xl">
            ☰
        </button>
    </div>

    <!-- MOBILE MENU -->
    <div id="mobileMenu" class="hidden md:hidden flex flex-col bg-white/10 backdrop-blur-lg px-4 pb-4">

        <a href="<?php echo BASE_URL; ?>dashboard" class="py-2 border-b border-white/10">Dashboard</a>
        <a href="<?php echo BASE_URL; ?>resumes" class="py-2 border-b border-white/10">Resumes</a>
        <a href="<?php echo BASE_URL; ?>upload" class="py-2 border-b border-white/10">Upload</a>
        <a href="<?php echo BASE_URL; ?>history" class="py-2 border-b border-white/10">History</a>

        <!-- 🔥 ADMIN MENU MOBILE -->
        <?php if ($user_role === 'admin'): ?>
            <span class="py-2 text-yellow-400 font-semibold border-b border-white/10">Admin</span>

            <a href="<?php echo BASE_URL; ?>admin" class="py-2 border-b border-white/10">Admin Panel</a>
            <a href="<?php echo BASE_URL; ?>index.php?page=admin_users">Users</a>
            <a href="<?php echo BASE_URL; ?>index.php?page=admin_resumes">All Resumes</a>
            <a href="<?php echo BASE_URL; ?>index.php?page=admin_analysis">Analytics</a>
        <?php endif; ?>

        <!-- USER -->
        <span class="py-2 text-gray-300 border-b border-white/10">
            👤 <?php echo htmlspecialchars($user_name); ?>
        </span>

        <!-- LOGOUT -->
        <a href="<?php echo BASE_URL; ?>modules/auth/logout.php"
           class="py-2 text-red-400">
            Logout
        </a>
    </div>
</nav>

<!-- MOBILE MENU SCRIPT -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const btn = document.getElementById("menuBtn");
    const menu = document.getElementById("mobileMenu");

    btn.addEventListener("click", function() {
        menu.classList.toggle("hidden");
    });
});
</script>

<!-- PAGE CONTENT START -->
<div class="pt-20">