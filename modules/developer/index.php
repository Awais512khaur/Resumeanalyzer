<?php
require_once __DIR__ . "/../../templates/header.php";
?>
<main class="mx-auto w-full max-w-5xl px-4 pb-16 pt-24 sm:px-6 lg:px-8">
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
