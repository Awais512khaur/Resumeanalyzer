<?php
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once __DIR__ . "/../../config/config.php";
$auth_error = $_SESSION['error'] ?? '';
$password_reset_success = $_SESSION['password_reset_success'] ?? '';
$account_locked = stripos($auth_error, 'locked') !== false;
unset($_SESSION['error'], $_SESSION['password_reset_success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#080d1b">
<title>Log in — ResumeAI</title>
<link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>assets/images/favicon.svg?v=2">
<link rel="icon" type="image/png" sizes="64x64" href="<?php echo BASE_URL; ?>assets/images/favicon.png?v=2">
<link rel="shortcut icon" type="image/x-icon" href="<?php echo BASE_URL; ?>favicon.ico?v=2">
<?php include_once __DIR__ . '/../../assets/CDN/tailwind.php'; ?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/auth-login.css?v=1">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/site-detail.css?v=1">
</head>
<body>
<div class="auth-grid pointer-events-none fixed inset-0" aria-hidden="true"></div>
<canvas class="neural-network auth-network" data-neural-network data-fixed="true" aria-hidden="true"></canvas>
<header class="relative z-10 mx-auto flex max-w-7xl items-center justify-between px-5 py-5 sm:px-8">
  <a href="<?php echo BASE_URL; ?>" class="flex items-center gap-2.5 font-bold tracking-tight"><span class="brand-mark" aria-hidden="true"><svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M7 3.75h7l4 4V20a.75.75 0 0 1-.75.75h-10.5A.75.75 0 0 1 6 20V4.5a.75.75 0 0 1 .75-.75Z" stroke="currentColor" stroke-width="1.8"/><path d="M14 4v4h4M9 12h6M9 15.5h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span><span class="text-lg">Resume<span class="text-teal-200">AI</span></span></a>
  <a href="<?php echo BASE_URL; ?>" class="text-sm text-slate-400 transition hover:text-white">← Back to home</a>
</header>
<main class="relative z-10 flex min-h-[calc(100vh-80px)] items-center justify-center px-5 pb-12 pt-4">
  <section class="auth-panel entrance w-full max-w-md rounded-3xl p-7 sm:p-9">
    <div class="mb-7 text-center"><div class="mx-auto mb-5 grid h-12 w-12 place-items-center rounded-2xl border border-teal-200/15 bg-teal-200/[.07] text-teal-200"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM5 20a7 7 0 0 1 14 0" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></div><p class="text-xs font-bold uppercase tracking-[.2em] text-teal-200/80">Welcome back</p><h1 class="mt-2 text-3xl font-bold tracking-tight">Good to see you.</h1><p class="mt-2 text-sm text-slate-400">Log in to continue working on your next step.</p></div>
    <?php if ($auth_error !== ''): ?><div role="alert" class="mb-5 flex gap-3 rounded-xl border px-4 py-3 text-sm <?php echo $account_locked ? 'border-amber-300/20 bg-amber-300/[.07] text-amber-100' : 'border-red-400/20 bg-red-400/[.08] text-red-200'; ?>"><span class="mt-0.5" aria-hidden="true"><?php echo $account_locked ? '🔒' : '⚠'; ?></span><span><?php echo htmlspecialchars($auth_error, ENT_QUOTES, 'UTF-8'); ?></span></div><?php endif; ?>
    <?php if ($password_reset_success !== ''): ?><div role="status" class="mb-5 rounded-xl border border-teal-200/15 bg-teal-200/[.06] px-4 py-3 text-sm text-teal-100"><?php echo htmlspecialchars($password_reset_success, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
    <form action="<?php echo BASE_URL; ?>modules/auth/process_login.php" method="POST" class="space-y-5">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
      <div><label for="email" class="mb-2 block text-sm font-medium text-slate-300">Email address</label><input id="email" type="email" name="email" placeholder="you@example.com" autocomplete="email" required class="auth-input"></div>
      <div><label for="password" class="mb-2 block text-sm font-medium text-slate-300">Password</label><div class="relative"><input id="password" type="password" name="password" placeholder="Enter your password" autocomplete="current-password" required class="auth-input pr-16"><button type="button" data-password-toggle aria-controls="password" aria-label="Show password" class="absolute inset-y-0 right-3 my-auto h-9 rounded-lg px-2 text-sm font-semibold text-teal-200 transition hover:bg-white/[.06] hover:text-white">Show</button></div></div>
      <div class="-mt-2 text-right"><a href="<?php echo BASE_URL; ?>index.php?page=forgot_password" class="text-sm font-medium text-teal-200 transition hover:text-teal-100">Forgot password?</a></div>
      <button type="submit" class="submit-btn mt-1 w-full rounded-xl px-5 py-3.5 font-bold transition">Log in <span aria-hidden="true">→</span></button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-400">New to ResumeAI? <a href="<?php echo BASE_URL; ?>register" class="font-semibold text-teal-200 transition hover:text-teal-100">Create an account</a></p>
  </section>
</main>
<script src="<?php echo BASE_URL; ?>assets/js/password-toggle.js" defer></script>
<script src="<?php echo BASE_URL; ?>assets/js/neural-network.js" defer></script>
</body>
</html>
