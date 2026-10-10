<?php
// session_start();

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../templates/header.php";

// ADMIN CHECK
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL);
    exit;
}

// FETCH WITH POSITION + USER
$stmt = $pdo->query("
    SELECT 
        resumes.*, 
        users.name AS user_name,
        positions.name AS position_name
    FROM resumes 
    JOIN users ON resumes.user_id = users.id
    LEFT JOIN positions ON resumes.position = positions.id
    ORDER BY resumes.id DESC
");

$resumes = $stmt->fetchAll();
?>

<div class="max-w-7xl mx-auto px-4">

    <h2 class="text-3xl font-bold mb-6">All Resumes</h2>

    <div class="bg-white/10 backdrop-blur-lg rounded-xl overflow-hidden">

        <table class="w-full text-left">

            <thead class="bg-white/20">
                <tr>
                    <th class="p-3">#</th>
                    <th class="p-3">Resume Name</th>
                    <th class="p-3">Uploaded By</th>
                    <th class="p-3">Date</th>
                    <th class="p-3">Position</th>
                    <th class="p-3 text-center">Actions</th>
                </tr>
            </thead>

            <tbody>

                <?php if (count($resumes) > 0): ?>
                <?php foreach ($resumes as $index => $r): ?>

                <tr class="border-b border-white/10 hover:bg-white/10 transition">

                    <td class="p-3"><?php echo $index + 1; ?></td>

                    <td class="p-3">
                        <?php echo htmlspecialchars($r['file_name']); ?>
                    </td>

                    <td class="p-3 text-blue-400">
                        <?php echo htmlspecialchars($r['user_name']); ?>
                    </td>

                    <td class="p-3">
                        <?php echo date("d M Y", strtotime($r['created_at'] ?? 'now')); ?>
                    </td>

                    <!-- POSITION COLUMN -->
                    <td class="p-3 text-yellow-400">
                        <?php echo htmlspecialchars($r['position_name'] ?? 'N/A'); ?>
                    </td>

                    <td class="p-3 text-center space-x-2">

                        <!-- ANALYZE -->
                        <a href="<?php echo BASE_URL; ?>analyze_admin&resume_id=<?php echo $r['id']; ?>"
                            class="bg-green-500 px-3 py-1 rounded hover:bg-green-600">
                            Analyze
                        </a>

                        <!-- DELETE -->
                        <button onclick="openDeleteModal(<?php echo $r['id']; ?>)"
                            class="bg-red-500 px-3 py-1 rounded hover:bg-red-600">
                            Delete
                        </button>

                    </td>

                </tr>

                <?php endforeach; ?>
                <?php else: ?>

                <tr>
                    <td colspan="6" class="text-center p-6 text-gray-400">
                        No resumes found
                    </td>
                </tr>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>

<!-- DELETE MODAL -->
<div id="deleteModal" class="fixed inset-0 bg-black/60 hidden justify-center items-center z-50">

    <div class="bg-gray-900 p-6 rounded-xl w-80 text-center">

        <h3 class="text-xl font-bold mb-4">Delete Resume</h3>

        <p class="text-gray-300 mb-6">Are you sure you want to delete this resume?</p>

        <div class="flex justify-center gap-4">

            <button onclick="closeModal()" class="bg-gray-600 px-4 py-2 rounded hover:bg-gray-700">
                Cancel
            </button>

            <a id="confirmDelete" class="bg-red-500 px-4 py-2 rounded hover:bg-red-600">
                Delete
            </a>

        </div>

    </div>

</div>

<script>
function openDeleteModal(id) {
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteModal').classList.add('flex');

    document.getElementById('confirmDelete').href =
        "<?php echo BASE_URL; ?>modules/admin/delete_resume.php?id=" + id;
}

function closeModal() {
    document.getElementById('deleteModal').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . "/../../templates/footer.php"; ?>