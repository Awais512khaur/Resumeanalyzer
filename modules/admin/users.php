<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";

// ADMIN CHECK
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL . "login");
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/* =========================
   PAGINATION SETTINGS
=========================*/
$limit = 10; // users per page
$page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
$page = max($page, 1);

$offset = ($page - 1) * $limit;

/* =========================
   TOTAL USERS
=========================*/
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalPages = ceil($totalUsers / $limit);

/* =========================
   FETCH USERS
=========================*/
$stmt = $pdo->prepare("SELECT id, name, email, role, status, lock_reason, locked_by_name, created_at FROM users ORDER BY CASE WHEN role = 'admin' THEN 0 ELSE 1 END, id DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();

$users = $stmt->fetchAll();
$userAdminNotice = $_SESSION['user_admin_notice'] ?? '';
$userAdminError = $_SESSION['user_admin_error'] ?? '';
unset($_SESSION['user_admin_notice'], $_SESSION['user_admin_error']);
require_once __DIR__ . "/../../templates/header.php";
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/admin-users.css?v=1">

<div class="max-w-7xl mx-auto px-4">

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div><p class="mb-1 text-xs font-semibold uppercase tracking-[.18em] text-teal-200">Accounts &amp; access</p><h2 class="text-3xl font-bold">Manage Users</h2></div>
        <button type="button" onclick="openCreateUserModal()" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-[#9af2d5] to-[#67d9f3] px-4 py-2.5 font-bold text-[#06131a] shadow-lg shadow-teal-900/20">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M12 5v14M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
            Add user
        </button>
    </div>

    <?php if ($userAdminNotice !== '' || $userAdminError !== ''): ?>
        <?php $toastIsError = $userAdminError !== ''; ?>
        <div id="userActionToast" role="<?php echo $toastIsError ? 'alert' : 'status'; ?>" aria-live="<?php echo $toastIsError ? 'assertive' : 'polite'; ?>" class="user-action-toast fixed right-4 top-24 z-[100] w-[calc(100%-2rem)] max-w-sm rounded-2xl border px-4 py-3.5 text-sm shadow-2xl backdrop-blur-xl sm:right-6 <?php echo $toastIsError ? 'border-rose-200/20 bg-slate-900/95 text-rose-100' : 'border-teal-200/20 bg-slate-900/95 text-teal-100'; ?>">
            <div class="flex items-start gap-3"><span aria-hidden="true" class="mt-0.5"><?php echo $toastIsError ? '!' : '✓'; ?></span><span><?php echo htmlspecialchars($toastIsError ? $userAdminError : $userAdminNotice, ENT_QUOTES, 'UTF-8'); ?></span></div>
        </div>
        <script>setTimeout(() => document.getElementById('userActionToast')?.classList.add('is-dismissed'), 4200); setTimeout(() => document.getElementById('userActionToast')?.remove(), 4600);</script>
    <?php endif; ?>

    <div class="app-table-wrap bg-white/10 backdrop-blur-lg rounded-xl overflow-x-auto">

        <table class="w-full text-left">

            <thead class="bg-white/20">
                <tr>
                    <th class="p-3">#</th>
                    <th class="p-3">Name</th>
                    <th class="p-3">Email</th>
                    <th class="p-3">Role</th>
                    <th class="p-3 text-center">Account</th>
                    <th class="p-3 text-center">Action</th>
                </tr>
            </thead>

            <tbody>

                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $index => $u): ?>

                        <tr class="border-b border-white/10 hover:bg-white/10 transition">

                            <!-- SERIAL NUMBER FIX -->
                            <td class="p-3">
                                <?php echo ($offset + $index + 1); ?>
                            </td>

                            <td class="p-3 text-blue-400">
                                <a href="<?php echo BASE_URL; ?>index.php?page=admin_user&id=<?php echo (int)$u['id']; ?>">
                                    <?php echo htmlspecialchars($u['name']); ?>
                                </a>
                            </td>

                            <td class="p-3"><?php echo htmlspecialchars($u['email']); ?></td>

                            <td class="p-3">
                                <span class="<?php echo $u['role'] === 'admin' ? 'text-yellow-400' : 'text-blue-400'; ?>">
                                    <?php echo ucfirst($u['role']); ?>
                                </span>
                            </td>

                            <td class="p-3 text-center">
                                <?php if ($u['role'] === 'user'): ?>
                                    <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold <?php echo (int)$u['status'] === 1 ? 'border-teal-200/15 bg-teal-200/[.06] text-teal-100' : 'border-rose-200/15 bg-rose-200/[.06] text-rose-200'; ?>">
                                        <?php echo (int)$u['status'] === 1 ? 'Active' : 'Locked'; ?>
                                    </span>
                                    <?php if ((int)$u['status'] !== 1): ?>
                                        <span class="mt-1 block max-w-56 text-xs leading-5 text-slate-400">
                                            <?php echo $u['lock_reason'] === 'failed_attempts'
                                                ? 'Locked after 10 failed login attempts'
                                                : 'Locked by admin' . ($u['locked_by_name'] ? ': ' . htmlspecialchars($u['locked_by_name'], ENT_QUOTES, 'UTF-8') : ''); ?>
                                        </span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="text-xs text-slate-500">Admin account</span>
                                <?php endif; ?>
                            </td>

                            <td class="p-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                <button type="button" title="Edit user" aria-label="Edit <?php echo htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8'); ?>" onclick="openEditUserModal(this)"
                                        data-user-id="<?php echo (int)$u['id']; ?>" data-name="<?php echo htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8'); ?>" data-email="<?php echo htmlspecialchars($u['email'], ENT_QUOTES, 'UTF-8'); ?>" data-role="<?php echo htmlspecialchars($u['role'], ENT_QUOTES, 'UTF-8'); ?>" data-status="<?php echo (int)$u['status']; ?>" data-is-self="<?php echo (int)$u['id'] === (int)$_SESSION['user_id'] ? '1' : '0'; ?>"
                                        class="grid h-9 w-9 place-items-center rounded-lg border border-sky-200/15 bg-sky-200/[.06] text-sky-100 hover:bg-sky-200/[.12] focus-visible:outline focus-visible:outline-2 focus-visible:outline-teal-200">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m14 6 4 4M4 20l4.2-.8L19 8.4a2.1 2.1 0 0 0-3-3L5.2 16.2 4 20Z" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                </button>
                                <?php if ($u['role'] === 'user'): ?>
                                    <form action="<?php echo BASE_URL; ?>modules/admin/toggle_user_status.php" method="POST" class="inline-flex">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <input type="hidden" name="user_id" value="<?php echo (int)$u['id']; ?>">
                                        <input type="hidden" name="p" value="<?php echo (int)$page; ?>">
                                        <button type="submit" title="<?php echo (int)$u['status'] === 1 ? 'Lock account' : 'Unlock account'; ?>" aria-label="<?php echo (int)$u['status'] === 1 ? 'Lock account' : 'Unlock account'; ?> for <?php echo htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8'); ?>" class="grid h-9 w-9 place-items-center rounded-lg border <?php echo (int)$u['status'] === 1 ? 'border-amber-200/15 bg-amber-200/[.06] text-amber-100 hover:bg-amber-200/[.12]' : 'border-teal-200/15 bg-teal-200/[.06] text-teal-100 hover:bg-teal-200/[.12]'; ?>">
                                            <?php if ((int)$u['status'] === 1): ?>
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M8 10V7a4 4 0 1 1 8 0v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                            <?php else: ?>
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><rect x="5" y="10" width="14" height="11" rx="2" stroke="currentColor" stroke-width="1.8"/><path d="M16 10V7a4 4 0 0 0-7.5-2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                                            <?php endif; ?>
                                        </button>
                                    </form>
                                <?php endif; ?>
                                <?php if ($u['role'] !== 'admin'): ?>
                                    <button type="button" title="Delete user" aria-label="Delete <?php echo htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8'); ?>" onclick="openDeleteModal(this)" data-user-id="<?php echo (int)$u['id']; ?>" data-user-name="<?php echo htmlspecialchars($u['name'], ENT_QUOTES, 'UTF-8'); ?>" class="grid h-9 w-9 place-items-center rounded-lg border border-rose-200/15 bg-rose-200/[.06] text-rose-200 hover:bg-rose-200/[.12]">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="M4 7h16M10 11v6m4-6v6M6 7l1 13h10l1-13M9 7V4h6v3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                <?php endif; ?>
                                </div>
                            </td>

                        </tr>

                    <?php endforeach; ?>
                <?php else: ?>

                    <tr>
                        <td colspan="6" class="text-center p-6 text-gray-400">
                            No users found
                        </td>
                    </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

    <!-- PAGINATION UI -->
    <div class="flex justify-center mt-6 space-x-2">

        <!-- PREVIOUS -->
        <?php if ($page > 1): ?>
            <a href="<?php echo BASE_URL; ?>index.php?page=admin_users&p=<?php echo $page - 1; ?>"
               class="px-3 py-1 bg-white/20 rounded hover:bg-white/30">
                Prev
            </a>
        <?php endif; ?>

        <!-- PAGE NUMBERS -->
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="<?php echo BASE_URL; ?>index.php?page=admin_users&p=<?php echo $i; ?>"
               class="px-3 py-1 rounded <?php echo ($i == $page) ? 'bg-blue-600' : 'bg-white/20'; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <!-- NEXT -->
        <?php if ($page < $totalPages): ?>
            <a href="<?php echo BASE_URL; ?>index.php?page=admin_users&p=<?php echo $page + 1; ?>"
               class="px-3 py-1 bg-white/20 rounded hover:bg-white/30">
                Next
            </a>
        <?php endif; ?>

    </div>

</div>

<!-- Add / edit user modal -->
<div id="userModal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-950/75 p-4 backdrop-blur-sm" aria-hidden="true">
    <section class="surface-card w-full max-w-lg rounded-2xl p-6 sm:p-7" role="dialog" aria-modal="true" aria-labelledby="userModalTitle">
        <div class="mb-6 flex items-start justify-between gap-4">
            <div><p class="text-xs font-semibold uppercase tracking-[.16em] text-teal-200">User management</p><h3 id="userModalTitle" class="mt-1 text-2xl font-bold text-white">Add user</h3><p id="userModalHint" class="mt-1 text-sm text-slate-400">Create an account for your team.</p></div>
            <button type="button" onclick="closeUserModal()" aria-label="Close dialog" class="grid h-9 w-9 place-items-center rounded-lg border border-white/10 bg-white/[.04] text-slate-300 hover:bg-white/[.08]">×</button>
        </div>
        <form id="userForm" action="<?php echo BASE_URL; ?>modules/admin/save_user.php" method="POST" class="space-y-4">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="user_id" id="userId" value="">
            <input type="hidden" name="p" value="<?php echo (int)$page; ?>">
            <div><label for="userName" class="mb-1.5 block text-sm font-medium text-slate-300">Full name</label><input id="userName" name="name" required maxlength="100" class="w-full rounded-xl border border-white/10 bg-slate-950/40 px-4 py-3 text-white placeholder:text-slate-500" placeholder="Enter full name"></div>
            <div><label for="userEmail" class="mb-1.5 block text-sm font-medium text-slate-300">Email address</label><input id="userEmail" name="email" type="email" required maxlength="150" class="w-full rounded-xl border border-white/10 bg-slate-950/40 px-4 py-3 text-white placeholder:text-slate-500" placeholder="name@example.com"></div>
            <div id="userPasswordWrap"><label for="userPassword" class="mb-1.5 block text-sm font-medium text-slate-300">Password</label><div class="relative"><input id="userPassword" name="password" type="password" minlength="12" autocomplete="new-password" class="w-full rounded-xl border border-white/10 bg-slate-950/40 px-4 py-3 pr-16 text-white placeholder:text-slate-500" placeholder="At least 12 characters"><button type="button" data-password-toggle aria-controls="userPassword" aria-label="Show password" class="absolute inset-y-0 right-3 my-auto h-9 rounded-lg px-2 text-sm font-semibold text-teal-200 transition hover:bg-white/[.06] hover:text-white">Show</button></div><p id="passwordHint" class="mt-1 text-xs text-slate-500">Required for a new account.</p></div>
            <div id="userConfirmPasswordWrap"><label for="userConfirmPassword" class="mb-1.5 block text-sm font-medium text-slate-300">Confirm password</label><div class="relative"><input id="userConfirmPassword" name="password_confirmation" type="password" minlength="12" autocomplete="new-password" required class="w-full rounded-xl border border-white/10 bg-slate-950/40 px-4 py-3 pr-16 text-white placeholder:text-slate-500" placeholder="Re-enter password"><button type="button" data-password-toggle aria-controls="userConfirmPassword" aria-label="Show password" class="absolute inset-y-0 right-3 my-auto h-9 rounded-lg px-2 text-sm font-semibold text-teal-200 transition hover:bg-white/[.06] hover:text-white">Show</button></div><p id="confirmPasswordHint" class="mt-1 text-xs text-slate-500">Required for a new account.</p></div>
            <div id="userRoleWrap"><label for="userRole" class="mb-1.5 block text-sm font-medium text-slate-300">Role</label><select id="userRole" name="role" style="color-scheme:dark" class="w-full rounded-xl border border-white/10 bg-slate-950/80 px-4 py-3 text-white"><option value="user">User</option><option value="admin">Administrator</option></select></div>
            <div class="flex justify-end gap-3 pt-2"><button type="button" onclick="closeUserModal()" class="rounded-xl border border-white/10 bg-white/[.04] px-4 py-2.5 text-sm font-semibold text-slate-300">Cancel</button><button id="userSubmit" type="submit" class="rounded-xl bg-gradient-to-r from-[#9af2d5] to-[#67d9f3] px-5 py-2.5 text-sm font-bold text-[#06131a]">Create user</button></div>
        </form>
    </section>
</div>


<!-- Delete confirmation -->
<div id="deleteModal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-slate-950/75 p-4 backdrop-blur-sm" aria-hidden="true">
    <section class="surface-card w-full max-w-sm rounded-2xl p-6 text-center" role="dialog" aria-modal="true" aria-labelledby="deleteTitle">
        <span class="mx-auto mb-4 grid h-12 w-12 place-items-center rounded-xl border border-rose-200/15 bg-rose-200/[.07] text-rose-200" aria-hidden="true">!</span>
        <h3 id="deleteTitle" class="text-xl font-bold text-white">Delete user?</h3>
        <p class="mt-2 text-sm leading-6 text-slate-400">This removes <strong id="deleteUserName" class="text-slate-200"></strong> and their account data. This action cannot be undone.</p>
        <form action="<?php echo BASE_URL; ?>modules/admin/delete_user.php" method="POST" class="mt-6 flex justify-center gap-3">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="user_id" id="deleteUserId" value="">
            <input type="hidden" name="p" value="<?php echo (int)$page; ?>">
            <button type="button" onclick="closeDeleteModal()" class="rounded-xl border border-white/10 bg-white/[.04] px-4 py-2.5 text-sm font-semibold text-slate-300">Cancel</button>
            <button type="submit" class="rounded-xl border border-rose-200/15 bg-rose-200/[.08] px-4 py-2.5 text-sm font-bold text-rose-100">Delete user</button>
        </form>
    </section>
</div>

<script>
const userModal = document.getElementById('userModal');
const userForm = document.getElementById('userForm');
function showModal(modal) { modal.classList.remove('hidden'); modal.classList.add('flex'); modal.setAttribute('aria-hidden', 'false'); }
function hideModal(modal) { modal.classList.add('hidden'); modal.classList.remove('flex'); modal.setAttribute('aria-hidden', 'true'); }
function openCreateUserModal() {
    userForm.reset();
    resetPasswordVisibility(document.getElementById('userPassword'));
    resetPasswordVisibility(document.getElementById('userConfirmPassword'));
    document.getElementById('userId').value = '';
    document.getElementById('userModalTitle').textContent = 'Add user';
    document.getElementById('userModalHint').textContent = 'Create an account for your team.';
    document.getElementById('userSubmit').textContent = 'Create user';
    document.getElementById('userPassword').required = true;
    document.getElementById('userConfirmPassword').required = true;
    document.getElementById('confirmPasswordHint').textContent = 'Required for a new account.';
    document.getElementById('passwordHint').textContent = 'Required for a new account.';
    document.getElementById('userRoleWrap').classList.remove('hidden');
    document.getElementById('userRole').disabled = false;
    showModal(userModal);
    document.getElementById('userName').focus();
}
function openEditUserModal(button) {
    userForm.reset();
    resetPasswordVisibility(document.getElementById('userPassword'));
    resetPasswordVisibility(document.getElementById('userConfirmPassword'));
    const isSelf = button.dataset.isSelf === '1';
    document.getElementById('userId').value = button.dataset.userId;
    document.getElementById('userName').value = button.dataset.name;
    document.getElementById('userEmail').value = button.dataset.email;
    document.getElementById('userRole').value = button.dataset.role;
    document.getElementById('userModalTitle').textContent = 'Edit user';
    document.getElementById('userModalHint').textContent = isSelf ? 'Update your name and email address.' : 'Update account details or set a new password.';
    document.getElementById('userSubmit').textContent = 'Save changes';
    document.getElementById('userPassword').required = false;
    document.getElementById('userPassword').value = '';
    document.getElementById('userConfirmPassword').required = false;
    document.getElementById('userConfirmPassword').value = '';
    document.getElementById('confirmPasswordHint').textContent = 'Required when setting a new password.';
    document.getElementById('passwordHint').textContent = 'Leave blank to keep the current password.';
    document.getElementById('userRoleWrap').classList.toggle('hidden', isSelf);
    document.getElementById('userRole').disabled = isSelf;
    showModal(userModal);
    document.getElementById('userName').focus();
}
document.getElementById('userPassword').addEventListener('input', event => {
    const isCreate = document.getElementById('userId').value === '';
    document.getElementById('userConfirmPassword').required = isCreate || event.target.value !== '';
});
function closeUserModal() { hideModal(userModal); }
function openDeleteModal(button) {
    document.getElementById('deleteUserId').value = button.dataset.userId;
    document.getElementById('deleteUserName').textContent = button.dataset.userName;
    showModal(document.getElementById('deleteModal'));
}
function closeDeleteModal() { hideModal(document.getElementById('deleteModal')); }
document.addEventListener('keydown', event => { if (event.key === 'Escape') { closeUserModal(); closeDeleteModal(); } });
</script>
<script src="<?php echo BASE_URL; ?>assets/js/password-toggle.js" defer></script>

<?php require_once __DIR__ . "/../../templates/footer.php"; ?>
