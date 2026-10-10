<?php
// session_start();

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";

// ADMIN CHECK
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL);
    exit;
}

$user_id = $_SESSION['user_id'];
//Insert
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['position_name']) && !isset($_POST['edit_id'])) {

    $name = trim($_POST['position_name']);
    $description = $_POST['description'];

    $stmt = $pdo->prepare("INSERT INTO positions (name, description, created_by) VALUES (?, ?, ?)");
    $stmt->execute([$name, $description, $user_id]);

    header("Location: " . BASE_URL . "admin_positions");
    exit;
}

//update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {

    $id = (int) $_POST['edit_id'];
    $name = trim($_POST['position_name']);
    $description = $_POST['description'];

    $stmt = $pdo->prepare("UPDATE positions SET name = ?, description = ? WHERE id = ?");
    $stmt->execute([$name, $description, $id]);

    header("Location: " . BASE_URL . "admin_positions");
    exit;
}
//Delete Query
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {

    $id = (int) $_POST['delete_id'];

    $stmt = $pdo->prepare("DELETE FROM positions WHERE id = ?");
    $stmt->execute([$id]);

    header("Location: " . BASE_URL . "admin_positions");
    exit;
}
$where = [];
$params = [];

// SEARCH
if (!empty($_GET['search'])) {
    $where[] = "p.name LIKE ?";
    $params[] = "%" . $_GET['search'] . "%";
}

// USER FILTER
if (!empty($_GET['user'])) {
    $where[] = "p.created_by = ?";
    $params[] = $_GET['user'];
}

// DATE FILTER
if (!empty($_GET['date'])) {
    $where[] = "DATE(p.created_at) = ?";
    $params[] = $_GET['date'];
}

// BUILD QUERY
$sql = "
    SELECT p.*, u.name as created_by_name
    FROM positions p
    LEFT JOIN users u ON p.created_by = u.id
";

if (!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

$sql .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$positions = $stmt->fetchAll();

require_once __DIR__ . "/../../templates/header.php";
?>

<style>
.ck-editor__editable {
    color: black !important;
    background: white !important;
}
</style>

<div class="max-w-6xl mx-auto px-4">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold">Manage Positions</h2>

        <button onclick="openAddModal()"
                class="bg-blue-600 px-4 py-2 rounded hover:bg-blue-700">
            Add Position
        </button>
    </div>

    <div class="bg-white/10 backdrop-blur-lg rounded-xl overflow-hidden">
<form method="GET" class="mb-6 grid md:grid-cols-4 gap-4">

    <!-- SEARCH -->
    <input type="text" name="search"
        value="<?php echo $_GET['search'] ?? ''; ?>"
        placeholder="Search position..."
        class="p-3 rounded bg-white/20 text-white">
    <!-- DATE -->
    <input type="date" name="date"
        value="<?php echo $_GET['date'] ?? ''; ?>"
        class="p-3 rounded bg-white/20 text-white">

    <!-- BUTTON -->
    <button class="bg-blue-600 rounded p-3 hover:bg-blue-700">
        Filter
    </button>

</form>
        <table class="w-full text-left">

            <thead class="bg-white/20">
                <tr>
                    <th class="p-3">#</th>
                    <th class="p-3">Name</th>
                    <th class="p-3">Description</th>
                    <th class="p-3">Created By</th>
                    <th class="p-3">Date</th>
                    <th class="p-3 text-center">Action</th>
                </tr>
            </thead>

            <tbody>

                <?php foreach ($positions as $index => $p): ?>

                <tr class="border-b border-white/10 hover:bg-white/10">

                    <td class="p-3"><?php echo $index + 1; ?></td>

                    <td class="p-3 text-blue-400">
                        <?php echo htmlspecialchars($p['name']); ?>
                    </td>

                    <td class="p-3 text-gray-300">
                        <?php echo $p['description']; ?>
                    </td>

                    <td class="p-3 text-yellow-400">
                        <?php echo htmlspecialchars($p['created_by_name'] ?? 'N/A'); ?>
                    </td>

                    <td class="p-3">
                        <?php echo date("d M Y", strtotime($p['created_at'])); ?>
                    </td>

                    <td class="p-3 text-center space-x-2">

                        <!-- EDIT -->
                        <button onclick="openEditModal(<?php echo htmlspecialchars(json_encode($p)); ?>)"
                                class="bg-yellow-500 px-3 py-1 rounded hover:bg-yellow-600">
                            Edit
                        </button>

                        <!-- DELETE -->
                        <button onclick="confirmDelete(<?php echo $p['id']; ?>)"
                                class="bg-red-500 px-3 py-1 rounded hover:bg-red-600">
                            Delete
                        </button>

                    </td>

                </tr>

                <?php endforeach; ?>

            </tbody>

        </table>

    </div>

</div>

<!-- ADD MODAL -->
<div id="addModal" class="fixed inset-0 bg-black/60 hidden flex justify-center items-center">
    <div class="bg-gray-900 p-6 rounded-xl w-96">

        <h3 class="text-xl font-bold mb-4">Add Position</h3>

        <form method="POST">

            <input type="text" name="position_name"
                   placeholder="Position Name"
                   required
                   class="w-full p-3 mb-3 rounded bg-white/20 text-white">

            <textarea id="editorAdd" name="description"
                      class="w-full p-3 mb-4 rounded bg-white text-black"></textarea>

            <button class="bg-blue-600 px-4 py-2 rounded w-full">Save</button>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div id="editModal" class="fixed inset-0 bg-black/60 hidden flex justify-center items-center">
    <div class="bg-gray-900 p-6 rounded-xl w-96">

        <h3 class="text-xl font-bold mb-4">Edit Position</h3>

        <form method="POST">

            <input type="hidden" name="edit_id" id="edit_id">

            <input type="text" name="position_name" id="edit_name"
                   class="w-full p-3 mb-3 rounded bg-white/20 text-white">

            <textarea id="editorEdit" name="description"
                      class="w-full p-3 mb-4 rounded bg-white text-black"></textarea>

            <button class="bg-yellow-500 px-4 py-2 rounded w-full">Update</button>
        </form>
    </div>
</div>

<!-- DELETE MODAL -->
<div id="deleteModal" class="fixed inset-0 bg-black/60 hidden flex justify-center items-center">
    <div class="bg-gray-900 p-6 rounded-xl w-80 text-center">

        <form method="POST">
            <input type="hidden" name="delete_id" id="deleteId">

            <p class="mb-4">Delete this position?</p>

            <button class="bg-red-600 px-4 py-2 rounded w-full">Delete</button>
        </form>
    </div>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<script>
let addEditor, editEditor;

ClassicEditor.create(document.querySelector('#editorAdd')).then(e => addEditor = e);
ClassicEditor.create(document.querySelector('#editorEdit')).then(e => editEditor = e);

function openAddModal() {
    document.getElementById('addModal').classList.remove('hidden');
}

function openEditModal(data) {
    document.getElementById('editModal').classList.remove('hidden');

    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_name').value = data.name;

    setTimeout(() => {
        editEditor.setData(data.description || '');
    }, 200);
}

function confirmDelete(id) {
    document.getElementById('deleteModal').classList.remove('hidden');
    document.getElementById('deleteId').value = id;
}
</script>
<?php require_once __DIR__ . "/../../templates/footer.php"; ?>