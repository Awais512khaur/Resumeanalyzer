<?php
require_once __DIR__ . "/../../templates/header.php";
?>
<?php if (empty($_SESSION['user_id'])): ?>
<header class="app-nav sticky top-0 z-20">
    <nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 sm:px-8" aria-label="Main navigation">
        <a href="<?php echo BASE_URL; ?>" class="flex items-center gap-2.5 font-bold tracking-tight" aria-label="ResumeAI home">
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-gradient-to-br from-teal-200 to-sky-400 text-slate-950 shadow-[0_0_25px_rgba(73,200,252,.3)]" aria-hidden="true">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M7 3.75h7l4 4V20a.75.75 0 0 1-.75.75h-10.5A.75.75 0 0 1 6 20V4.5a.75.75 0 0 1 .75-.75Z" stroke="currentColor" stroke-width="1.8"/><path d="M14 4v4h4M9 12h6M9 15.5h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
            </span>
            <span class="text-lg">Resume<span class="text-teal-200">AI</span></span>
        </a>
        <div class="flex items-center gap-2 sm:gap-3">
            <a href="<?php echo BASE_URL; ?>login" class="rounded-xl px-4 py-2 text-sm font-medium text-slate-300 transition hover:text-white">Log in</a>
            <a href="<?php echo BASE_URL; ?>register" class="rounded-xl border border-white/10 bg-white/[.07] px-4 py-2 text-sm font-semibold transition hover:border-teal-200/30 hover:bg-white/10">Create account <span aria-hidden="true">↗</span></a>
        </div>
    </nav>
</header>
<?php endif; ?>
<main class="mx-auto w-full max-w-5xl px-4 pb-16 pt-8 sm:px-6 lg:px-8">
    <section class="surface-card relative overflow-hidden rounded-3xl px-6 py-8 text-center sm:px-12 sm:py-12">
        <div aria-hidden="true" class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full bg-teal-300/10 blur-3xl"></div>
        <div aria-hidden="true" class="pointer-events-none absolute -bottom-28 -left-16 h-72 w-72 rounded-full bg-sky-400/10 blur-3xl"></div>
        <div class="relative mx-auto max-w-3xl">
            <p class="mb-5 text-xs font-bold uppercase tracking-[.22em] text-teal-200">Meet the developer</p>
            <div class="mx-auto mb-6 h-40 w-40 overflow-hidden rounded-full border-4 border-teal-200/30 shadow-[0_0_55px_rgba(103,217,243,.2)] sm:h-48 sm:w-48">
                <img src="<?php echo BASE_URL; ?>assets/images/my.png" alt="Muhammad Awais" class="h-full w-full object-cover object-top">
            </div>
            <h1 class="text-3xl font-bold tracking-tight text-white sm:text-5xl">Muhammad Awais</h1>
            <p class="mt-3 text-lg font-medium text-teal-100 sm:text-xl">Software Engineer · Front-End &amp; Back-End Developer</p>
            <p class="mx-auto mt-6 max-w-2xl text-base leading-8 text-slate-300">
                I’m a software engineer who enjoys turning ideas into reliable, user-friendly applications. I work across the full development process—from crafting clear, responsive interfaces to building the server-side logic and databases that power them. I focus on practical solutions, thoughtful details, and code that is easy to maintain.
            </p>
            <div class="mt-9">
                <p class="mb-4 text-xs font-bold uppercase tracking-[.18em] text-slate-400">Technologies I work with</p>
                <div class="flex flex-wrap justify-center gap-2">
                    <?php foreach (['Core PHP', 'Laravel', '.NET', 'MySQL', 'HTML', 'CSS', 'JavaScript', 'Alpine.js'] as $skill): ?>
                        <span class="rounded-full border border-white/10 bg-white/[.04] px-3.5 py-2 text-sm font-medium text-slate-200"><?php echo htmlspecialchars($skill, ENT_QUOTES, 'UTF-8'); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="mt-9 flex flex-col items-center gap-4">
                <p class="text-sm text-slate-400">Connect with me</p>
                <?php require __DIR__ . '/../../templates/social_links.php'; ?>
            </div>
        </div>
    </section>
</main>
<?php require_once __DIR__ . "/../../templates/footer.php"; ?>
