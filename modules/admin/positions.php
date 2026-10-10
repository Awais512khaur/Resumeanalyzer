<?php
require_once __DIR__ . '/../../config/session.php';

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../config/audit.php";
require_once __DIR__ . "/../analysis/position_skills.php";

// ADMIN CHECK
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: " . BASE_URL);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !resume_csrf_is_valid($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Your session expired. Refresh the page and try again.');
}

$user_id = $_SESSION['user_id'];
$normalizeRequiredSkills = static function ($input) {
    $skills = preg_split('/[,\r\n]+/', (string)$input);
    $uniqueSkills = [];
    $seen = [];
    foreach ($skills as $skill) {
        $skill = trim($skill);
        $key = strtolower($skill);
        if ($skill !== '' && !isset($seen[$key])) {
            $seen[$key] = true;
            $uniqueSkills[] = $skill;
        }
    }
    return implode(', ', $uniqueSkills);
};
//Insert
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['position_name']) && !isset($_POST['edit_id'])) {

    $name = trim($_POST['position_name']);
    $description = $_POST['description'];
    $requiredSkills = $normalizeRequiredSkills($_POST['required_skills'] ?? '');

    $stmt = $pdo->prepare("INSERT INTO positions (name, description, required_skills, created_by) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $description, $requiredSkills, $user_id]);
    audit_log($pdo, 'position.created', 'Position created: ' . $name, 'position', (int)$pdo->lastInsertId());

    header("Location: " . BASE_URL . "admin_positions");
    exit;
}

//update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {

    $id = (int) $_POST['edit_id'];
    $name = trim($_POST['position_name']);
    $description = $_POST['description'];
    $requiredSkills = $normalizeRequiredSkills($_POST['required_skills'] ?? '');

    $stmt = $pdo->prepare("UPDATE positions SET name = ?, description = ?, required_skills = ? WHERE id = ?");
    $stmt->execute([$name, $description, $requiredSkills, $id]);
    audit_log($pdo, 'position.updated', 'Position updated: ' . $name, 'position', $id);

    header("Location: " . BASE_URL . "admin_positions");
    exit;
}
//Delete Query
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {

    $id = (int) $_POST['delete_id'];

    $stmt = $pdo->prepare("DELETE FROM positions WHERE id = ?");
    $stmt->execute([$id]);
    audit_log($pdo, 'position.deleted', 'Position deleted.', 'position', $id);

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
foreach ($positions as &$position) {
    $position['skills_list'] = get_position_required_skills($position);
    if (empty($position['required_skills']) && $position['skills_list']) {
        $position['required_skills'] = implode(', ', $position['skills_list']);
    }
}
unset($position);

require_once __DIR__ . "/../../templates/header.php";
?>

<style>
.position-modal { position:fixed; inset:0; z-index:80; display:none; align-items:center; justify-content:center; overflow-y:auto; padding:1rem; background:rgba(3,8,18,.76); backdrop-filter:blur(10px); }
.position-modal.is-open { display:flex; animation:modal-fade .18s ease-out; }
.position-modal-card { width:min(100%,32rem); max-height:calc(100vh - 2rem); overflow-y:auto; border:1px solid rgba(255,255,255,.12); border-radius:1.35rem; padding:1.5rem; background:linear-gradient(145deg,rgba(27,42,65,.98),rgba(12,20,36,.98)); box-shadow:0 32px 100px rgba(0,0,0,.56),inset 0 1px rgba(255,255,255,.08); }
.position-modal-heading { color:#f5f7ff; font-size:1.35rem; font-weight:750; letter-spacing:-.025em; }
.position-modal-copy { color:#96a6bf; font-size:.85rem; line-height:1.5; }
.position-modal-close { display:grid; width:2.25rem; height:2.25rem; place-items:center; border:1px solid rgba(255,255,255,.1); border-radius:.75rem; background:rgba(255,255,255,.05); color:#a9b7ca; font-size:1.2rem; }
.position-modal-close:hover { background:rgba(255,255,255,.1); color:white; }
.position-modal input[type="text"] { margin-top:1.1rem; margin-bottom:1rem; min-height:3.25rem; border-radius:.85rem; padding:.85rem 1rem; }
.position-modal .ck.ck-editor { width:100%; overflow:hidden; border:1px solid rgba(255,255,255,.13); border-radius:.85rem; }
.position-modal .ck.ck-toolbar { border:0!important; border-bottom:1px solid rgba(255,255,255,.1)!important; background:#142238!important; padding:.35rem!important; }
.position-modal .ck.ck-toolbar .ck-button { color:#b9c8dc!important; }
.position-modal .ck.ck-toolbar .ck-button:hover, .position-modal .ck.ck-toolbar .ck-button.ck-on { background:rgba(112,218,216,.14)!important; color:#a8f1e8!important; }
.position-modal .ck.ck-toolbar .ck-toolbar__separator { background:rgba(255,255,255,.14)!important; }
.position-modal .ck.ck-dropdown__panel { border-color:rgba(255,255,255,.12)!important; background:#142238!important; }
.position-modal .ck.ck-dropdown__panel .ck-list__item .ck-button { color:#e2eaf4!important; }
.position-modal .ck.ck-editor__main>.ck-editor__editable { min-height:9rem; max-height:17rem; border:0!important; background:#0d1728!important; color:#e6eef8!important; box-shadow:none!important; padding:.9rem 1rem!important; }
.position-modal .ck.ck-editor__main>.ck-editor__editable:focus { border:0!important; box-shadow:inset 0 0 0 1px rgba(117,232,216,.35)!important; }
.position-modal .ck.ck-editor__main>.ck-editor__editable.ck-placeholder::before { color:#6f8099!important; }
.position-modal .ck-content p { margin:.35rem 0; }
.modal-submit { width:100%; min-height:3.2rem; margin-top:1rem; border-radius:.9rem!important; background:linear-gradient(115deg,#9af2d5,#67d9f3)!important; color:#06131a!important; font-weight:750!important; box-shadow:0 12px 28px rgba(50,188,178,.16); }
.modal-submit:hover { box-shadow:0 14px 34px rgba(50,188,178,.28); }
.modal-cancel { min-height:2.7rem; border:1px solid rgba(255,255,255,.1)!important; border-radius:.8rem!important; background:rgba(255,255,255,.06)!important; color:#d3deed!important; }
@keyframes modal-fade { from {opacity:0} to {opacity:1} }
@media(max-width:480px) { .position-modal { padding:.75rem; } .position-modal-card { padding:1.15rem; border-radius:1.1rem; } }
@media(prefers-reduced-motion:reduce) { .position-modal.is-open { animation:none; } }
</style>

<div class="max-w-6xl mx-auto px-4">

    <div class="flex justify-between items-center mb-6">
        <h2 class="text-3xl font-bold">Manage Positions</h2>

        <button onclick="openAddModal()"
                class="bg-blue-600 px-4 py-2 rounded hover:bg-blue-700">
            Add Position
        </button>
    </div>

    <div class="app-table-wrap bg-white/10 backdrop-blur-lg rounded-xl p-3 sm:p-4">
<form method="GET" class="positions-filter mb-4 grid gap-3 md:grid-cols-[1.4fr_1fr_auto]">

    <!-- SEARCH -->
    <input type="text" name="search"
        value="<?php echo htmlspecialchars((string)($_GET['search'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
        placeholder="Search position..."
        class="p-3 rounded bg-white/20 text-white">
    <!-- DATE -->
    <input type="date" name="date"
        value="<?php echo htmlspecialchars((string)($_GET['date'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
        class="p-3 rounded bg-white/20 text-white">

    <!-- BUTTON -->
    <button class="bg-blue-600 rounded p-3 hover:bg-blue-700">
        Filter
    </button>

</form>
        <div class="overflow-x-auto rounded-xl">
        <table class="w-full text-left">

            <thead class="bg-white/20">
                <tr>
                    <th class="p-3">#</th>
                    <th class="p-3">Name</th>
                    <th class="p-3">Required Skills</th>
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

                    <td class="p-3 text-teal-100">
                        <?php echo htmlspecialchars($p['required_skills'] ?? 'Not set'); ?>
                    </td>

                    <td class="p-3 text-gray-300">
                        <?php echo htmlspecialchars((string)$p['description'], ENT_QUOTES, 'UTF-8'); ?>
                    </td>

                    <td class="p-3 text-yellow-400">
                        <?php echo htmlspecialchars($p['created_by_name'] ?? 'N/A'); ?>
                    </td>

                    <td class="p-3">
                        <?php echo date("d M Y", strtotime($p['created_at'])); ?>
                    </td>

                    <td class="p-3 text-center space-x-2">

                        <!-- EDIT -->
                        <button onclick="openEditModal(JSON.parse(this.dataset.position))"
                                data-position="<?php echo htmlspecialchars(json_encode($p, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8'); ?>"
                                class="bg-yellow-500 px-3 py-1 rounded hover:bg-yellow-600">
                            Edit
                        </button>

                        <!-- DELETE -->
                        <button onclick="confirmDelete(<?php echo (int)$p['id']; ?>)"
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

</div>

<!-- ADD MODAL -->
<div id="addModal" class="position-modal" role="dialog" aria-modal="true" aria-labelledby="addModalTitle" onclick="if(event.target===this) closePositionModal('addModal')">
    <div class="position-modal-card">
        <div class="mb-5 flex items-start justify-between gap-4">
            <div><p class="mb-1 text-[.68rem] font-bold uppercase tracking-[.18em] text-teal-200">Position library</p><h3 id="addModalTitle" class="position-modal-heading">Add a position</h3><p class="position-modal-copy mt-1">Describe the role so resumes can be matched against it.</p></div>
            <button type="button" class="position-modal-close" aria-label="Close dialog" onclick="closePositionModal('addModal')">×</button>
        </div>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

            <label class="mb-2 block text-sm font-medium text-slate-300" for="position_name">Position name</label>
            <input type="text" id="position_name" name="position_name"
                   placeholder="e.g. Product Designer"
                   required
                   class="w-full">

            <label class="mb-2 block text-sm font-medium text-slate-300" for="required_skills">Required skills</label>
            <textarea id="required_skills" name="required_skills" rows="3" placeholder="e.g. PHP, MySQL, REST APIs, communication" class="mb-4 w-full rounded-xl border border-white/10 bg-white/[.04] p-3 text-sm text-slate-100 placeholder:text-slate-500"></textarea>
            <p class="-mt-2 mb-4 text-xs text-slate-500">Separate skills with commas or new lines. These skills determine the resume match score.</p>

            <label class="mb-2 block text-sm font-medium text-slate-300" for="editorAdd">Role description</label>
            <textarea id="editorAdd" name="description"></textarea>

            <button class="modal-submit" type="submit">Save position <span aria-hidden="true">→</span></button>
        </form>
    </div>
</div>

<!-- EDIT MODAL -->
<div id="editModal" class="position-modal" role="dialog" aria-modal="true" aria-labelledby="editModalTitle" onclick="if(event.target===this) closePositionModal('editModal')">
    <div class="position-modal-card">
        <div class="mb-5 flex items-start justify-between gap-4">
            <div><p class="mb-1 text-[.68rem] font-bold uppercase tracking-[.18em] text-teal-200">Position library</p><h3 id="editModalTitle" class="position-modal-heading">Edit position</h3><p class="position-modal-copy mt-1">Update the role details used in resume matching.</p></div>
            <button type="button" class="position-modal-close" aria-label="Close dialog" onclick="closePositionModal('editModal')">×</button>
        </div>

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

            <input type="hidden" name="edit_id" id="edit_id">

            <label class="mb-2 block text-sm font-medium text-slate-300" for="edit_name">Position name</label>
            <input type="text" name="position_name" id="edit_name" required class="w-full">

            <label class="mb-2 block text-sm font-medium text-slate-300" for="edit_required_skills">Required skills</label>
            <textarea id="edit_required_skills" name="required_skills" rows="3" placeholder="e.g. PHP, MySQL, REST APIs, communication" class="mb-4 w-full rounded-xl border border-white/10 bg-white/[.04] p-3 text-sm text-slate-100 placeholder:text-slate-500"></textarea>
            <p class="-mt-2 mb-4 text-xs text-slate-500">Separate skills with commas or new lines. These skills determine the resume match score.</p>

            <label class="mb-2 block text-sm font-medium text-slate-300" for="editorEdit">Role description</label>
            <textarea id="editorEdit" name="description"></textarea>

            <button class="modal-submit" type="submit">Save changes <span aria-hidden="true">→</span></button>
        </form>
    </div>
</div>

<!-- DELETE MODAL -->
<div id="deleteModal" class="position-modal" role="dialog" aria-modal="true" aria-labelledby="deleteModalTitle" onclick="if(event.target===this) closePositionModal('deleteModal')">
    <div class="position-modal-card max-w-sm text-center">

        <form method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="delete_id" id="deleteId">

            <div class="mx-auto mb-4 grid h-12 w-12 place-items-center rounded-2xl border border-rose-300/15 bg-rose-300/[.08] text-xl text-rose-200">!</div>
            <h3 id="deleteModalTitle" class="position-modal-heading">Delete this position?</h3>
            <p class="position-modal-copy mt-2 mb-6">This action cannot be undone.</p>

            <div class="flex justify-center gap-3">
                <button type="button" class="modal-cancel px-5" onclick="closePositionModal('deleteModal')">Cancel</button>
                <button class="rounded-xl border border-rose-300/20 bg-rose-300/[.12] px-5 py-2.5 font-semibold text-rose-200 hover:bg-rose-300/[.2]" type="submit">Delete position</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<script>
let addEditor, editEditor;

const editorConfig = {
    toolbar: {
        items: ['undo', 'redo', '|', 'heading', '|', 'bold', 'italic', '|', 'bulletedList', 'numberedList', 'link'],
        shouldNotGroupWhenFull: true
    }
};
ClassicEditor.create(document.querySelector('#editorAdd'), editorConfig).then(e => addEditor = e);
ClassicEditor.create(document.querySelector('#editorEdit'), editorConfig).then(e => editEditor = e);

function openAddModal() {
    openPositionModal('addModal');
}

function openEditModal(data) {
    openPositionModal('editModal');

    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_name').value = data.name;
    document.getElementById('edit_required_skills').value = data.required_skills || '';

    setTimeout(() => {
        editEditor.setData(data.description || '');
    }, 200);
}

function confirmDelete(id) {
    openPositionModal('deleteModal');
    document.getElementById('deleteId').value = id;
}

function openPositionModal(id) {
    document.getElementById(id).classList.add('is-open');
    document.body.style.overflow = 'hidden';
}

function closePositionModal(id) {
    document.getElementById(id).classList.remove('is-open');
    if (!document.querySelector('.position-modal.is-open')) document.body.style.overflow = '';
}

document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.position-modal.is-open').forEach(modal => closePositionModal(modal.id));
    }
});
</script>
<?php require_once __DIR__ . "/../../templates/footer.php"; ?>
