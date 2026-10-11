<?php
require_once __DIR__ . "/../../config/config.php";
// Prevent stale landing-page content from being restored from browser cache.
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#080d1b">
<title>ResumeAI — Make your next move</title>
<link rel="icon" type="image/svg+xml" href="<?php echo BASE_URL; ?>assets/images/favicon.svg?v=2">
<link rel="icon" type="image/png" sizes="64x64" href="<?php echo BASE_URL; ?>assets/images/favicon.png?v=2">
<link rel="shortcut icon" type="image/x-icon" href="<?php echo BASE_URL; ?>favicon.ico?v=2">
<?php include_once __DIR__ . '/../../assets/CDN/tailwind.php'; ?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/home.css?v=1">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/site-detail.css?v=1">
</head>
<body>
<div class="scene min-h-screen overflow-hidden">
<div class="grid-glow" aria-hidden="true"></div>
<header class="topbar sticky top-0 z-20">
  <nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 sm:px-8" aria-label="Main navigation">
    <a href="<?php echo BASE_URL; ?>" class="flex items-center gap-2.5 font-bold tracking-tight" aria-label="ResumeAI home">
      <span class="brand-mark" aria-hidden="true"><svg width="17" height="17" viewBox="0 0 24 24" fill="none"><path d="M7 3.75h7l4 4V20a.75.75 0 0 1-.75.75h-10.5A.75.75 0 0 1 6 20V4.5a.75.75 0 0 1 .75-.75Z" stroke="currentColor" stroke-width="1.8"/><path d="M14 4v4h4M9 12h6M9 15.5h6" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></span>
      <span class="text-lg">Resume<span class="text-teal-200">AI</span></span>
    </a>
    <div class="flex items-center gap-2 sm:gap-3">
      <a href="<?php echo BASE_URL; ?>login" class="rounded-xl px-4 py-2 text-sm font-medium text-slate-300 transition hover:text-white">Log in</a>
      <a href="<?php echo BASE_URL; ?>register" class="rounded-xl border border-white/10 bg-white/[.07] px-4 py-2 text-sm font-semibold transition hover:border-teal-200/30 hover:bg-white/10">Create account <span aria-hidden="true">↗</span></a>
    </div>
  </nav>
</header>

<main>
  <section class="relative mx-auto grid max-w-7xl items-center gap-4 px-5 pb-12 pt-12 sm:px-8 md:min-h-[650px] md:grid-cols-[1.02fr_.98fr] md:gap-0 md:pt-8">
    <canvas class="neural-network" data-neural-network aria-hidden="true"></canvas>
    <div class="relative z-10 max-w-2xl py-6 text-center md:text-left">
      <div class="eyebrow rise-in"><span class="eyebrow-dot"></span> Your next opportunity starts here</div>
      <h1 class="headline rise-in delay-1 mt-6 text-5xl font-bold sm:text-6xl lg:text-[4.65rem]">Your experience,<br><span class="headline-accent">in its best light.</span></h1>
      <p class="rise-in delay-2 mx-auto mt-6 max-w-xl text-base leading-7 text-slate-400 sm:text-lg sm:leading-8 md:mx-0">Turn your resume into your strongest introduction. Get clear, AI-powered feedback and see how well your skills match the role you want.</p>
      <div class="rise-in delay-3 mt-8 flex flex-wrap items-center justify-center gap-3 md:justify-start">
        <a href="<?php echo BASE_URL; ?>register" class="primary-cta inline-flex items-center gap-2 rounded-xl px-6 py-3.5 font-bold transition">Analyze my resume <span aria-hidden="true">→</span></a>
        <a href="<?php echo BASE_URL; ?>login" class="secondary-cta rounded-xl px-6 py-3.5 font-semibold text-slate-200 transition">I have an account</a>
      </div>
      <div class="rise-in delay-3 mt-7 flex items-center justify-center gap-2 text-sm text-slate-500 md:justify-start"><span class="text-teal-300" aria-hidden="true">✦</span> Helpful insights. A more confident application.</div>
    </div>

    <div class="visual-stage" aria-label="Illustration of a resume being analyzed">
      <div class="orb" aria-hidden="true"></div><div class="orbit" aria-hidden="true"></div>
      <div class="resume-card" aria-hidden="true">
        <div class="resume-top"><div class="avatar">JD</div><div class="flex-1"><div class="mb-2 h-2 w-24 rounded bg-slate-200/80"></div><div class="h-1.5 w-16 rounded bg-slate-500/40"></div></div><div class="rounded-md border border-teal-200/15 bg-teal-200/10 px-2 py-1 text-[9px] font-bold tracking-wider text-teal-200">RESUME</div></div>
        <div class="mt-5"><div class="mb-3 text-[10px] font-bold tracking-[.18em] text-teal-200">PROFILE</div><div class="line bright mb-2 w-full"></div><div class="line mb-2 w-[92%]"></div><div class="line w-[74%]"></div></div>
        <div class="mt-6"><div class="mb-3 text-[10px] font-bold tracking-[.18em] text-teal-200">EXPERIENCE</div><div class="mb-2 flex items-center justify-between"><div class="line bright w-28"></div><div class="line w-12"></div></div><div class="line mb-2 w-full"></div><div class="line mb-2 w-[88%]"></div><div class="line w-[66%]"></div></div>
        <div class="mt-6"><div class="mb-3 text-[10px] font-bold tracking-[.18em] text-teal-200">SKILLS</div><div class="flex flex-wrap gap-2"><span class="rounded-md border border-white/10 bg-white/[.04] px-2 py-1 text-[9px] text-slate-300">Leadership</span><span class="rounded-md border border-white/10 bg-white/[.04] px-2 py-1 text-[9px] text-slate-300">Analytics</span><span class="rounded-md border border-white/10 bg-white/[.04] px-2 py-1 text-[9px] text-slate-300">Communication</span></div></div>
        <div class="mt-6 flex items-center gap-2 rounded-lg border border-teal-200/10 bg-teal-200/[.06] p-2.5"><span class="text-sm text-teal-200">✦</span><div><div class="mb-1 h-1.5 w-24 rounded bg-teal-100/45"></div><div class="h-1 w-32 rounded bg-slate-300/15"></div></div></div>
      </div>
      <div class="match-card" aria-hidden="true"><div class="flex items-center gap-2.5"><span class="grid h-8 w-8 place-items-center rounded-lg bg-indigo-300/10 text-indigo-200">✳</span><div><div class="text-[10px] font-semibold text-slate-200">Role compatibility</div><div class="mt-1 text-[9px] text-slate-400">Skills aligned to your goal</div></div></div></div>
      <div class="score-card" aria-hidden="true"><div class="flex items-center gap-3"><div class="score-ring">85%</div><div><div class="text-xs font-bold text-white">Strong match</div><div class="mt-1 text-[10px] text-emerald-200">Looking good ✦</div></div></div></div>
    </div>
  </section>

  <section class="mx-auto max-w-7xl px-5 pb-20 pt-6 sm:px-8" aria-labelledby="features-heading">
    <div class="mb-8 text-center"><p class="text-xs font-bold uppercase tracking-[.2em] text-teal-200/80">A clearer way forward</p><h2 id="features-heading" class="mt-3 text-2xl font-bold tracking-tight sm:text-3xl">Small improvements. Stronger applications.</h2></div>
    <div class="grid gap-4 md:grid-cols-3">
      <article class="feature-card rounded-2xl p-6"><div class="feature-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m12 3 1.9 5.8L20 11l-6.1 2.2L12 19l-1.9-5.8L4 11l6.1-2.2L12 3Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="m19 15 .9 2.1L22 18l-2.1.9L19 21l-.9-2.1L16 18l2.1-.9L19 15Z" fill="currentColor"/></svg></div><h3 class="mt-5 text-lg font-bold">Useful AI feedback</h3><p class="mt-2 text-sm leading-6 text-slate-400">See clear suggestions that help you improve your resume and highlight what you do best.</p></article>
      <article class="feature-card rounded-2xl p-6"><div class="feature-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M5 19V5m0 14h14M8 15l3-4 3 2 5-7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 6h3v3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></div><h3 class="mt-5 text-lg font-bold">Role-specific matching</h3><p class="mt-2 text-sm leading-6 text-slate-400">Compare your experience with a job description and understand where you align.</p></article>
      <article class="feature-card rounded-2xl p-6"><div class="feature-icon"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M7 3.75h7l4 4V20a.75.75 0 0 1-.75.75h-10.5A.75.75 0 0 1 6 20V4.5a.75.75 0 0 1 .75-.75Z" stroke="currentColor" stroke-width="1.7"/><path d="M14 4v4h4M9 12h6M9 15.5h6M9 19h3" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></div><h3 class="mt-5 text-lg font-bold">Progress you can see</h3><p class="mt-2 text-sm leading-6 text-slate-400">Keep track of your resume reviews as you refine your story for each opportunity.</p></article>
    </div>
  </section>
</main>
<section class="border-t border-white/[.07] px-5 py-8 text-center" aria-label="Meet the developer">
  <a href="<?php echo BASE_URL; ?>developer" class="inline-flex items-center gap-2 rounded-xl border border-teal-200/20 bg-teal-200/[.07] px-5 py-3 text-sm font-semibold text-teal-100 transition hover:bg-teal-200/[.12]">Meet the developer <span aria-hidden="true">→</span></a>
</section>
<footer class="border-t border-white/[.07] bg-[#080d1b]/60 px-5 py-6">
  <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 sm:flex-row">
    <p class="text-sm text-slate-500">© <?php echo date("Y"); ?> Resume Analyzer. Built for your next step.</p>
    <?php require __DIR__ . '/../../templates/social_links.php'; ?>
  </div>
</footer>
</div>
<script src="<?php echo BASE_URL; ?>assets/js/neural-network.js" defer></script>
</body>
</html>
