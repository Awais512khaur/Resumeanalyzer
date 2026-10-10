<?php
require_once __DIR__ . '/../../config/config.php';
if (empty($_SESSION['password_reset_csrf'])) {
    $_SESSION['password_reset_csrf'] = bin2hex(random_bytes(32));
}
$error = $_SESSION['password_reset_error'] ?? '';
unset($_SESSION['password_reset_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="theme-color" content="#080d1b">
<title>Forgot password — ResumeAI</title>
<?php include_once __DIR__ . '/../../assets/CDN/tailwind.php'; ?>
<style>body{margin:0;min-height:100vh;color:#f5f7ff;background:radial-gradient(ellipse at 18% 35%,rgba(72,104,255,.14),transparent 38%),radial-gradient(ellipse at 88% 70%,rgba(9,212,180,.12),transparent 35%),#080d1b;font-family:Inter,ui-sans-serif,system-ui,sans-serif}.auth-panel{border:1px solid rgba(255,255,255,.11);background:linear-gradient(145deg,rgba(25,36,59,.9),rgba(13,20,36,.94));box-shadow:0 30px 90px rgba(0,0,0,.42)}.auth-input{width:100%;border:1px solid rgba(255,255,255,.11);border-radius:.8rem;background:rgba(255,255,255,.045);padding:.85rem 1rem;color:#f5f7ff;outline:none}.auth-input:focus{border-color:rgba(117,232,216,.58);box-shadow:0 0 0 3px rgba(117,232,216,.1)}.submit-btn{color:#06131a;background:linear-gradient(115deg,#9af2d5,#67d9f3)}</style>
</head>
<body class="flex min-h-screen flex-col">
<header class="mx-auto flex w-full max-w-7xl items-center justify-between px-5 py-5 sm:px-8"><a href="<?php echo BASE_URL; ?>" class="font-bold">Resume<span class="text-teal-200">AI</span></a><a href="<?php echo BASE_URL; ?>login" class="text-sm text-slate-400 hover:text-white">← Back to login</a></header>
<main class="flex flex-1 items-center justify-center px-5 py-10">
  <section class="auth-panel w-full max-w-md rounded-3xl p-7 sm:p-9">
    <p class="text-xs font-bold uppercase tracking-[.2em] text-teal-200/80">Account recovery</p>
    <h1 class="mt-2 text-3xl font-bold">Forgot your password?</h1>
    <p class="mt-3 text-sm leading-6 text-slate-400">Enter the email address linked to your account. We’ll send a one-time code to your inbox.</p>
    <?php if ($error !== ''): ?><div role="alert" class="mt-5 rounded-xl border border-rose-300/20 bg-rose-300/[.07] px-4 py-3 text-sm text-rose-100"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
    <form action="<?php echo BASE_URL; ?>modules/auth/process_forgot_password.php" method="POST" class="mt-6 space-y-4">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['password_reset_csrf'], ENT_QUOTES, 'UTF-8'); ?>">
      <div><label for="email" class="mb-2 block text-sm font-medium text-slate-300">Email address</label><input id="email" name="email" type="email" autocomplete="email" required class="auth-input" placeholder="you@example.com"></div>
      <button type="submit" class="submit-btn w-full rounded-xl px-5 py-3.5 font-bold">Send reset code <span aria-hidden="true">→</span></button>
    </form>
  </section>
</main>
</body>
</html>
