<?php
// session_start();
// 🔒 PREVENT BACK CACHE
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../templates/header.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login");
    exit;
}

// FETCH USER RESUMES
$stmt = $pdo->prepare("SELECT * FROM resumes WHERE user_id = ? ORDER BY id DESC");
$stmt->execute([$_SESSION['user_id']]);
$resumes = $stmt->fetchAll();
?>

<div class="max-w-6xl mx-auto px-4">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold">My Resumes</h2>

        <a href="<?php echo BASE_URL; ?>upload"
           class="bg-blue-600 px-4 py-2 rounded-lg hover:bg-blue-700">
            Upload New
        </a>
    </div>

    <div class="app-table-wrap bg-white/10 backdrop-blur-lg rounded-xl overflow-x-auto">

        <table class="w-full text-left">
            <thead class="bg-white/20">
                <tr>
                    <th class="p-3">#</th>
                    <th class="p-3">Resume Name</th>
                    <th class="p-3">Date</th>
                    <th class="p-3 text-center">Actions</th>
                </tr>
            </thead>

            <tbody>

                <?php if (count($resumes) > 0): ?>
                    <?php foreach ($resumes as $index => $resume): ?>

                        <tr class="border-b border-white/10 hover:bg-white/10 transition">

                            <td class="p-3"><?php echo $index + 1; ?></td>

                            <td class="p-3">
                                <?php echo htmlspecialchars($resume['file_name']); ?>
                            </td>

                            <td class="p-3">
                                <?php echo date("d M Y", strtotime($resume['created_at'] ?? 'now')); ?>
                            </td>

                            <td class="p-3 text-center space-x-2">

                                <!-- VIEW PDF -->
                                <a href="<?php echo BASE_URL; ?>modules/resume/view.php?id=<?php echo (int)$resume['id']; ?>"
                                   target="_blank" rel="noopener noreferrer"
                                   class="inline-block rounded-lg border border-teal-200/20 bg-teal-200/[.08] px-3 py-1 text-teal-100 hover:bg-teal-200/[.15]">
                                    View
                                </a>

                                <!-- ANALYZE -->
                                <a href="<?php echo BASE_URL; ?>analyze&resume_id=<?php echo $resume['id']; ?>"
                                   class="bg-green-500 px-3 py-1 rounded hover:bg-green-600">
                                    Analyze
                                </a>

                                <!-- DELETE BUTTON -->
                                <button 
                                    onclick="openModal(<?php echo $resume['id']; ?>)"
                                    class="bg-red-500 px-3 py-1 rounded hover:bg-red-600">
                                    Delete
                                </button>

                            </td>

                        </tr>

                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center p-6 text-gray-400">
                            No resumes uploaded yet
                        </td>
                    </tr>
                <?php endif; ?>

            </tbody>
        </table>

    </div>

</div>

<!-- 🔥 MODAL -->
<div id="deleteModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 ">

    <div class="bg-gray-900 text-white rounded-xl p-6 w-96 shadow-xl"  >

        <h3 class="text-xl font-bold mb-4">Delete Resume</h3>

        <p class="mb-6">Are you sure you want to delete this resume?</p>

        <div class="flex justify-end gap-3">

            <button onclick="closeModal()"
                class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                Cancel
            </button>

            <a id="confirmDeleteBtn"
               href="#"
               class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                Delete
            </a>

        </div>

    </div>

</div>

<script>
function openModal(id) {
    const modal = document.getElementById('deleteModal');
    const btn = document.getElementById('confirmDeleteBtn');

    // Set delete URL dynamically
    btn.href = "<?php echo BASE_URL; ?>modules/resume/delete.php?id=" + id;

    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}
</script>

<?php require_once __DIR__ . "/../../templates/footer.php"; ?>
