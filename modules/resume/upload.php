<?php
require_once __DIR__ . '/../../config/session.php';
$uploadToast = $_SESSION['upload_toast'] ?? '';
unset($_SESSION['upload_toast']);
// 🔒 PREVENT BACK CACHE
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");
require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login");
    exit;
}
// INCLUDE HEADER (THIS WAS MISSING)
require_once __DIR__ . "/../../templates/header.php";
?>
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/resume-upload.css?v=1">
<div class="min-h-screen flex items-center justify-center px-4">
    <?php if ($uploadToast !== ''): ?>
        <div id="uploadToast" class="upload-toast" role="status" aria-live="polite">
            <span class="grid h-7 w-7 shrink-0 place-items-center rounded-full border border-amber-200/20 bg-amber-200/10 text-amber-200" aria-hidden="true">!</span>
            <p class="flex-1 text-sm leading-6"><?php echo htmlspecialchars($uploadToast, ENT_QUOTES, 'UTF-8'); ?></p>
            <button type="button" id="dismissUploadToast" class="-mr-1 -mt-1 rounded-lg px-2 py-1 text-slate-400 hover:text-white" aria-label="Dismiss notification">×</button>
        </div>
    <?php endif; ?>
    <div class="resume-upload-card w-full max-w-xl rounded-3xl p-8">
        <h2 class="mb-6 text-center text-2xl font-bold">Upload Resume</h2>
        <!-- FORM -->
        <form action="<?php echo BASE_URL; ?>modules/resume/process_upload.php"
              method="POST"
              enctype="multipart/form-data"
              id="uploadForm">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

            <!-- DROP AREA -->
            <div id="dropArea"
                 class="resume-drop-zone border-2 border-dashed rounded-2xl p-8 text-center cursor-pointer"
                 role="button" tabindex="0" aria-describedby="uploadStatus">

                <div class="resume-visual" aria-hidden="true">
                    <span class="resume-orbit"></span>
                    <div class="resume-sheet">
                        <svg viewBox="0 0 24 24" fill="none"><path d="M12 16V4m0 0L7.5 8.5M12 4l4.5 4.5M5 14v4a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-4" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        <span class="resume-sheet-lines"></span>
                        <span class="resume-scan"></span>
                    </div>
                </div>
                <div class="drop-copy">
                    <p class="text-gray-200">Drag &amp; drop your resume here</p>
                    <p class="mt-2 text-sm text-gray-400">or click to browse · PDF only</p>
                </div>
                <div>
                    <p id="uploadStatus" class="upload-status" role="status" aria-live="polite">Uploading and processing your resume…</p>
                    <div class="upload-progress-track" aria-hidden="true"><span class="upload-progress-bar"></span></div>
                </div>

                <input type="file" name="resume" id="fileInput" class="hidden" accept=".pdf">
            </div>

            <!-- FILE PREVIEW -->
            <div id="filePreview" class="resume-file-preview mt-5 hidden rounded-2xl p-4">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <div class="min-w-0">
                        <p class="text-xs font-semibold uppercase tracking-[.16em] text-teal-200">Resume preview</p>
                        <p id="fileName" class="mt-1 truncate text-sm text-slate-300"></p>
                    </div>
                    <span class="shrink-0 rounded-full border border-teal-200/15 bg-teal-200/[.07] px-3 py-1 text-xs font-medium text-teal-100">PDF</span>
                </div>
                <iframe id="pdfPreview" class="resume-preview-frame" title="Preview of selected resume" hidden></iframe>
                <p id="previewFallback" class="hidden rounded-xl border border-slate-700 bg-slate-900/70 p-4 text-sm text-slate-300">
                    Preview is unavailable in this browser. You can still upload this PDF.
                </p>
            </div>

            <!-- ERROR -->
            <?php if (!empty($_SESSION['error'])): ?>
                <p class="text-red-400 mt-3 text-center">
                    <?php 
                        echo htmlspecialchars((string)$_SESSION['error'], ENT_QUOTES, 'UTF-8');
                        unset($_SESSION['error']); 
                    ?>
                </p>
            <?php endif; ?>

            <!-- SUBMIT -->
            <button type="submit" id="uploadButton"
                    class="resume-upload-button w-full mt-6 p-3 rounded-xl font-bold">
                Upload Resume
            </button>

        </form>

    </div>

</div>

<script>
// Elements
const dropArea = document.getElementById("dropArea");
const fileInput = document.getElementById("fileInput");
const filePreview = document.getElementById("filePreview");
const fileName = document.getElementById("fileName");
const pdfPreview = document.getElementById("pdfPreview");
const previewFallback = document.getElementById("previewFallback");
const uploadForm = document.getElementById("uploadForm");
const uploadButton = document.getElementById("uploadButton");
let previewObjectUrl = null;

const uploadToast = document.getElementById("uploadToast");
if (uploadToast) {
    const dismissToast = () => {
        uploadToast.classList.add("is-leaving");
        window.setTimeout(() => uploadToast.remove(), 400);
    };
    requestAnimationFrame(() => uploadToast.classList.add("is-visible"));
    const toastTimer = window.setTimeout(dismissToast, 4500);
    document.getElementById("dismissUploadToast").addEventListener("click", () => {
        window.clearTimeout(toastTimer);
        dismissToast();
    });
}

function openFileBrowser() {
    if (dropArea.classList.contains("is-uploading")) return;
    dropArea.classList.remove("is-browsing");
    void dropArea.offsetWidth; // restart the short click animation
    dropArea.classList.add("is-browsing");
    fileInput.click();
}

dropArea.addEventListener("click", openFileBrowser);
fileInput.addEventListener("click", (event) => event.stopPropagation());
dropArea.addEventListener("keydown", (event) => {
    if (event.key === "Enter" || event.key === " ") {
        event.preventDefault();
        openFileBrowser();
    }
});

fileInput.addEventListener("change", function() {
    handleFile(this.files[0]);
});

dropArea.addEventListener("dragover", (e) => {
    e.preventDefault();
    dropArea.classList.add("is-dragging");
});

dropArea.addEventListener("dragleave", () => {
    dropArea.classList.remove("is-dragging");
});

dropArea.addEventListener("drop", (e) => {
    e.preventDefault();
    dropArea.classList.remove("is-dragging");

    const file = e.dataTransfer.files[0];
    fileInput.files = e.dataTransfer.files;
    handleFile(file);
});

function handleFile(file) {
    if (!file) return;

    if (file.type !== "application/pdf") {
        alert("Only PDF files allowed!");
        fileInput.value = "";
        return;
    }

    filePreview.classList.remove("hidden");
    fileName.textContent = file.name;
    if (previewObjectUrl) URL.revokeObjectURL(previewObjectUrl);
    previewObjectUrl = URL.createObjectURL(file);
    pdfPreview.hidden = false;
    previewFallback.classList.add("hidden");
    pdfPreview.src = previewObjectUrl + "#toolbar=1&navpanes=0";
    pdfPreview.onerror = () => {
        pdfPreview.hidden = true;
        previewFallback.classList.remove("hidden");
    };
    dropArea.classList.remove("is-browsing");
}

uploadForm.addEventListener("submit", (event) => {
    if (!fileInput.files.length) {
        event.preventDefault();
        alert("Please choose a PDF resume first.");
        openFileBrowser();
        return;
    }

    // Give the browser one frame to paint the status before the native form upload starts.
    event.preventDefault();
    dropArea.classList.remove("is-browsing");
    dropArea.classList.add("is-uploading");
    uploadButton.disabled = true;
    uploadButton.classList.add("opacity-70", "cursor-not-allowed");
    uploadButton.textContent = "Uploading…";

    requestAnimationFrame(() => uploadForm.submit());
});
</script>

<?php
// INCLUDE FOOTER
require_once __DIR__ . "/../../templates/footer.php";
?>
