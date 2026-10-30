<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/audit.php';

// Safe dynamic detection of TCPDF library
$tcpdfCandidates = [
    __DIR__ . '/../../tools/tcpdf/tcpdf.php',
    __DIR__ . '/../../tcpdf/tcpdf.php',
    __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php',
];
$tcpdfAvailable = false;
foreach ($tcpdfCandidates as $candidate) {
    if (is_file($candidate)) {
        require_once $candidate;
        $tcpdfAvailable = true;
        break;
    }
}
if (!$tcpdfAvailable && is_file(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
    if (class_exists('TCPDF')) {
        $tcpdfAvailable = true;
    }
}

if (!filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT)) {
    http_response_code(400);
    exit('Invalid report request.');
}

$id = (int)$_GET['id'];
$statement = $pdo->prepare('SELECT * FROM analysis_results WHERE id = ?');
$statement->execute([$id]);
$result = $statement->fetch();
if (!$result) {
    http_response_code(404);
    exit('Report not found.');
}

$ownerStatement = $pdo->prepare(
    'SELECT r.user_id, r.file_name, p.name AS position_name, u.name AS owner_name
     FROM resumes r
     INNER JOIN users u ON u.id = r.user_id
     LEFT JOIN positions p ON p.id = r.position
     WHERE r.id = ?'
);
$ownerStatement->execute([(int)$result['resume_id']]);
$resume = $ownerStatement->fetch();
if (!$resume || (($_SESSION['role'] ?? '') !== 'admin' && (int)$resume['user_id'] !== (int)($_SESSION['user_id'] ?? 0))) {
    http_response_code(403);
    exit('You do not have permission to download this report.');
}

audit_log($pdo, 'analysis.report_downloaded', 'PDF report downloaded for resume: ' . $resume['file_name'], 'analysis', $id);

function pdf_escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function pdf_keywords(?string $value): array
{
    $keywords = preg_split('/[,;|\r\n]+/', $value ?? '') ?: [];
    $keywords = array_map('trim', $keywords);
    return array_values(array_unique(array_filter($keywords, static fn($item) => $item !== '')));
}

function pdf_keyword_rows(array $keywords, string $background, string $foreground): string
{
    if (!$keywords) {
        return '<tr><td style="color:#8796ad;padding:8px 0;">No keywords recorded</td></tr>';
    }

    $rows = '';
    foreach (array_chunk($keywords, 3) as $row) {
        $rows .= '<tr>';
        foreach ($row as $keyword) {
            $rows .= '<td width="33.3%" style="padding:4px 5px 4px 0;">'
                . '<table cellpadding="6" cellspacing="0" border="0" width="100%"><tr>'
                . '<td bgcolor="' . $background . '" style="color:' . $foreground . ';font-size:9px;font-weight:bold;">'
                . pdf_escape($keyword) . '</td></tr></table></td>';
        }
        for ($i = count($row); $i < 3; $i++) {
            $rows .= '<td width="33.3%"></td>';
        }
        $rows .= '</tr>';
    }
    return $rows;
}

$score = max(0, min(100, (int)($result['score'] ?? 0)));
$atsScore = max(0, min(100, (int)($result['ats_score'] ?? 0)));
$matched = pdf_keywords($result['matched_keywords'] ?? '');
$missing = pdf_keywords($result['missing_keywords'] ?? '');
$suggestions = array_values(array_filter(array_map('trim', explode('|', (string)($result['suggestions'] ?? '')))));
$generatedAt = !empty($result['created_at']) ? strtotime($result['created_at']) : time();
$generatedAt = $generatedAt ?: time();
$resumeName = pdf_escape((string)($resume['file_name'] ?: 'Resume'));
$candidateName = pdf_escape((string)($resume['owner_name'] ?: 'Candidate'));
$positionName = pdf_escape((string)($resume['position_name'] ?: 'General review'));

// ── MODE 1: NATIVE TCPDF BINARY DOWNLOAD (If TCPDF is available) ──
if ($tcpdfAvailable && class_exists('TCPDF')) {
    class ResumeAnalysisPdf extends TCPDF
    {
        public function Header(): void
        {
            $this->SetFillColor(8, 13, 27);
            $this->Rect(0, 0, 210, 36, 'F');
            $this->SetFillColor(112, 231, 216);
            $this->RoundedRect(16, 10, 12, 12, 2, '1111', 'F');
            $this->SetTextColor(8, 25, 34);
            $this->SetFont('helvetica', 'B', 10);
            $this->SetXY(16, 10);
            $this->Cell(12, 12, 'R', 0, 0, 'C');
            $this->SetTextColor(245, 248, 255);
            $this->SetFont('helvetica', 'B', 16);
            $this->SetXY(33, 9);
            $this->Cell(110, 8, 'ResumeAI', 0, 1, 'L');
            $this->SetTextColor(157, 176, 199);
            $this->SetFont('helvetica', '', 8);
            $this->SetXY(33, 19);
            $this->Cell(130, 6, 'CAREER INSIGHTS  /  RESUME REVIEW', 0, 0, 'L');
            $this->SetDrawColor(103, 224, 213);
            $this->SetLineWidth(0.8);
            $this->Line(16, 35, 194, 35);
        }

        public function Footer(): void
        {
            $this->SetY(-15);
            $this->SetDrawColor(220, 226, 235);
            $this->SetLineWidth(0.2);
            $this->Line(16, $this->GetY(), 194, $this->GetY());
            $this->SetY(-12);
            $this->SetTextColor(119, 133, 154);
            $this->SetFont('helvetica', '', 8);
            $this->Cell(130, 6, 'ResumeAI  ·  Private resume analysis', 0, 0, 'L');
            $this->Cell(48, 6, 'PAGE ' . $this->getAliasNumPage() . ' OF ' . $this->getAliasNbPages(), 0, 0, 'R');
        }
    }

    $pdf = new ResumeAnalysisPdf('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('ResumeAI');
    $pdf->SetAuthor('ResumeAI');
    $pdf->SetTitle('Resume Analysis — ' . ($resume['file_name'] ?: 'Resume'));
    $pdf->SetSubject('Resume match and ATS analysis report');
    $pdf->setPrintHeader(true);
    $pdf->setPrintFooter(true);
    $pdf->SetMargins(16, 43, 16);
    $pdf->SetHeaderMargin(0);
    $pdf->SetFooterMargin(14);
    $pdf->SetAutoPageBreak(true, 20);
    $pdf->SetFont('helvetica', '', 10);
    $pdf->AddPage();

    $html = '<table cellpadding="0" cellspacing="0" border="0" width="100%">'
        . '<tr><td><font color="#12a995" size="8"><b>PERSONALIZED REVIEW</b></font></td></tr>'
        . '<tr><td style="padding-top:3px;"><font color="#142238" size="20"><b>Resume Analysis Report</b></font></td></tr>'
        . '<tr><td style="padding-top:3px;padding-bottom:14px;"><font color="#738198" size="9">Generated ' . date('F j, Y', $generatedAt) . ' at ' . date('g:i A', $generatedAt) . '</font></td></tr>'
        . '</table>'
        . '<table cellpadding="8" cellspacing="0" border="0" width="100%" bgcolor="#f2f7fa">'
        . '<tr>'
        . '<td width="33%"><font color="#7b8ca2" size="8"><b>CANDIDATE</b></font><br /><font color="#17263b" size="10"><b>' . $candidateName . '</b></font></td>'
        . '<td width="37%"><font color="#7b8ca2" size="8"><b>RESUME</b></font><br /><font color="#17263b" size="9">' . $resumeName . '</font></td>'
        . '<td width="30%"><font color="#7b8ca2" size="8"><b>TARGET ROLE</b></font><br /><font color="#17263b" size="9">' . $positionName . '</font></td>'
        . '</tr></table>'
        . '<br /><br />'
        . '<table cellpadding="0" cellspacing="0" border="0" width="100%"><tr>'
        . '<td width="50%" style="padding:0 7px 0 0;">'
        . '<table cellpadding="10" cellspacing="0" border="0" width="100%" bgcolor="#111d31"><tr><td style="border:1px solid #26364d;">'
        . '<font color="#9aa9be" size="9"><b>MATCH SCORE</b></font><br />'
        . '<font color="#68d9c7" size="25"><b>' . $score . '%</b></font><br />'
        . '<font color="#8c9bb1" size="8">Overall role alignment</font>'
        . '</td></tr></table></td>'
        . '<td width="50%" style="padding:0 0 0 7px;">'
        . '<table cellpadding="10" cellspacing="0" border="0" width="100%" bgcolor="#111d31"><tr><td style="border:1px solid #26364d;">'
        . '<font color="#9aa9be" size="9"><b>ATS SCORE</b></font><br />'
        . '<font color="#76c9f2" size="25"><b>' . $atsScore . '%</b></font><br />'
        . '<font color="#8c9bb1" size="8">Applicant tracking readiness</font>'
        . '</td></tr></table></td>'
        . '</tr></table>'
        . '<br /><br />'
        . '<table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td style="border-bottom:1px solid #dce4ec;padding-bottom:6px;"><font color="#17263b" size="12"><b>Skills match</b></font></td></tr></table>'
        . '<br /><table cellpadding="0" cellspacing="0" border="0" width="100%"><tr>'
        . '<td width="50%" style="padding-right:8px;"><font color="#128c76" size="9"><b>MATCHED SKILLS  (' . count($matched) . ')</b></font><br /><br />'
        . '<table cellpadding="0" cellspacing="0" border="0" width="100%">' . pdf_keyword_rows($matched, '#e3f6f1', '#147c6d') . '</table></td>'
        . '<td width="50%" style="padding-left:8px;"><font color="#ba5967" size="9"><b>MISSING SKILLS  (' . count($missing) . ')</b></font><br /><br />'
        . '<table cellpadding="0" cellspacing="0" border="0" width="100%">' . pdf_keyword_rows($missing, '#fff0f0', '#a84e59') . '</table></td>'
        . '</tr></table>'
        . '<br /><br />'
        . '<table cellpadding="0" cellspacing="0" border="0" width="100%"><tr><td style="border-bottom:1px solid #dce4ec;padding-bottom:6px;"><font color="#17263b" size="12"><b>Recommended next steps</b></font></td></tr></table>'
        . '<br />';

    if ($suggestions) {
        $html .= '<table cellpadding="7" cellspacing="0" border="0" width="100%">';
        foreach ($suggestions as $index => $suggestion) {
            $html .= '<tr><td width="9%" bgcolor="#e7f6f3" align="center"><font color="#128c76" size="9"><b>' . ($index + 1) . '</b></font></td>'
                . '<td width="91%" bgcolor="#f6f8fb"><font color="#34445a" size="9">' . pdf_escape($suggestion) . '</font></td></tr>';
        }
        $html .= '</table>';
    } else {
        $html .= '<font color="#738198" size="9">No recommendations were recorded for this analysis.</font>';
    }

    $pdf->writeHTML($html, true, false, true, false, '');
    header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
    $pdf->Output('resume_analysis_report.pdf', 'D');
    exit;
}

// ── MODE 2: ELEGANT PRINT-READY HTML REPORT (Zero external dependencies fallback) ──
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resume Analysis Report — <?php echo $resumeName; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; color: #0f172a !important; padding: 0 !important; }
            .print-card { box-shadow: none !important; border: 1px solid #cbd5e1 !important; }
            @page { margin: 15mm; size: A4; }
        }
    </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen py-8 px-4 font-sans">
    <div class="max-w-3xl mx-auto">
        <!-- ACTION BAR -->
        <div class="no-print mb-6 flex flex-wrap items-center justify-between gap-3 bg-slate-800/90 border border-slate-700 p-4 rounded-2xl backdrop-blur-md">
            <div>
                <h1 class="text-base font-bold text-white flex items-center gap-2">
                    📄 Resume Analysis Report
                </h1>
                <p class="text-xs text-slate-400">Ready to save or print</p>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="window.print()" class="px-4 py-2 bg-gradient-to-r from-teal-400 to-cyan-400 hover:from-teal-300 hover:to-cyan-300 text-slate-950 font-bold rounded-xl text-sm shadow-md transition cursor-pointer flex items-center gap-2">
                    🖨️ Save as PDF / Print
                </button>
                <a href="<?php echo BASE_URL; ?>history" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white font-medium rounded-xl text-sm transition">
                    ← Back to History
                </a>
            </div>
        </div>

        <!-- REPORT CANVAS (Styled for Screen & Print) -->
        <div class="print-card bg-slate-800/60 border border-slate-700/80 rounded-2xl p-6 sm:p-10 shadow-2xl backdrop-blur-md">
            
            <!-- HEADER -->
            <div class="border-b border-slate-700/80 pb-6 mb-6 flex items-start justify-between flex-wrap gap-4">
                <div>
                    <span class="text-xs font-bold uppercase tracking-wider text-teal-400">Personalized Career Insights</span>
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white mt-1">Resume Analysis Report</h2>
                    <p class="text-xs text-slate-400 mt-1">Generated on <?php echo date('F j, Y · g:i A', $generatedAt); ?></p>
                </div>
                <div class="text-right">
                    <span class="inline-block px-3 py-1 rounded-full bg-teal-500/10 border border-teal-500/30 text-teal-300 font-semibold text-xs">
                        ResumeAI Verified
                    </span>
                </div>
            </div>

            <!-- METADATA GRID -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 bg-slate-900/50 border border-slate-700/50 p-4 rounded-xl mb-6 text-sm">
                <div>
                    <span class="text-xs text-slate-400 block uppercase font-semibold">Candidate</span>
                    <strong class="text-white"><?php echo $candidateName; ?></strong>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block uppercase font-semibold">Resume File</span>
                    <span class="text-slate-200 truncate block"><?php echo $resumeName; ?></span>
                </div>
                <div>
                    <span class="text-xs text-slate-400 block uppercase font-semibold">Target Position</span>
                    <span class="text-slate-200"><?php echo $positionName; ?></span>
                </div>
            </div>

            <!-- SCORES -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                <div class="bg-gradient-to-br from-teal-950/40 to-slate-900/80 border border-teal-500/30 p-5 rounded-xl">
                    <span class="text-xs uppercase font-bold text-teal-300 tracking-wider">Role Match Score</span>
                    <div class="text-4xl font-extrabold text-teal-300 mt-2"><?php echo $score; ?>%</div>
                    <p class="text-xs text-slate-400 mt-1">Overall candidate alignment with job requirements</p>
                </div>
                <div class="bg-gradient-to-br from-cyan-950/40 to-slate-900/80 border border-cyan-500/30 p-5 rounded-xl">
                    <span class="text-xs uppercase font-bold text-cyan-300 tracking-wider">ATS Readiness Score</span>
                    <div class="text-4xl font-extrabold text-cyan-300 mt-2"><?php echo $atsScore; ?>%</div>
                    <p class="text-xs text-slate-400 mt-1">Applicant tracking system scan compatibility</p>
                </div>
            </div>

            <!-- SKILLS BREAKDOWN -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-emerald-950/20 border border-emerald-500/20 p-5 rounded-xl">
                    <h3 class="text-sm font-bold text-emerald-300 mb-3 flex items-center gap-2">
                        <span>✓</span> Matched Skills (<?php echo count($matched); ?>)
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <?php if ($matched): ?>
                            <?php foreach ($matched as $m): ?>
                                <span class="px-2.5 py-1 bg-emerald-500/10 border border-emerald-500/30 text-emerald-200 rounded-lg text-xs font-medium">
                                    <?php echo pdf_escape($m); ?>
                                </span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="text-xs text-slate-400">No matching skills identified.</span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="bg-rose-950/20 border border-rose-500/20 p-5 rounded-xl">
                    <h3 class="text-sm font-bold text-rose-300 mb-3 flex items-center gap-2">
                        <span>✕</span> Skills to Improve (<?php echo count($missing); ?>)
                    </h3>
                    <div class="flex flex-wrap gap-2">
                        <?php if ($missing): ?>
                            <?php foreach ($missing as $mis): ?>
                                <span class="px-2.5 py-1 bg-rose-500/10 border border-rose-500/30 text-rose-200 rounded-lg text-xs font-medium">
                                    <?php echo pdf_escape($mis); ?>
                                </span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="text-xs text-slate-400">No missing skills detected.</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- RECOMMENDATIONS -->
            <div>
                <h3 class="text-sm font-bold text-white mb-3">Actionable AI Recommendations</h3>
                <?php if ($suggestions): ?>
                    <div class="space-y-2.5">
                        <?php foreach ($suggestions as $idx => $sug): ?>
                            <div class="flex items-start gap-3 p-3 bg-slate-900/40 border border-slate-700/60 rounded-xl text-xs sm:text-sm text-slate-300 leading-relaxed">
                                <span class="flex-shrink-0 w-6 h-6 rounded-full bg-teal-500/20 text-teal-300 font-bold text-xs flex items-center justify-center">
                                    <?php echo $idx + 1; ?>
                                </span>
                                <span><?php echo pdf_escape($sug); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-xs text-slate-400">No specific recommendations recorded.</p>
                <?php endif; ?>
            </div>

            <!-- FOOTER -->
            <div class="border-t border-slate-700/80 pt-6 mt-8 flex items-center justify-between text-xs text-slate-400">
                <span>ResumeAI Career Insights Platform</span>
                <span>Page 1 of 1</span>
            </div>

        </div>
    </div>
</body>
</html>
