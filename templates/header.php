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

<style>
* { box-sizing: border-box; }
body { background: radial-gradient(ellipse at 15% 12%, rgba(72,104,255,.13), transparent 34%), radial-gradient(ellipse at 90% 55%, rgba(9,212,180,.09), transparent 33%), #080d1b; }
body::before { content:""; position:fixed; inset:0; z-index:-1; pointer-events:none; opacity:.35; background-image:linear-gradient(rgba(142,166,208,.035) 1px,transparent 1px),linear-gradient(90deg,rgba(142,166,208,.035) 1px,transparent 1px); background-size:58px 58px; mask-image:linear-gradient(to bottom,black,transparent 85%); }
.app-nav { border-bottom:1px solid rgba(255,255,255,.08); background:rgba(8,13,27,.78); backdrop-filter:blur(20px); }
.app-brand-mark { display:grid; place-items:center; width:2rem; height:2rem; border-radius:.7rem; color:#07131e; background:linear-gradient(145deg,#83ffe4,#49c8fc); box-shadow:0 0 25px rgba(73,200,252,.25); }
.app-link { display:inline-flex; align-items:center; border:1px solid transparent; border-radius:.65rem; padding:.45rem .65rem; color:#9aa8bf; transition:color .2s,background .2s,border-color .2s; }
.app-link:hover, .app-link.active { color:#9cefe1; background:rgba(85,212,194,.08); }
.app-link.active { border-color:rgba(125,230,220,.16); box-shadow:inset 0 -2px rgba(125,230,220,.7); }
.mobile-nav-link { display:block; border-radius:.55rem; color:#cbd5e1; transition:color .2s,background .2s; }
.mobile-nav-link.active { color:#9cefe1; background:rgba(85,212,194,.1); font-weight:650; }
.account-menu > summary::-webkit-details-marker { display:none; }
.account-dropdown { visibility:hidden; opacity:0; transform:translateY(-.35rem); transition:opacity .18s ease,transform .18s ease,visibility .18s; }
.account-menu[open] .account-dropdown, .account-menu:hover .account-dropdown, .account-menu:focus-within .account-dropdown { visibility:visible; opacity:1; transform:translateY(0); }
.account-dropdown a.active { color:#9cefe1; background:rgba(85,212,194,.1); }
.surface-card, .stat-card, .card, .app-table-wrap { border:1px solid rgba(255,255,255,.09); background:linear-gradient(145deg,rgba(25,36,59,.76),rgba(13,20,36,.8)); box-shadow:0 18px 50px rgba(0,0,0,.14),inset 0 1px rgba(255,255,255,.035); backdrop-filter:blur(16px); }
.stat-card { padding:1.3rem; border-radius:1.1rem; text-align:left; }
.stat-card h3 { color:#91a0b7; font-size:.8rem; font-weight:600; letter-spacing:.02em; }
.stat-card > p, .stat-card p.stat-value { margin-top:.55rem; color:#f5f7ff; font-size:1.8rem; line-height:1.15; }
.card { display:block; padding:1.35rem; border-radius:1.1rem; transition:transform .25s,border-color .25s,background .25s; }
.card:hover { transform:translateY(-4px); border-color:rgba(119,222,222,.27); background:linear-gradient(145deg,rgba(33,52,76,.8),rgba(15,25,42,.84)); }
.app-table-wrap { overflow-x:auto; border-radius:1rem; }
table { border-collapse:collapse; min-width:640px; }
table thead[class] { background:linear-gradient(110deg,rgba(67,94,126,.36),rgba(36,56,78,.5))!important; color:#a8bbcf; font-size:.7rem; letter-spacing:.12em; text-transform:uppercase; }
table th { padding-top:.9rem!important; padding-bottom:.9rem!important; font-weight:700; white-space:nowrap; }
table td { padding-top:.9rem!important; padding-bottom:.9rem!important; color:#d5deeb; }
tbody tr { border-color:rgba(255,255,255,.07)!important; }
tbody tr:hover { background:rgba(255,255,255,.035)!important; }
button:not(#menuBtn) { border-radius:.7rem; transition:transform .18s ease,filter .18s ease,box-shadow .18s ease,border-color .18s ease; }
button:not(#menuBtn):hover { filter:brightness(1.08); }
button:not(#menuBtn):active { transform:translateY(1px) scale(.99); }
button:focus-visible, a:focus-visible { outline:2px solid rgba(132,240,220,.75); outline-offset:3px; }
button[class*="bg-blue-"], button[class*="bg-green-"], button[class*="bg-indigo-"], a[class*="bg-blue-"], a[class*="bg-green-"], a[class*="bg-indigo-"] { border:1px solid rgba(132,240,220,.18); background:linear-gradient(115deg,#9af2d5,#67d9f3)!important; color:#07151b!important; font-weight:700; box-shadow:0 8px 24px rgba(45,184,177,.12); }
button[class*="bg-yellow-"], a[class*="bg-yellow-"] { border:1px solid rgba(105,217,220,.2); background:rgba(88,199,203,.12)!important; color:#a8f1e8!important; font-weight:650; }
button[class*="bg-red-"], button[class*="bg-rose-"], a[class*="bg-red-"], a[class*="bg-rose-"] { border:1px solid rgba(251,126,150,.2); background:rgba(190,70,99,.15)!important; color:#ffb7c6!important; font-weight:650; }
button[class*="bg-gray-"], a[class*="bg-gray-"] { border:1px solid rgba(255,255,255,.1); background:rgba(255,255,255,.07)!important; color:#cbd5e1!important; }
button[class*="bg-blue-"]:hover, button[class*="bg-green-"]:hover, a[class*="bg-blue-"]:hover, a[class*="bg-green-"]:hover { box-shadow:0 10px 30px rgba(45,184,177,.24); }
button[class*="bg-red-"]:hover, button[class*="bg-rose-"]:hover, a[class*="bg-red-"]:hover, a[class*="bg-rose-"]:hover { background:rgba(190,70,99,.25)!important; }
div.bg-gray-900 { border:1px solid rgba(255,255,255,.1); background:linear-gradient(145deg,#1a2940,#10192a)!important; box-shadow:0 28px 80px rgba(0,0,0,.48); }
input:not([type=checkbox]), select, textarea { border-color:rgba(255,255,255,.12)!important; background-color:rgba(255,255,255,.045)!important; color:#f5f7ff!important; }
input::placeholder, textarea::placeholder { color:#71809a!important; }
input:focus, select:focus, textarea:focus { border-color:rgba(117,232,216,.55)!important; box-shadow:0 0 0 3px rgba(117,232,216,.09)!important; outline:none!important; }
button, a { -webkit-tap-highlight-color:transparent; }
@media (prefers-reduced-motion:reduce) { *,*::before,*::after { scroll-behavior:auto!important; transition-duration:.01ms!important; animation-duration:.01ms!important; } }
@media (max-width:700px) { table { min-width:680px; } table td,table th { padding-left:.75rem!important; padding-right:.75rem!important; } }
</style>
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
