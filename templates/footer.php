</div>

<footer class="mt-auto border-t border-white/[.07] bg-[#080d1b]/60 px-5 py-7">
    <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-5 sm:flex-row">
        <p class="text-sm text-slate-500">© <?php echo date("Y"); ?> Resume Analyzer</p>
        <div class="flex flex-col items-center gap-4 sm:flex-row">
            <a href="<?php echo BASE_URL; ?>developer" class="rounded-xl border border-teal-200/20 bg-teal-200/[.07] px-4 py-2 text-sm font-semibold text-teal-100 transition hover:bg-teal-200/[.12]">Meet the developer <span aria-hidden="true">→</span></a>
            <?php require __DIR__ . '/social_links.php'; ?>
        </div>
    </div>
</footer>

</body>
</html>
