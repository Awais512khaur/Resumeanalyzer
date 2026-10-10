<?php
require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../templates/header.php";

// ADMIN CHECK
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL);
    exit;
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
$stmt = $pdo->prepare("SELECT * FROM users ORDER BY id DESC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();

$users = $stmt->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4">

    <h2 class="text-3xl font-bold mb-6">Manage Users</h2>

    <div class="bg-white/10 backdrop-blur-lg rounded-xl overflow-hidden">

        <table class="w-full text-left">

            <thead class="bg-white/20">
                <tr>
                    <th class="p-3">#</th>
                    <th class="p-3">Name</th>
                    <th class="p-3">Email</th>
                    <th class="p-3">Role</th>
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
                                <a href="<?php echo BASE_URL; ?>index.php?page=admin_user&id=<?php echo $u['id']; ?>">
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
                                <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                    <button onclick="openDeleteModal(<?php echo $u['id']; ?>)"
                                            class="bg-red-500 px-3 py-1 rounded hover:bg-red-600">
                                        Delete
                                    </button>
                                <?php else: ?>
                                    <span class="text-gray-400 text-sm">You</span>
                                <?php endif; ?>
                            </td>

                        </tr>

                    <?php endforeach; ?>
                <?php else: ?>

                    <tr>
                        <td colspan="5" class="text-center p-6 text-gray-400">
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

<!-- DELETE MODAL -->
<div id="deleteModal" class="fixed inset-0 bg-black/60 hidden justify-center items-center z-50">

    <div class="bg-gray-900 p-6 rounded-xl w-80 text-center">

        <h3 class="text-xl font-bold mb-4">Delete User</h3>

        <p class="text-gray-300 mb-6">Are you sure?</p>

        <div class="flex justify-center gap-4">

            <button onclick="closeModal()" class="bg-gray-600 px-4 py-2 rounded">
                Cancel
            </button>

            <a id="confirmDelete" class="bg-red-500 px-4 py-2 rounded">
                Delete
            </a>

        </div>

    </div>

</div>

<script>
function openDeleteModal(userId) {
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteModal').classList.add('flex');

    document.getElementById('confirmDelete').href =
        "<?php echo BASE_URL; ?>modules/admin/delete_user.php?id=" + userId;
}

function closeModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . "/../../templates/footer.php"; ?>