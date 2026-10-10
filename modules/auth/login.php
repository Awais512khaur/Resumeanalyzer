<?php
// session_start();
// 🔒 PREVENT BACK CACHE
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once __DIR__ . "/../../config/config.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login</title>

<?php include_once __DIR__ . '/../../assets/cdn/tailwind.php'; ?>

<style>
body {
    background: linear-gradient(135deg, #0f172a, #1e293b);
}
</style>
</head>

<body class="text-white">

<div class="min-h-screen flex items-center justify-center">

    <div class="bg-white/10 backdrop-blur-lg p-8 rounded-2xl shadow-2xl w-full max-w-md animate-slide-in">

        <h2 class="text-3xl font-bold text-center mb-6">Welcome Back</h2>

        <!-- ERROR MESSAGE -->
        <?php if (!empty($_SESSION['error'])): ?>
            <p class="text-red-400 text-center mb-4">
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']); 
                ?>
            </p>
        <?php endif; ?>

        <!-- IMPORTANT FIX: direct relative path -->
        <form action="modules/auth/process_login.php" method="POST" class="space-y-4">

            <input type="email" name="email" placeholder="Email" required
                class="w-full p-3 rounded-lg bg-white/20 focus:outline-none text-white">

            <input type="password" name="password" placeholder="Password" required
                class="w-full p-3 rounded-lg bg-white/20 focus:outline-none text-white">

            <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 transition p-3 rounded-lg font-bold">
                Login
            </button>
        </form>

        <p class="text-center mt-4">
            Don't have an account?
            <a href="<?php echo BASE_URL; ?>register" class="text-green-400">Register</a>
        </p>

    </div>

</div>

<style>
@keyframes slide-in {
    from {opacity:0; transform: translateX(50px);}
    to {opacity:1;}
}
.animate-slide-in {
    animation: slide-in 0.7s ease-out;
}
</style>

</body>
</html>