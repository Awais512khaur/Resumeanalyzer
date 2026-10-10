<?php
// session_start();
require_once __DIR__ . "/../../config/config.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Register</title>

<?php include_once __DIR__ . '/../../assets/cdn/tailwind.php'; ?>

<style>
body {
    background: linear-gradient(135deg, #0f172a, #1e293b);
}
</style>
</head>

<body class="text-white">

<div class="min-h-screen flex items-center justify-center px-4">

    <div class="bg-white/10 backdrop-blur-lg p-8 rounded-2xl shadow-2xl w-full max-w-md animate-slide-in">

        <h2 class="text-3xl font-bold text-center mb-6">Create Account</h2>

        <!-- ERROR MESSAGE -->
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="bg-red-500/20 border border-red-500 text-red-300 p-3 rounded mb-4 text-center">
                <?php 
                    echo $_SESSION['error']; 
                    unset($_SESSION['error']); 
                ?>
            </div>
        <?php endif; ?>

        <form action="<?php echo BASE_URL; ?>modules/auth/process_register.php" method="POST" class="space-y-4">

            <input type="text" name="name" placeholder="Full Name" required
                class="w-full p-3 rounded-lg bg-white/20 focus:outline-none text-white placeholder-gray-300">

            <input type="email" name="email" placeholder="Email" required
                class="w-full p-3 rounded-lg bg-white/20 focus:outline-none text-white placeholder-gray-300">

            <input type="password" name="password" placeholder="Password" required
                class="w-full p-3 rounded-lg bg-white/20 focus:outline-none text-white placeholder-gray-300">

            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 transition p-3 rounded-lg font-bold">
                Register
            </button>
        </form>

        <p class="text-center mt-4 text-gray-300">
            Already have an account?
            <a href="<?php echo BASE_URL; ?>login" class="text-blue-400 hover:underline">
                Login
            </a>
        </p>

    </div>

</div>

<style>
@keyframes slide-in {
    from {opacity:0; transform: translateY(30px);}
    to {opacity:1;}
}
.animate-slide-in {
    animation: slide-in 0.7s ease-out;
}
</style>

</body>
</html>