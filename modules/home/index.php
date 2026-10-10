<?php
require_once __DIR__ . "/../../config/config.php";
// 🔒 PREVENT BACK CACHE
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Resume Analyzer</title>

<?php include_once __DIR__ . '/../../assets/CDN/tailwind.php'; ?>

<style>
body {
    background: linear-gradient(135deg, #0f172a, #1e293b);
}

@keyframes fadeIn {
    from {opacity:0; transform: translateY(30px);}
    to {opacity:1;}
}
.fade-in {
    animation: fadeIn 1s ease;
}

@keyframes float {
    0% {transform: translateY(0px);}
    50% {transform: translateY(-10px);}
    100% {transform: translateY(0px);}
}
.float {
    animation: float 3s ease-in-out infinite;
}
</style>
</head>

<body class="text-white">

<!-- HEADER -->
<header class="flex justify-between items-center px-6 py-4 bg-white/10 backdrop-blur-lg">
    
    <h1 class="text-2xl font-bold">ResumeAI</h1>

    <div class="space-x-4">
        <a href="<?php echo BASE_URL; ?>login"
           class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 transition">
            Login
        </a>

        <a href="<?php echo BASE_URL; ?>register"
           class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 transition">
            Register
        </a>
    </div>

</header>

<!-- HERO -->
<!-- HERO SECTION --> 
 <section class="min-h-screen flex flex-col justify-center items-center text-center px-4"> 
    <div class="fade-in max-w-3xl"> 
        <h2 class="text-4xl md:text-6xl font-bold mb-6 leading-tight"> Analyze Your Resume <br> 
        <span class="text-blue-400">Get Hired Faster</span> 
    </h2> 
    <p class="text-gray-300 text-lg mb-8"> AI-powered resume analysis that helps you match job descriptions, improve your CV, and increase your chances of landing a job. </p> 
    <div class="space-x-4"> 
        <a href="<?php echo BASE_URL; ?>/modules/auth/register.php" 
        class="px-6 py-3 bg-green-600 hover:bg-green-700 rounded-lg text-lg font-semibold transition"> Get Started </a> 
        <a href="<?php echo BASE_URL; ?>/modules/auth/login.php" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 rounded-lg text-lg font-semibold transition"> Login </a> 
    </div> 
</div> <!-- Floating card --> 
<div class="mt-16 float bg-white/10 backdrop-blur-lg p-6 rounded-xl shadow-xl max-w-md"> 
    <p class="text-lg font-semibold">📊 Resume Score: 85%</p> 
    <p class="text-green-400">✔ Strong match with job</p> 
</div> 
</section>
<!-- FEATURES --> 
 <section class="py-16 px-6"> 
    <div class="max-w-6xl mx-auto grid md:grid-cols-3 gap-8 text-center"> 
        <div class="bg-white/10 p-6 rounded-xl backdrop-blur-lg hover:scale-105 transition"> 
            <h3 class="text-xl font-bold mb-2">Smart Analysis</h3> 
            <p class="text-gray-300">Compare resume with job description instantly</p> 
        </div> 
        <div class="bg-white/10 p-6 rounded-xl backdrop-blur-lg hover:scale-105 transition"> 
            <h3 class="text-xl font-bold mb-2">ATS Friendly</h3> 
            <p class="text-gray-300">Improve your resume for ATS systems</p> 
        </div> 
        <div class="bg-white/10 p-6 rounded-xl backdrop-blur-lg hover:scale-105 transition"> 
            <h3 class="text-xl font-bold mb-2">Track Progress</h3> 
            <p class="text-gray-300">Monitor your resume improvements over time</p> 
        </div> 
    </div> 
</section>
<!-- FOOTER --> 
 <footer class="text-center py-6 bg-white/10 backdrop-blur-lg"> <p class="text-gray-400"> © <?php echo date("Y"); ?> Resume Analyzer. All rights reserved. </p> </footer>
</body>
</html>