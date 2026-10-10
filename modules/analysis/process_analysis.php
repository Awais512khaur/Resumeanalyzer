<?php
session_start();

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/ai_service.php";

// VALIDATE INPUT
if (!isset($_POST['resume_id'], $_POST['job_description'])) {
    header("Location: " . BASE_URL . "analyze");
    exit;
}

// INPUT
$resume_id = $_POST['resume_id'];
$original_job_description = trim($_POST['job_description']);
$job_description = strtolower($original_job_description);
$position_id = $_POST['position_id'] ?? null;

// convert empty → null
if ($position_id === '') {
    $position_id = null;
}

// 🔥 UPDATE RESUME POSITION
if ($position_id !== null) {
    $stmt = $pdo->prepare("UPDATE resumes SET position = ? WHERE id = ?");
    $stmt->execute([$position_id, $resume_id]);
}

// GET RESUME
$stmt = $pdo->prepare("SELECT * FROM resumes WHERE id = ?");
$stmt->execute([$resume_id]);
$resume = $stmt->fetch();

if (!$resume) {
    die("Resume not found");
}

// FILE PATH
$filePath = __DIR__ . "/../../" . $resume['file_path'];

if (!file_exists($filePath)) {
    die("Resume file not found");
}

// PDF TEXT EXTRACTION
$pdftotext = __DIR__ . "/../../tools/poppler/Library/bin/pdftotext.exe";

if (!file_exists($pdftotext)) {
    die("pdftotext.exe not found");
}

$command = "\"$pdftotext\" \"$filePath\" -";
$text = shell_exec($command);

if (!$text) {
    die("Failed to extract text from PDF");
}

$raw_resume_text = $text;

// CLEAN TEXT FOR KEYWORD MATCHING
$resume_text = strtolower($text);
$resume_text = preg_replace("/[^a-z0-9 ]/", " ", $resume_text);
$job_description = preg_replace("/[^a-z0-9 ]/", " ", $job_description);

// STOP WORDS
$stop_words = ["the","and","is","in","at","of","a","to","for","with","on","by","an","be","or","as","are","from","that","this","will"];

$resume_words = array_diff(array_unique(explode(" ", $resume_text)), $stop_words);
$job_words = array_diff(array_unique(explode(" ", $job_description)), $stop_words);

$resume_words = array_filter($resume_words);
$job_words = array_filter($job_words);

// MATCHING
$matched = array_intersect($resume_words, $job_words);
$missing = array_diff($job_words, $resume_words);

// SCORE
$score = count($job_words) > 0 
    ? round((count($matched) / count($job_words)) * 100) 
    : 0;

// ATS SCORE
$ats_score = 0;

if (strlen($resume_text) > 1000) $ats_score += 20;
if (strpos($resume_text, "experience") !== false) $ats_score += 20;
if (strpos($resume_text, "skills") !== false) $ats_score += 20;
if (strpos($resume_text, "education") !== false) $ats_score += 20;
if ($score > 60) $ats_score += 20;

$ats_score = min(100, $ats_score);

// 🤖 GENERATE AI SUGGESTIONS
$suggestions = generate_ai_suggestions(
    $raw_resume_text,
    $original_job_description,
    $matched,
    $missing,
    $score,
    $ats_score
);

// SAVE RESULT
$stmt = $pdo->prepare("
    INSERT INTO analysis_results 
    (resume_id, score, ats_score, matched_keywords, missing_keywords, suggestions)
    VALUES (?, ?, ?, ?, ?, ?)
");

$stmt->execute([
    $resume_id,
    $score,
    $ats_score,
    implode(",", $matched),
    implode(",", $missing),
    implode(" | ", $suggestions)
]);

// REDIRECT
header("Location: " . BASE_URL . "history");
exit;