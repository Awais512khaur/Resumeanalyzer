<?php
session_start();
require_once __DIR__ . "/config/config.php";
require_once __DIR__ . "/config/db.php";
// If no page → show landing page
if (!isset($_GET['page'])) {
    require __DIR__ . "/modules/home/index.php";
    exit;
}
// Get page
$page = trim($_GET['page'], '/');
// ROUTING
switch ($page) {
    case 'login':
        require __DIR__ . "/modules/auth/login.php";
        break;
    case 'register':
        require __DIR__ . "/modules/auth/register.php";
        break;
    case 'dashboard':
        require __DIR__ . "/modules/dashboard/index.php";
        break;
    case 'logout':
        require __DIR__ . "/modules/auth/logout.php";
        break;
    case 'upload':
        require __DIR__ . "/modules/resume/upload.php";
        break;
    case 'analyze':
        require __DIR__ . "/modules/analysis/analyze.php";
        break;
    case 'resumes':
        require __DIR__ . "/modules/resume/index.php";
        break;
    case 'history':
        require __DIR__ . "/modules/history/index.php";
        break;
    case 'admin':
        require __DIR__ . "/modules/admin/index.php";
        break;
    case 'admin_users':
        require __DIR__ . "/modules/admin/users.php";
        break;
    case 'admin_resumes':
        require __DIR__ . "/modules/admin/resumes.php";
        break;
    case 'analyze_admin':
        require __DIR__ . "/modules/admin/analyze_admin.php";
        break;
    case 'admin_analysis':
        require __DIR__ . "/modules/admin/analysis.php";
        break;
    case 'admin_positions':
        require __DIR__ . "/modules/admin/positions.php";
        break;
    case 'admin_user':
       require __DIR__ . "/modules/admin/user_detail/index.php";
       break;
    default:
        require __DIR__ . "/modules/home/index.php";
        break;
}