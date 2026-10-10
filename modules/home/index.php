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
<style>
:root { color-scheme: dark; }
* { box-sizing: border-box; }
html { scroll-behavior: smooth; }
body { margin: 0; min-height: 100vh; overflow-x: hidden; color: #f5f7ff; background: #080d1b; font-family: Inter, ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
.scene { position: relative; isolation: isolate; background: radial-gradient(ellipse at 70% 36%, rgba(27, 72, 116, .22), transparent 44%), #080d1b; }
.scene::before, .scene::after { content: ""; position: absolute; z-index: -1; width: 24rem; height: 24rem; border-radius: 50%; filter: blur(100px); opacity: .22; pointer-events: none; }
.scene::before { top: 6rem; left: -13rem; background: #4868ff; }
.scene::after { top: 20rem; right: -14rem; background: #09d4b4; }
.grid-glow { position:absolute; inset:0; z-index:-1; opacity:.17; background-image:linear-gradient(rgba(142,166,208,.08) 1px,transparent 1px),linear-gradient(90deg,rgba(142,166,208,.08) 1px,transparent 1px); background-size:64px 64px; mask-image:linear-gradient(to bottom,black,transparent 82%); }
.topbar { border-bottom: 1px solid rgba(255,255,255,.08); background: rgba(8,13,27,.65); backdrop-filter: blur(18px); }
.brand-mark { display:grid; place-items:center; width:2rem; height:2rem; border-radius:.7rem; color:#07131e; background:linear-gradient(145deg,#83ffe4,#49c8fc); box-shadow:0 0 25px rgba(73,200,252,.3); }
.eyebrow { display:inline-flex; align-items:center; gap:.55rem; padding:.45rem .75rem; border:1px solid rgba(105,230,216,.2); border-radius:999px; color:#9cefe1; background:rgba(26,107,104,.12); font-size:.75rem; font-weight:650; letter-spacing:.08em; text-transform:uppercase; }
.eyebrow-dot { width:.42rem; height:.42rem; border-radius:50%; background:#56e6c4; box-shadow:0 0 12px #56e6c4; }
.headline { letter-spacing:-.065em; line-height:1.03; }
.headline-accent { color:transparent; background:linear-gradient(95deg,#8df5dc 4%,#70d4ff 54%,#9aa5ff); -webkit-background-clip:text; background-clip:text; }
.primary-cta { color:#06131a; background:linear-gradient(115deg,#9af2d5,#67d9f3); box-shadow:0 10px 35px rgba(67,207,206,.2),inset 0 1px rgba(255,255,255,.55); }
.primary-cta:hover { transform:translateY(-2px); box-shadow:0 15px 42px rgba(67,207,206,.32),inset 0 1px rgba(255,255,255,.55); }
.secondary-cta { border:1px solid rgba(255,255,255,.14); background:rgba(255,255,255,.035); }
.secondary-cta:hover { border-color:rgba(143,231,224,.5); background:rgba(255,255,255,.08); }
.visual-stage { position:relative; min-height:31rem; perspective:1200px; }
.orb { position:absolute; width:19rem; height:19rem; top:5.4rem; left:50%; transform:translateX(-50%); border-radius:50%; background:radial-gradient(circle at 35% 28%,rgba(110,232,232,.4),rgba(66,107,220,.17) 34%,rgba(26,41,89,.07) 63%,transparent 70%); filter:blur(2px); animation:orb-pulse 7s ease-in-out infinite; }
.orbit { position:absolute; top:7.2rem; left:50%; width:26rem; height:14rem; border:1px solid rgba(127,225,229,.17); border-radius:50%; transform:translateX(-50%) rotate(-22deg); box-shadow:0 0 35px rgba(63,190,226,.06),inset 0 0 35px rgba(63,190,226,.04); }
.orbit::after { content:""; position:absolute; width:.55rem; height:.55rem; top:1.2rem; right:3.7rem; border-radius:50%; background:#8df5dc; box-shadow:0 0 18px 5px rgba(91,235,216,.6); }
.resume-card { position:absolute; top:3.1rem; left:50%; width:min(19rem,80vw); min-height:24rem; padding:1.35rem; border:1px solid rgba(255,255,255,.17); border-radius:1.2rem; background:linear-gradient(145deg,rgba(31,45,72,.92),rgba(14,22,39,.9)); box-shadow:0 36px 90px rgba(0,0,0,.45),inset 0 1px rgba(255,255,255,.12),0 0 55px rgba(65,150,204,.12); transform:translateX(-50%) rotateY(-12deg) rotateX(4deg); transform-style:preserve-3d; animation:card-float 6s ease-in-out infinite; }
.resume-top { display:flex; align-items:center; gap:.7rem; padding-bottom:1rem; border-bottom:1px solid rgba(255,255,255,.09); }
.avatar { display:grid; place-items:center; width:2.6rem; height:2.6rem; border-radius:.85rem; color:#07131e; font-weight:800; background:linear-gradient(145deg,#9af2d5,#67d9f3); }
.line { height:.4rem; border-radius:10px; background:rgba(183,202,232,.15); }
.line.bright { background:linear-gradient(90deg,rgba(130,226,223,.56),rgba(130,226,223,.12)); }
.score-card { position:absolute; top:17.5rem; left:calc(50% + 6rem); min-width:10.3rem; padding:.85rem 1rem; border:1px solid rgba(115,235,203,.24); border-radius:1rem; background:rgba(14,29,38,.9); box-shadow:0 18px 45px rgba(0,0,0,.35),0 0 30px rgba(78,223,183,.1); backdrop-filter:blur(14px); animation:badge-float 5s ease-in-out infinite; }
.score-ring { display:grid; place-items:center; width:3.2rem; height:3.2rem; border-radius:50%; background:radial-gradient(closest-side,#10221f 79%,transparent 81%),conic-gradient(#6ce7bf 0 85%,rgba(108,231,191,.13) 85%); color:#a1f5d8; font-size:.8rem; font-weight:800; }
.match-card { position:absolute; top:8.5rem; left:calc(50% - 13rem); padding:.75rem .9rem; border:1px solid rgba(255,255,255,.12); border-radius:.9rem; background:rgba(24,34,58,.88); box-shadow:0 18px 40px rgba(0,0,0,.3); backdrop-filter:blur(12px); animation:badge-float 6s ease-in-out infinite reverse; }
.feature-card { border:1px solid rgba(255,255,255,.08); background:linear-gradient(145deg,rgba(25,36,59,.72),rgba(13,20,36,.72)); transition:transform .28s ease,border-color .28s ease,background .28s ease; }
.feature-card:hover { transform:translateY(-6px); border-color:rgba(119,222,222,.27); background:linear-gradient(145deg,rgba(33,52,76,.8),rgba(15,25,42,.8)); }
.feature-icon { display:grid; place-items:center; width:2.75rem; height:2.75rem; border:1px solid rgba(125,230,220,.17); border-radius:.9rem; color:#9df1dd; background:rgba(85,212,194,.09); }
.rise-in { opacity:0; transform:translateY(18px); animation:rise-in .8s cubic-bezier(.2,.75,.3,1) forwards; }
.delay-1 { animation-delay:.12s; } .delay-2 { animation-delay:.24s; } .delay-3 { animation-delay:.4s; }
@keyframes rise-in { to { opacity:1; transform:translateY(0); } }
@keyframes card-float { 0%,100% { transform:translateX(-50%) rotateY(-12deg) rotateX(4deg) translateY(0); } 50% { transform:translateX(-50%) rotateY(-8deg) rotateX(2deg) translateY(-10px); } }
@keyframes badge-float { 0%,100% { translate:0 0; } 50% { translate:0 -8px; } }
@keyframes orb-pulse { 0%,100% { opacity:.72; scale:1; } 50% { opacity:1; scale:1.08; } }
@media (max-width:900px) { .visual-stage { min-height:29rem; } .resume-card { top:2.6rem; } .score-card { left:calc(50% + 4.6rem); } .match-card { left:calc(50% - 11.5rem); } }
@media (max-width:640px) { .visual-stage { min-height:26rem; margin-top:1.5rem; } .resume-card { top:2rem; width:17rem; min-height:22rem; } .orb { top:4rem; width:16rem; height:16rem; } .orbit { top:6rem; width:21rem; } .score-card { top:16.3rem; left:calc(50% + 2.8rem); transform:scale(.88); transform-origin:left top; } .match-card { top:7.2rem; left:calc(50% - 10rem); transform:scale(.88); transform-origin:right top; } }
@media (prefers-reduced-motion: reduce) { *,*::before,*::after { scroll-behavior:auto !important; animation-duration:.01ms !important; animation-iteration-count:1 !important; transition-duration:.01ms !important; } .rise-in { opacity:1; transform:none; } }
</style>
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
  <section class="mx-auto grid max-w-7xl items-center gap-4 px-5 pb-12 pt-12 sm:px-8 md:min-h-[650px] md:grid-cols-[1.02fr_.98fr] md:gap-0 md:pt-8">
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
<footer class="border-t border-white/[.07] px-5 py-6 text-center text-sm text-slate-500"><p>© <?php echo date("Y"); ?> Resume Analyzer. Built for your next step.</p></footer>
</div>
</body>
</html>
