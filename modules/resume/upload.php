<?php
// session_start();
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
<div class="min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-xl bg-white/10 backdrop-blur-lg p-8 rounded-2xl shadow-xl">
        <h2 class="text-2xl font-bold mb-6 text-center">Upload Resume</h2>
        <style>
            .resume-drop-zone { min-height: 180px; position: relative; overflow: hidden; }
            .resume-upload-icon { width: 58px; height: 58px; margin: 0 auto 14px; color: #93c5fd; transition: transform .25s ease, color .25s ease; }
            .resume-drop-zone:hover .resume-upload-icon,
            .resume-drop-zone.is-browsing .resume-upload-icon { color: #fff; transform: translateY(-5px) scale(1.08); }
            .resume-drop-zone.is-browsing { animation: upload-zone-pulse .75s ease; }
            .resume-drop-zone.is-uploading { border-color: #60a5fa; cursor: wait; background: rgba(37, 99, 235, .12); }
            .resume-drop-zone.is-uploading::after { content: ""; position: absolute; inset: -70%; background: linear-gradient(110deg, transparent 43%, rgba(147, 197, 253, .18) 50%, transparent 57%); animation: upload-shimmer 1.3s linear infinite; pointer-events: none; }
            .upload-spinner { display: none; width: 46px; height: 46px; margin: 0 auto 15px; border: 4px solid rgba(191, 219, 254, .3); border-top-color: #fff; border-radius: 50%; animation: upload-spin .8s linear infinite; }
            .is-uploading .upload-spinner { display: block; }
            .is-uploading .resume-upload-icon, .is-uploading .drop-copy { display: none; }
            .upload-status { display: none; position: relative; z-index: 1; color: #dbeafe; font-weight: 600; }
            .is-uploading .upload-status { display: block; }
            @keyframes upload-spin { to { transform: rotate(360deg); } }
            @keyframes upload-shimmer { to { transform: translateX(36%); } }
            @keyframes upload-zone-pulse { 50% { border-color: #93c5fd; box-shadow: 0 0 0 7px rgba(96, 165, 250, .14); } }
            @media (prefers-reduced-motion: reduce) { .resume-drop-zone, .resume-upload-icon, .resume-drop-zone::after, .upload-spinner { animation: none !important; transition: none !important; } }
        </style>
        <!-- FORM -->
        <form action="<?php echo BASE_URL; ?>modules/resume/process_upload.php"
              method="POST"
              enctype="multipart/form-data"
              id="uploadForm">

            <!-- DROP AREA -->
            <div id="dropArea"
                 class="resume-drop-zone border-2 border-dashed border-gray-400 rounded-xl p-8 text-center cursor-pointer hover:border-blue-400 transition"
                 role="button" tabindex="0" aria-describedby="uploadStatus">

                <svg class="resume-upload-icon" viewBox="0 0 64 64" fill="none" aria-hidden="true">
                    <path d="M32 42V12m0 0-11 11m11-11 11 11M14 39v9a4 4 0 0 0 4 4h28a4 4 0 0 0 4-4v-9" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
                <div class="drop-copy">
                    <p class="text-gray-300">Drag & Drop your resume here</p>
                    <p class="text-sm text-gray-400 mt-2">or click to browse</p>
                </div>
                <div class="upload-spinner" aria-hidden="true"></div>
                <p id="uploadStatus" class="upload-status" role="status" aria-live="polite">Uploading your resume…</p>

                <input type="file" name="resume" id="fileInput" class="hidden" accept=".pdf">
            </div>

            <!-- FILE PREVIEW -->
            <div id="filePreview" class="mt-4 hidden">
                <p class="text-green-400">Selected file: <span id="fileName"></span></p>
            </div>

            <!-- ERROR -->
            <?php if (!empty($_SESSION['error'])): ?>
                <p class="text-red-400 mt-3 text-center">
                    <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']); 
                    ?>
                </p>
            <?php endif; ?>

            <!-- SUBMIT -->
            <button type="submit" id="uploadButton"
                    class="w-full mt-6 bg-blue-600 hover:bg-blue-700 p-3 rounded-lg font-bold">
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
const uploadForm = document.getElementById("uploadForm");
const uploadButton = document.getElementById("uploadButton");

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
    dropArea.classList.add("border-blue-400");
});

dropArea.addEventListener("dragleave", () => {
    dropArea.classList.remove("border-blue-400");
});

dropArea.addEventListener("drop", (e) => {
    e.preventDefault();
    dropArea.classList.remove("border-blue-400");

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
