<?php
require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: " . BASE_URL . "login");
    exit;
}

$totalRows = (int)$pdo->query("SELECT COUNT(*) FROM audit_logs")->fetchColumn();
$allowedSizes = ['25' => 25, '50' => 50, '80' => 80, 'all' => $totalRows];
$pageSizeKey = isset($_GET['limit']) && isset($allowedSizes[(string)$_GET['limit']]) ? (string)$_GET['limit'] : '25';
$pageSize = $allowedSizes[$pageSizeKey];
$page = max(1, (int)($_GET['p'] ?? 1));
$totalPages = $pageSizeKey === 'all' ? 1 : max(1, (int)ceil($totalRows / $pageSize));
$page = min($page, $totalPages);
$offset = $pageSizeKey === 'all' ? 0 : ($page - 1) * $pageSize;
$limitSql = $pageSizeKey === 'all' ? '' : ' LIMIT ' . (int)$pageSize . ' OFFSET ' . (int)$offset;
$logs = $pdo->query(
    "SELECT audit_logs.*, COALESCE(audit_logs.actor_name, users.name, 'System / unauthenticated') AS display_actor
     FROM audit_logs
     LEFT JOIN users ON users.id = audit_logs.actor_id
     ORDER BY audit_logs.created_at DESC, audit_logs.id DESC" . $limitSql
)->fetchAll();

$rangeStart = $totalRows === 0 ? 0 : $offset + 1;
$rangeEnd = $pageSizeKey === 'all' ? $totalRows : min($offset + $pageSize, $totalRows);
$pageUrl = static function ($targetPage) use ($pageSizeKey) {
    return BASE_URL . 'index.php?page=admin_audit_logs&limit=' . rawurlencode($pageSizeKey) . '&p=' . (int)$targetPage;
};

require_once __DIR__ . "/../../templates/header.php";
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin-audit-logs.css?v=1">
<main class="mx-auto w-full max-w-7xl px-4 pb-14 pt-5 sm:px-6">
    <section class="mb-7 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="mb-2 text-xs font-semibold uppercase tracking-[.2em] text-teal-200">Security &amp; activity</p>
            <h1 class="text-3xl font-bold tracking-tight text-white sm:text-4xl">Audit log</h1>
            <p class="mt-2 text-sm leading-6 text-slate-400">Review sign-ins, resume activity, analysis runs, and administrator changes.</p>
        </div>
        <div class="rounded-2xl border border-white/[.08] bg-white/[.035] px-5 py-3">
            <p class="text-xs uppercase tracking-[.12em] text-slate-500">Recorded events</p>
            <p class="mt-1 text-2xl font-bold text-white"><?php echo number_format($totalRows); ?></p>
        </div>
    </section>

    <section class="surface-card overflow-hidden rounded-2xl">
        <div class="flex flex-col gap-4 border-b border-white/[.07] px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <div>
                <h2 class="font-semibold text-white">System events</h2>
                <p class="mt-1 text-xs text-slate-500">Showing <?php echo number_format($rangeStart); ?>–<?php echo number_format($rangeEnd); ?> of <?php echo number_format($totalRows); ?></p>
            </div>
            <form method="get" action="<?php echo htmlspecialchars(BASE_URL . 'index.php', ENT_QUOTES, 'UTF-8'); ?>" class="flex items-center gap-3">
                <input type="hidden" name="page" value="admin_audit_logs">
                <label for="auditLimit" class="text-sm text-slate-400">Rows per page</label>
                <select id="auditLimit" name="limit" onchange="this.form.submit()" style="color-scheme:dark" class="rounded-xl border border-white/10 bg-slate-900/80 px-3 py-2 text-sm text-slate-100">
                    <option value="25" <?php echo $pageSizeKey === '25' ? 'selected' : ''; ?>>25 rows</option>
                    <option value="50" <?php echo $pageSizeKey === '50' ? 'selected' : ''; ?>>50 rows</option>
                    <option value="80" <?php echo $pageSizeKey === '80' ? 'selected' : ''; ?>>80 rows</option>
                    <option value="all" <?php echo $pageSizeKey === 'all' ? 'selected' : ''; ?>>All rows</option>
                </select>
            </form>
        </div>

        <div class="app-table-wrap rounded-none border-0 bg-transparent shadow-none">
            <table class="w-full text-left">
                <thead>
                    <tr>
                        <th class="px-5">Event</th>
                        <th class="px-5">Actor</th>
                        <th class="px-5">Target</th>
                        <th class="px-5">Details</th>
                        <th class="px-5">IP address</th>
                        <th class="px-5">Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($logs): ?>
                        <?php foreach ($logs as $log): ?>
                            <?php
                                $eventLabel = ucwords(str_replace(['.', '_', '-'], ' ', (string)$log['action']));
                                $eventTone = strpos((string)$log['action'], 'deleted') !== false ? 'rose' : ((strpos((string)$log['action'], 'failed') !== false || strpos((string)$log['action'], 'locked') !== false) ? 'amber' : 'teal');
                                $targetText = $log['entity_type'] ? ucfirst(str_replace('_', ' ', $log['entity_type'])) . ($log['entity_id'] ? ' #' . $log['entity_id'] : '') : '—';
                            ?>
                            <tr class="border-b border-white/[.05] align-top">
                                <td class="px-5 py-4"><span class="inline-flex whitespace-nowrap rounded-full border border-<?php echo $eventTone; ?>-200/15 bg-<?php echo $eventTone; ?>-200/[.06] px-2.5 py-1 text-xs font-semibold text-<?php echo $eventTone; ?>-100"><?php echo htmlspecialchars($eventLabel); ?></span></td>
                                <td class="px-5 py-4"><p class="font-medium text-slate-100"><?php echo htmlspecialchars($log['display_actor']); ?></p><?php if ($log['actor_id'] !== null): ?><p class="mt-1 text-xs text-slate-500">User #<?php echo (int)$log['actor_id']; ?></p><?php endif; ?></td>
                                <td class="whitespace-nowrap px-5 py-4 text-sm text-slate-300"><?php echo htmlspecialchars($targetText); ?></td>
                                <td class="max-w-sm px-5 py-4 text-sm leading-5 text-slate-400" title="<?php echo htmlspecialchars((string)$log['details'], ENT_QUOTES, 'UTF-8'); ?>"><span class="line-clamp-2"><?php echo htmlspecialchars((string)($log['details'] ?: '—')); ?></span><?php if (!empty($log['user_agent'])): ?><span class="mt-1 block truncate text-[.68rem] text-slate-600" title="<?php echo htmlspecialchars($log['user_agent'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($log['user_agent']); ?></span><?php endif; ?></td>
                                <td class="whitespace-nowrap px-5 py-4 font-mono text-xs text-slate-400"><?php echo htmlspecialchars((string)($log['ip_address'] ?: '—')); ?></td>
                                <td class="whitespace-nowrap px-5 py-4"><time class="text-sm text-slate-200" datetime="<?php echo htmlspecialchars(date(DATE_ATOM, strtotime($log['created_at']))); ?>"><?php echo htmlspecialchars(date('d M Y', strtotime($log['created_at']))); ?></time><span class="mt-1 block text-xs text-slate-500"><?php echo htmlspecialchars(date('H:i:s', strtotime($log['created_at']))); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="px-6 py-16 text-center"><span class="mx-auto mb-3 grid h-12 w-12 place-items-center rounded-2xl border border-teal-200/10 bg-teal-200/[.05] text-xl text-teal-200" aria-hidden="true">◷</span><p class="font-medium text-slate-200">No activity recorded yet</p><p class="mt-1 text-sm text-slate-500">New system actions will appear here.</p></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pageSizeKey !== 'all' && $totalPages > 1): ?>
            <nav class="flex flex-col gap-4 border-t border-white/[.07] px-4 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6" aria-label="Audit log pagination">
                <p class="text-xs text-slate-500">Page <?php echo number_format($page); ?> of <?php echo number_format($totalPages); ?></p>
                <div class="flex flex-wrap gap-2">
                    <?php if ($page > 1): ?><a href="<?php echo htmlspecialchars($pageUrl($page - 1), ENT_QUOTES, 'UTF-8'); ?>" class="rounded-lg border border-white/10 bg-white/[.04] px-3 py-2 text-sm text-slate-300 hover:border-teal-200/30 hover:text-teal-100">← Previous</a><?php endif; ?>
                    <?php
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        for ($pageNumber = $startPage; $pageNumber <= $endPage; $pageNumber++):
                    ?>
                        <a href="<?php echo htmlspecialchars($pageUrl($pageNumber), ENT_QUOTES, 'UTF-8'); ?>" aria-current="<?php echo $pageNumber === $page ? 'page' : 'false'; ?>" class="rounded-lg border px-3 py-2 text-sm <?php echo $pageNumber === $page ? 'border-teal-200/30 bg-teal-200/[.1] font-semibold text-teal-100' : 'border-white/10 bg-white/[.04] text-slate-300 hover:border-teal-200/30 hover:text-teal-100'; ?>"><?php echo number_format($pageNumber); ?></a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?><a href="<?php echo htmlspecialchars($pageUrl($page + 1), ENT_QUOTES, 'UTF-8'); ?>" class="rounded-lg border border-white/10 bg-white/[.04] px-3 py-2 text-sm text-slate-300 hover:border-teal-200/30 hover:text-teal-100">Next →</a><?php endif; ?>
                </div>
            </nav>
        <?php endif; ?>
    </section>
</main>
<?php require_once __DIR__ . "/../../templates/footer.php"; ?>
