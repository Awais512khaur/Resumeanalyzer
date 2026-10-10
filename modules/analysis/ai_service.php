<?php
/**
 * AI Suggestion Service for Resume Analyzer
 * Powered by Google Gemini Free API with intelligent fallback
 */

if (!function_exists('generate_ai_suggestions')) {
    function generate_ai_suggestions($resume_text, $job_description, $matched_words = [], $missing_words = [], $score = 0, $ats_score = 0) {
        $apiKey = defined('GEMINI_API_KEY') ? trim(GEMINI_API_KEY) : '';

        // If no API key is set, use smart rule-based fallback
        if (empty($apiKey)) {
            return get_fallback_suggestions($missing_words, $score, $ats_score, $resume_text);
        }

        // Prepare context
        $topMissing = !empty($missing_words) ? implode(', ', array_slice(array_values($missing_words), 0, 12)) : 'None detected';
        $topMatched = !empty($matched_words) ? implode(', ', array_slice(array_values($matched_words), 0, 8)) : 'None detected';

        // Keep snippet lengths optimal for speed and token limits
        $resumeSnippet = mb_substr(trim($resume_text), 0, 4000);
        $jobSnippet = mb_substr(trim($job_description), 0, 2500);

        $prompt = "You are an expert ATS (Applicant Tracking System) reviewer and hiring manager.
Review the following resume against the job description and provide 4 to 5 specific, highly actionable recommendations to improve the resume, optimize it for ATS, and increase interview selection.

RESUME CONTENT:
{$resumeSnippet}

JOB DESCRIPTION:
{$jobSnippet}

CURRENT METRICS:
- Match Score: {$score}%
- ATS Compatibility Score: {$ats_score}%
- Matched Keywords: {$topMatched}
- Missing Keywords from Job Description: {$topMissing}

INSTRUCTIONS:
1. Provide exactly 4 or 5 concise, actionable bullet suggestions.
2. Directly mention specific missing technical tools, skills, or certifications from the job description if applicable.
3. Suggest quantifying accomplishments (e.g., metrics, percentages, team sizes, revenue impact).
4. Suggest ATS layout or structural improvements if sections are missing or weak.
5. Do NOT include markdown formatting like asterisks or bold (**).
6. Do NOT include conversational filler, introductory remarks, or concluding remarks.
7. Return each suggestion on a separate line starting with a dash (-).";

        $payload = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ],
            "generationConfig" => [
                "temperature" => 0.3,
                "maxOutputTokens" => 1024
            ]
        ];

        // Ultra-fast free Google Gemini models in order of priority
        $models = [
            "gemini-3.5-flash-lite",
            "gemini-3.1-flash-lite",
            "gemini-flash-latest"
        ];

        foreach ($models as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($apiKey);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Content-Type: application/json"
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_TIMEOUT, 12);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && !empty($response)) {
                $data = json_decode($response, true);
                $rawText = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';

                if (!empty($rawText)) {
                    $parsed = parse_ai_suggestions($rawText);
                    if (!empty($parsed)) {
                        return $parsed;
                    }
                }
            }
        }

        // If all API calls fail, fallback gracefully
        return get_fallback_suggestions($missing_words, $score, $ats_score, $resume_text);
    }

    function parse_ai_suggestions($rawText) {
        $lines = preg_split('/\r\n|\r|\n/', $rawText);
        $suggestions = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Strip bullets, numbering, asterisks, dashes
            $clean = preg_replace('/^(\d+[\.\)]|\*|\-|\•)\s*/u', '', $line);
            $clean = trim(str_replace(['**', '__', '"', '`'], '', $clean));

            // Skip meta headers or conversational lines
            if (stripos($clean, 'here are') === 0 || stripos($clean, 'suggestions:') === 0 || stripos($clean, 'recommendations:') === 0) {
                continue;
            }

            if (strlen($clean) >= 15) {
                $suggestions[] = $clean;
            }
        }

        return array_slice($suggestions, 0, 5);
    }

    function get_fallback_suggestions($missing_words, $score, $ats_score, $resume_text) {
        $suggestions = [];

        if (!empty($missing_words)) {
            $topSkills = implode(', ', array_slice(array_values($missing_words), 0, 5));
            $suggestions[] = "Incorporate critical missing keywords ($topSkills) into your skills and work history.";
        }

        if ($score < 50) {
            $suggestions[] = "Align your project descriptions and job summaries more closely with the target position's requirements.";
        }

        if ($ats_score < 60) {
            $suggestions[] = "Optimize resume structure for ATS scanners using standard section titles and clean single-column formatting.";
        }

        $lowerResume = strtolower($resume_text);
        if (strpos($lowerResume, 'experience') === false) {
            $suggestions[] = "Add a clearly defined 'Professional Experience' section detailing your past roles and achievements.";
        }

        if (strpos($lowerResume, 'skills') === false) {
            $suggestions[] = "Add a dedicated 'Technical & Core Skills' section categorized by languages, frameworks, and tools.";
        }

        $suggestions[] = "Quantify your achievements using measurable metrics (e.g., 'Improved performance by 25%' or 'Managed 5+ projects').";

        return array_slice($suggestions, 0, 5);
    }
}
