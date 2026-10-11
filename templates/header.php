<?php
require_once __DIR__ . '/../config/session.php';

// 🔒 PREVENT BACK CACHE
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once __DIR__ . "/../config/config.php";

$user_name = $_SESSION['user_name'] ?? 'Guest';
$user_role = $_SESSION['role'] ?? 'user';
$isDeveloperPage = (($_GET['page'] ?? '') === 'developer');
$showAppNav = !$isDeveloperPage || isset($_SESSION['user_id']);
$currentPage = (string)($_GET['page'] ?? '');
$navAttributes = static function (array $pages, string $base = 'app-link') use ($currentPage): string {
    $active = in_array($currentPage, $pages, true);
    return 'class="' . $base . ($active ? ' active' : '') . '"' . ($active ? ' aria-current="page"' : '');
};
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Resume Analyzer</title>

<link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>assets/images/favicon.svg?v=2">
<link rel="icon" type="image/png" sizes="64x64" href="<?php echo BASE_URL; ?>assets/images/favicon.png?v=2">
<link rel="shortcut icon" type="image/x-icon" href="<?php echo BASE_URL; ?>favicon.ico?v=2">

<?php include_once __DIR__ . '/../assets/cdn/tailwind.php'; ?>
<?php include_once __DIR__ . '/../assets/CDN/chart.php'; ?>

<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/app-header.css?v=1">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/site-detail.css?v=1">
</head>

<body class="text-white min-h-screen flex flex-col">

<?php if ($showAppNav): ?>
<!-- NAVBAR -->
<nav class="app-nav fixed w-full top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">

        <!-- LOGO -->
        <a href="<?php echo BASE_URL; ?>dashboard" class="flex items-center gap-2.5 text-lg font-bold tracking-tight">
            <span class="app-brand-mark" aria-hidden="true"><svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M7 3.75h7l4 4V20a.75.75 0 0 1-.75.75h-10.5A.75.75 0 0 1 6 20V4.5a.75.75 0 0 1 .75-.75Z" stroke="currentColor" stroke-width="1.8"/><path d="M14 4v4h4M9 12h6M9 15.5h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
            <span>Resume<span class="text-teal-200">AI</span></span>
        </a>

        <!-- DESKTOP MENU -->
        <div class="hidden md:flex items-center space-x-6">

            <a href="<?php echo BASE_URL; ?>dashboard" <?php echo $navAttributes(['dashboard']); ?>>Dashboard</a>
            <a href="<?php echo BASE_URL; ?>resumes" <?php echo $navAttributes(['resumes']); ?>>Resumes</a>
            <a href="<?php echo BASE_URL; ?>upload" <?php echo $navAttributes(['upload']); ?>>Upload</a>
            <a href="<?php echo BASE_URL; ?>history" <?php echo $navAttributes(['history']); ?>>Analyses report</a>
            <!-- 🔥 ADMIN MENU -->
            <?php if ($user_role === 'admin'): ?>
                <a href="<?php echo BASE_URL; ?>index.php?page=admin_users" <?php echo $navAttributes(['admin_users', 'admin_user']); ?>>Users</a>
                <a href="<?php echo BASE_URL; ?>index.php?page=admin_resumes" <?php echo $navAttributes(['admin_resumes', 'analyze_admin']); ?>>All Resumes</a>
                <a href="<?php echo BASE_URL; ?>index.php?page=admin_analysis" <?php echo $navAttributes(['admin_analysis']); ?>>Analytics</a>
                <a href="<?php echo BASE_URL; ?>index.php?page=admin_audit_logs" <?php echo $navAttributes(['admin_audit_logs']); ?>>Audit log</a>
            <?php endif; ?>
            <details class="account-menu relative">
                <summary class="flex cursor-pointer list-none items-center gap-2 rounded-xl border border-white/10 bg-white/[.04] px-3 py-1.5 text-sm text-slate-300 transition hover:bg-white/[.08]">
                    <span aria-hidden="true">👤</span>
                    <span><?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?><?php if ($user_role === 'admin'): ?> <span class="font-semibold text-yellow-400">Admin</span><?php endif; ?></span>
                    <svg class="h-3.5 w-3.5 text-slate-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 7.47a.75.75 0 0 1 1.06 0L10 11.19l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.53a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                </summary>
                <div class="account-dropdown absolute right-0 top-full z-[70] mt-2 w-48 rounded-xl border border-white/10 bg-[#101a2b] p-1.5 shadow-2xl shadow-black/40">
                    <a href="<?php echo BASE_URL; ?>profile" <?php echo $navAttributes(['profile'], 'block rounded-lg px-3 py-2.5 text-sm text-slate-200 transition hover:bg-white/[.07] hover:text-teal-100'); ?>>Profile</a>
                    <form action="<?php echo BASE_URL; ?>modules/auth/logout.php" method="POST"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>"><button type="submit" class="block w-full rounded-lg px-3 py-2.5 text-left text-sm text-rose-200 transition hover:bg-rose-200/[.08]">Logout</button></form>
                </div>
            </details>
        </div>

        <!-- MOBILE BUTTON -->
        <button id="menuBtn" class="md:hidden text-2xl">
            ☰
        </button>
    </div>

    <!-- MOBILE MENU -->
    <div id="mobileMenu" class="hidden md:hidden flex flex-col border-t border-white/10 bg-[#0b1221]/95 px-4 pb-4 backdrop-blur-xl">

        <a href="<?php echo BASE_URL; ?>dashboard" <?php echo $navAttributes(['dashboard'], 'mobile-nav-link py-2 border-b border-white/10'); ?>>Dashboard</a>
        <a href="<?php echo BASE_URL; ?>resumes" <?php echo $navAttributes(['resumes'], 'mobile-nav-link py-2 border-b border-white/10'); ?>>Resumes</a>
        <a href="<?php echo BASE_URL; ?>upload" <?php echo $navAttributes(['upload'], 'mobile-nav-link py-2 border-b border-white/10'); ?>>Upload</a>
        <a href="<?php echo BASE_URL; ?>history" <?php echo $navAttributes(['history'], 'mobile-nav-link py-2 border-b border-white/10'); ?>>Analyses report</a>

        <!-- 🔥 ADMIN MENU MOBILE -->
        <?php if ($user_role === 'admin'): ?>
            <span class="py-2 text-teal-200 font-semibold border-b border-white/10">Admin</span>

            <a href="<?php echo BASE_URL; ?>admin" <?php echo $navAttributes(['admin'], 'mobile-nav-link py-2 border-b border-white/10'); ?>>Admin Panel</a>
            <a href="<?php echo BASE_URL; ?>index.php?page=admin_users" <?php echo $navAttributes(['admin_users', 'admin_user'], 'mobile-nav-link py-2'); ?>>Users</a>
            <a href="<?php echo BASE_URL; ?>index.php?page=admin_resumes" <?php echo $navAttributes(['admin_resumes', 'analyze_admin'], 'mobile-nav-link py-2'); ?>>All Resumes</a>
            <a href="<?php echo BASE_URL; ?>index.php?page=admin_analysis" <?php echo $navAttributes(['admin_analysis'], 'mobile-nav-link py-2'); ?>>Analytics</a>
            <a href="<?php echo BASE_URL; ?>index.php?page=admin_audit_logs" <?php echo $navAttributes(['admin_audit_logs'], 'mobile-nav-link py-2 border-b border-white/10'); ?>>Audit log</a>
        <?php endif; ?>

        <a href="<?php echo BASE_URL; ?>profile" <?php echo $navAttributes(['profile'], 'mobile-nav-link py-2 border-b border-white/10'); ?>>Profile · <?php echo htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8'); ?></a>

        <!-- LOGOUT -->
        <form action="<?php echo BASE_URL; ?>modules/auth/logout.php" method="POST" class="py-2"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>"><button type="submit" class="text-rose-200">Logout</button></form>
    </div>
</nav>

<!-- MOBILE MENU SCRIPT -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const btn = document.getElementById("menuBtn");
    const menu = document.getElementById("mobileMenu");

    if (btn && menu) {
        btn.addEventListener("click", function() {
            menu.classList.toggle("hidden");
        });
    }
});

document.addEventListener("click", function(event) {
    document.querySelectorAll(".account-menu[open]").forEach(function(accountMenu) {
        if (!accountMenu.contains(event.target)) accountMenu.removeAttribute("open");
    });
});

document.addEventListener("keydown", function(event) {
    if (event.key === "Escape") {
        document.querySelectorAll(".account-menu[open]").forEach(menu => menu.removeAttribute("open"));
    }
});
</script>
<?php endif; ?>

<!-- PAGE CONTENT START -->
<div class="<?php echo $showAppNav ? 'pt-20' : ''; ?>">
