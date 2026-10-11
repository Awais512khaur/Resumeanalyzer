<?php
require_once __DIR__ . "/../../config/config.php";
$auth_error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#080d1b">
<title>Create account — ResumeAI</title>
<link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>assets/images/favicon.svg?v=2">
<link rel="icon" type="image/png" sizes="64x64" href="<?php echo BASE_URL; ?>assets/images/favicon.png?v=2">
<link rel="shortcut icon" type="image/x-icon" href="<?php echo BASE_URL; ?>favicon.ico?v=2">
<?php include_once __DIR__ . '/../../assets/CDN/tailwind.php'; ?>
<style>
*{box-sizing:border-box}body{margin:0;min-height:100vh;color:#f5f7ff;background:radial-gradient(ellipse at 18% 35%,rgba(72,104,255,.14),transparent 38%),radial-gradient(ellipse at 88% 70%,rgba(9,212,180,.12),transparent 35%),#080d1b;font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}.auth-grid{background-image:linear-gradient(rgba(142,166,208,.045) 1px,transparent 1px),linear-gradient(90deg,rgba(142,166,208,.045) 1px,transparent 1px);background-size:58px 58px;mask-image:linear-gradient(to bottom,black,transparent 90%)}.brand-mark{display:grid;place-items:center;width:2rem;height:2rem;border-radius:.7rem;color:#07131e;background:linear-gradient(145deg,#83ffe4,#49c8fc);box-shadow:0 0 25px rgba(73,200,252,.3)}.auth-panel{border:1px solid rgba(255,255,255,.11);background:linear-gradient(145deg,rgba(25,36,59,.84),rgba(13,20,36,.88));box-shadow:0 30px 90px rgba(0,0,0,.42),inset 0 1px rgba(255,255,255,.08);backdrop-filter:blur(24px)}.auth-input{width:100%;border:1px solid rgba(255,255,255,.11);border-radius:.8rem;background:rgba(255,255,255,.045);padding:.85rem 1rem;color:#f5f7ff;outline:none;transition:border-color .2s,box-shadow .2s,background .2s}.auth-input::placeholder{color:#71809a}.auth-input:focus{border-color:rgba(117,232,216,.58);background:rgba(255,255,255,.065);box-shadow:0 0 0 3px rgba(117,232,216,.1)}.submit-btn{color:#06131a;background:linear-gradient(115deg,#9af2d5,#67d9f3);box-shadow:0 10px 35px rgba(67,207,206,.18),inset 0 1px rgba(255,255,255,.55)}.submit-btn:hover{transform:translateY(-2px);box-shadow:0 15px 42px rgba(67,207,206,.3),inset 0 1px rgba(255,255,255,.55)}.entrance{animation:enter .65s cubic-bezier(.2,.75,.3,1) both}@keyframes enter{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}@media(prefers-reduced-motion:reduce){*,*::before,*::after{animation-duration:.01ms!important;transition-duration:.01ms!important}}
.auth-network{position:fixed;inset:0;z-index:0;width:100%;height:100%;pointer-events:none;opacity:.62}
</style>
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
    <div class="mb-7 text-center"><div class="mx-auto mb-5 grid h-12 w-12 place-items-center rounded-2xl border border-teal-200/15 bg-teal-200/[.07] text-teal-200"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2M9.5 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8ZM19 8v6M22 11h-6" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></div><p class="text-xs font-bold uppercase tracking-[.2em] text-teal-200/80">Start with a stronger story</p><h1 class="mt-2 text-3xl font-bold tracking-tight">Create your account.</h1><p class="mt-2 text-sm text-slate-400">Set up your space and get your resume ready.</p></div>
    <?php if ($auth_error !== ''): ?><div role="alert" class="mb-5 rounded-xl border border-red-400/20 bg-red-400/[.08] px-4 py-3 text-sm text-red-200"><?php echo htmlspecialchars($auth_error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
    <form action="<?php echo BASE_URL; ?>modules/auth/process_register.php" method="POST" class="space-y-5">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
      <div><label for="name" class="mb-2 block text-sm font-medium text-slate-300">Full name</label><input id="name" type="text" name="name" placeholder="Your name" autocomplete="name" required class="auth-input"></div>
      <div><label for="email" class="mb-2 block text-sm font-medium text-slate-300">Email address</label><input id="email" type="email" name="email" placeholder="you@example.com" autocomplete="email" required class="auth-input"></div>
      <div><label for="password" class="mb-2 block text-sm font-medium text-slate-300">Password</label><div class="relative"><input id="password" type="password" name="password" minlength="12" placeholder="At least 12 characters" autocomplete="new-password" required class="auth-input pr-16"><button type="button" data-password-toggle aria-controls="password" aria-label="Show password" class="absolute inset-y-0 right-3 my-auto h-9 rounded-lg px-2 text-sm font-semibold text-teal-200 transition hover:bg-white/[.06] hover:text-white">Show</button></div></div>
      <div><label for="password_confirmation" class="mb-2 block text-sm font-medium text-slate-300">Confirm password</label><div class="relative"><input id="password_confirmation" type="password" name="password_confirmation" minlength="12" placeholder="Enter your password again" autocomplete="new-password" required class="auth-input pr-16"><button type="button" data-password-toggle aria-controls="password_confirmation" aria-label="Show password" class="absolute inset-y-0 right-3 my-auto h-9 rounded-lg px-2 text-sm font-semibold text-teal-200 transition hover:bg-white/[.06] hover:text-white">Show</button></div></div>
      <button type="submit" class="submit-btn mt-1 w-full rounded-xl px-5 py-3.5 font-bold transition">Create account <span aria-hidden="true">→</span></button>
    </form>
    <p class="mt-6 text-center text-sm text-slate-400">Already have an account? <a href="<?php echo BASE_URL; ?>login" class="font-semibold text-teal-200 transition hover:text-teal-100">Log in</a></p>
  </section>
</main>
<script src="<?php echo BASE_URL; ?>assets/js/password-toggle.js" defer></script>
<script src="<?php echo BASE_URL; ?>assets/js/neural-network.js" defer></script>
</body>
</html>
