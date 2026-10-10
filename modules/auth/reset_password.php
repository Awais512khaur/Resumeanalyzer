<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
require_once __DIR__ . '/../../config/config.php';
if (empty($_SESSION['password_reset_email'])) {
    $_SESSION['password_reset_error'] = 'Start by entering your account email address.';
    header('Location: ' . BASE_URL . 'index.php?page=forgot_password');
    exit;
}
if (!empty($_SESSION['password_reset_user_id']) && time() - (int)($_SESSION['password_reset_verified_at'] ?? 0) > 600) {
    unset($_SESSION['password_reset_user_id'], $_SESSION['password_reset_verified_at']);
    $_SESSION['password_reset_error'] = 'Your verification expired. Request a new code.';
    header('Location: ' . BASE_URL . 'index.php?page=forgot_password');
    exit;
}
if (empty($_SESSION['password_reset_csrf'])) {
    $_SESSION['password_reset_csrf'] = bin2hex(random_bytes(32));
}
$verified = !empty($_SESSION['password_reset_user_id']);
$error = $_SESSION['password_reset_error'] ?? '';
$notice = $_SESSION['password_reset_notice'] ?? '';
unset($_SESSION['password_reset_error'], $_SESSION['password_reset_notice']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><meta name="theme-color" content="#080d1b">
<title><?php echo $verified ? 'Set a new password' : 'Verify reset code'; ?> — ResumeAI</title>
<?php include_once __DIR__ . '/../../assets/CDN/tailwind.php'; ?>
<style>body{margin:0;min-height:100vh;color:#f5f7ff;background:radial-gradient(ellipse at 18% 35%,rgba(72,104,255,.14),transparent 38%),radial-gradient(ellipse at 88% 70%,rgba(9,212,180,.12),transparent 35%),#080d1b;font-family:Inter,ui-sans-serif,system-ui,sans-serif}.auth-panel{border:1px solid rgba(255,255,255,.11);background:linear-gradient(145deg,rgba(25,36,59,.9),rgba(13,20,36,.94));box-shadow:0 30px 90px rgba(0,0,0,.42)}.auth-input{width:100%;border:1px solid rgba(255,255,255,.11);border-radius:.8rem;background:rgba(255,255,255,.045);padding:.85rem 1rem;color:#f5f7ff;outline:none}.auth-input:focus{border-color:rgba(117,232,216,.58);box-shadow:0 0 0 3px rgba(117,232,216,.1)}.submit-btn{color:#06131a;background:linear-gradient(115deg,#9af2d5,#67d9f3)}</style>
</head>
<body class="flex min-h-screen flex-col">
<header class="mx-auto flex w-full max-w-7xl items-center justify-between px-5 py-5 sm:px-8"><a href="<?php echo BASE_URL; ?>" class="font-bold">Resume<span class="text-teal-200">AI</span></a><a href="<?php echo BASE_URL; ?>login" class="text-sm text-slate-400 hover:text-white">← Back to login</a></header>
<main class="flex flex-1 items-center justify-center px-5 py-10">
  <section class="auth-panel w-full max-w-md rounded-3xl p-7 sm:p-9">
    <p class="text-xs font-bold uppercase tracking-[.2em] text-teal-200/80">Account recovery</p>
    <h1 class="mt-2 text-3xl font-bold"><?php echo $verified ? 'Choose a new password' : 'Check your email'; ?></h1>
    <p class="mt-3 text-sm leading-6 text-slate-400"><?php echo $verified ? 'Your code is verified. Set a new password for your account.' : 'Enter the six-digit code sent to ' . htmlspecialchars($_SESSION['password_reset_email'], ENT_QUOTES, 'UTF-8') . '.'; ?></p>
    <?php if ($notice !== ''): ?><div role="status" class="mt-5 rounded-xl border border-teal-200/15 bg-teal-200/[.06] px-4 py-3 text-sm text-teal-100"><?php echo htmlspecialchars($notice, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
    <?php if ($error !== ''): ?><div role="alert" class="mt-5 rounded-xl border border-rose-300/20 bg-rose-300/[.07] px-4 py-3 text-sm text-rose-100"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></div><?php endif; ?>
    <?php if (!$verified): ?>
      <form action="<?php echo BASE_URL; ?>modules/auth/verify_password_otp.php" method="POST" class="mt-6 space-y-4">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['password_reset_csrf'], ENT_QUOTES, 'UTF-8'); ?>">
        <div><label for="otp" class="mb-2 block text-sm font-medium text-slate-300">One-time code</label><input id="otp" name="otp" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" autocomplete="one-time-code" required class="auth-input text-center text-2xl tracking-[.45em]" placeholder="000000"></div>
        <button type="submit" class="submit-btn w-full rounded-xl px-5 py-3.5 font-bold">Verify code</button>
      </form>
      <form action="<?php echo BASE_URL; ?>modules/auth/process_forgot_password.php" method="POST" class="mt-3 text-center">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['password_reset_csrf'], ENT_QUOTES, 'UTF-8'); ?>"><input type="hidden" name="email" value="<?php echo htmlspecialchars($_SESSION['password_reset_email'], ENT_QUOTES, 'UTF-8'); ?>">
        <button type="submit" class="text-sm font-medium text-teal-200 hover:text-teal-100">Send a new code</button>
      </form>
    <?php else: ?>
      <form action="<?php echo BASE_URL; ?>modules/auth/process_password_reset.php" method="POST" class="mt-6 space-y-4">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['password_reset_csrf'], ENT_QUOTES, 'UTF-8'); ?>">
        <div><label for="new_password" class="mb-2 block text-sm font-medium text-slate-300">New password</label><div class="relative"><input id="new_password" name="password" type="password" minlength="12" autocomplete="new-password" required class="auth-input pr-16" placeholder="At least 12 characters"><button type="button" data-password-toggle aria-controls="new_password" aria-label="Show password" class="absolute inset-y-0 right-3 my-auto h-9 rounded-lg px-2 text-sm font-semibold text-teal-200">Show</button></div></div>
        <div><label for="confirm_password" class="mb-2 block text-sm font-medium text-slate-300">Confirm new password</label><div class="relative"><input id="confirm_password" name="password_confirmation" type="password" minlength="12" autocomplete="new-password" required class="auth-input pr-16" placeholder="Enter it again"><button type="button" data-password-toggle aria-controls="confirm_password" aria-label="Show password" class="absolute inset-y-0 right-3 my-auto h-9 rounded-lg px-2 text-sm font-semibold text-teal-200">Show</button></div></div>
        <button type="submit" class="submit-btn w-full rounded-xl px-5 py-3.5 font-bold">Save new password</button>
      </form>
      <script src="<?php echo BASE_URL; ?>assets/js/password-toggle.js" defer></script>
    <?php endif; ?>
  </section>
</main>
</body>
</html>
