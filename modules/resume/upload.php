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
    <div class="resume-upload-card w-full max-w-xl rounded-3xl p-8">
        <h2 class="mb-6 text-center text-2xl font-bold">Upload Resume</h2>
        <style>
            .resume-upload-card { background: linear-gradient(145deg, rgba(35, 42, 57, .97), rgba(24, 32, 48, .96)); border: 1px solid rgba(148, 163, 184, .1); box-shadow: 0 28px 80px rgba(0, 0, 0, .35), inset 0 1px rgba(255, 255, 255, .04); }
            .resume-drop-zone { min-height: 230px; position: relative; overflow: hidden; isolation: isolate; border-color: rgba(148, 163, 184, .55); background: radial-gradient(ellipse at 50% 35%, rgba(45, 212, 191, .08), transparent 65%), rgba(15, 23, 42, .18); transition: border-color .25s ease, background .25s ease, box-shadow .25s ease, transform .25s ease; }
            .resume-drop-zone:hover, .resume-drop-zone.is-browsing { border-color: #67e8f9; background-color: rgba(45, 212, 191, .055); box-shadow: inset 0 0 35px rgba(45, 212, 191, .06); }
            .resume-drop-zone.is-dragging { border-color: #5eead4; background-color: rgba(45, 212, 191, .11); box-shadow: 0 0 0 5px rgba(45, 212, 191, .09), inset 0 0 45px rgba(45, 212, 191, .1); transform: scale(1.01); }
            .resume-drop-zone.is-uploading { border-color: rgba(103, 232, 249, .8); cursor: wait; background: radial-gradient(ellipse at 50% 40%, rgba(45, 212, 191, .15), transparent 70%), rgba(15, 23, 42, .5); }
            .resume-visual { position: relative; width: 92px; height: 96px; margin: 0 auto 17px; perspective: 500px; transform-style: preserve-3d; }
            .resume-orbit { position: absolute; inset: 9px -4px; border: 1px solid rgba(103, 232, 249, .38); border-radius: 50%; transform: rotateX(66deg) rotateZ(-18deg); box-shadow: 0 0 16px rgba(45, 212, 191, .12); animation: resume-orbit 7s linear infinite; }
            .resume-orbit::before, .resume-orbit::after { content: ""; position: absolute; width: 7px; height: 7px; border-radius: 50%; background: #9af2d5; box-shadow: 0 0 12px #67e8f9; }
            .resume-orbit::before { top: 10px; left: 7px; }
            .resume-orbit::after { right: 9px; bottom: 10px; width: 5px; height: 5px; background: #67d9f3; }
            .resume-sheet { position: absolute; top: 10px; left: 25px; width: 43px; height: 57px; display: grid; place-items: center; border: 1px solid rgba(255, 255, 255, .85); border-radius: 7px 11px 7px 7px; color: #0f2630; background: linear-gradient(145deg, #f8feff, #bff7ef 72%, #83e5e9); box-shadow: 9px 11px 0 rgba(45, 212, 191, .12), 0 13px 25px rgba(0, 0, 0, .25); transform: rotateY(-18deg) rotateX(7deg) rotateZ(5deg); transform-style: preserve-3d; animation: resume-float 3.2s ease-in-out infinite; }
            .resume-sheet::after { content: ""; position: absolute; top: -1px; right: -1px; width: 13px; height: 13px; border-left: 1px solid rgba(15, 38, 48, .15); border-bottom: 1px solid rgba(15, 38, 48, .15); border-radius: 0 8px 0 5px; background: linear-gradient(135deg, #dffdfa, #79dedf); }
            .resume-sheet svg { width: 24px; height: 24px; margin-top: 8px; filter: drop-shadow(0 2px 2px rgba(15, 38, 48, .15)); }
            .resume-sheet-lines { position: absolute; left: 9px; right: 9px; bottom: 9px; height: 9px; border-top: 2px solid rgba(15, 38, 48, .28); border-bottom: 2px solid rgba(15, 38, 48, .18); }
            .resume-drop-zone.is-uploading .resume-sheet { animation: resume-upload 1.7s ease-in-out infinite; }
            .resume-scan { display: none; position: absolute; left: -5px; right: -5px; top: 4px; height: 2px; z-index: 2; background: #67e8f9; box-shadow: 0 0 9px #67e8f9, 0 0 22px rgba(103, 232, 249, .8); }
            .resume-drop-zone.is-uploading .resume-scan { display: block; animation: resume-scan 1.7s ease-in-out infinite; }
            .resume-upload-icon { display: none; }
            .drop-copy { position: relative; z-index: 1; }
            .upload-status { display: none; position: relative; z-index: 1; color: #d9fbf4; font-weight: 600; }
            .is-uploading .drop-copy { display: none; }
            .is-uploading .upload-status { display: block; }
            .upload-progress-track { display: none; width: 132px; height: 3px; margin: 13px auto 0; overflow: hidden; border-radius: 999px; background: rgba(148, 163, 184, .24); }
            .is-uploading .upload-progress-track { display: block; }
            .upload-progress-bar { display: block; width: 42%; height: 100%; border-radius: inherit; background: linear-gradient(90deg, #9af2d5, #67d9f3); box-shadow: 0 0 12px rgba(103, 217, 243, .7); transform: translateX(-110%); }
            .is-uploading .upload-progress-bar { animation: upload-progress 1.35s ease-in-out infinite; }
            .resume-upload-button { color: #07151b; background: linear-gradient(100deg, #9af2d5, #67d9f3); box-shadow: 0 10px 25px rgba(45, 212, 191, .13); transition: transform .2s ease, box-shadow .2s ease, filter .2s ease; }
            .resume-upload-button:hover { transform: translateY(-2px); filter: saturate(1.12); box-shadow: 0 14px 30px rgba(45, 212, 191, .24); }
            .resume-upload-button:disabled { transform: none; }
            .resume-file-preview { border: 1px solid rgba(148, 163, 184, .16); background: rgba(15, 23, 42, .48); }
            .resume-preview-frame { display: block; width: 100%; height: min(62vh, 560px); min-height: 340px; border: 0; border-radius: 12px; background: #fff; }
            .resume-preview-frame[hidden] { display: none; }
            @keyframes resume-float { 0%, 100% { transform: translateY(0) rotateY(-18deg) rotateX(7deg) rotateZ(5deg); } 50% { transform: translateY(-7px) rotateY(12deg) rotateX(-2deg) rotateZ(-3deg); } }
            @keyframes resume-orbit { to { transform: rotateX(66deg) rotateZ(342deg); } }
            @keyframes resume-upload { 0%, 100% { transform: translateY(0) rotateY(-18deg) rotateX(7deg) rotateZ(5deg); } 50% { transform: translateY(-4px) rotateY(18deg) rotateX(-6deg) rotateZ(-5deg); } }
            @keyframes resume-scan { 0%, 100% { top: 8px; opacity: .45; } 50% { top: 64px; opacity: 1; } }
            @keyframes upload-progress { to { transform: translateX(340%); } }
            @media (prefers-reduced-motion: reduce) { .resume-orbit, .resume-sheet, .resume-scan, .upload-progress-bar { animation: none !important; } .resume-drop-zone, .resume-upload-button { transition: none !important; } }
        </style>
        <!-- FORM -->
        <form action="<?php echo BASE_URL; ?>modules/resume/process_upload.php"
              method="POST"
              enctype="multipart/form-data"
              id="uploadForm">

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
                        echo $_SESSION['error']; 
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
