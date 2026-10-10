<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

function createChunk($type, $data) {
    return pack('N', strlen($data)) . $type . $data . pack('N', crc32($type . $data));
}

function buildResumePng() {
    $width = 32;
    $height = 32;

    $raw = '';
    for ($y = 0; $y < $height; $y++) {
        $raw .= "\x00"; // filter type 0 (None)
        for ($x = 0; $x < $width; $x++) {
            $r = 0; $g = 0; $b = 0; $a = 0; // Transparent default

            // Check if inside rounded card (bounds 1..30)
            $insideCard = false;
            if ($x >= 1 && $x <= 30 && $y >= 1 && $y <= 30) {
                $insideCard = true;
                // Corner rounding
                if ($x < 6 && $y < 6 && (($x-6)*($x-6) + ($y-6)*($y-6) > 25)) $insideCard = false;
                if ($x > 25 && $y < 6 && (($x-25)*($x-25) + ($y-6)*($y-6) > 25)) $insideCard = false;
                if ($x < 6 && $y > 25 && (($x-6)*($x-6) + ($y-25)*($y-25) > 25)) $insideCard = false;
                if ($x > 25 && $y > 25 && (($x-25)*($x-25) + ($y-25)*($y-25) > 25)) $insideCard = false;
            }

            if ($insideCard) {
                // Card background: vibrant blue
                $r = 37; $g = 99; $b = 235; $a = 255;

                // Resume document: x from 8..23, y from 6..25
                if ($x >= 8 && $x <= 23 && $y >= 6 && $y <= 25) {
                    if ($x >= 18 && $y <= 11) {
                        // Folded top-right ear
                        if (($x - 18) + (11 - $y) <= 5) {
                            $r = 147; $g = 197; $b = 253; // Fold color
                        } else {
                            $r = 37; $g = 99; $b = 235; // Behind fold
                        }
                    } else {
                        $r = 255; $g = 255; $b = 255; // White paper

                        // Text lines
                        if ($y >= 13 && $y <= 14 && $x >= 11 && $x <= 20) {
                            $r = 37; $g = 99; $b = 235; // Blue header line
                        } elseif ($y >= 17 && $y <= 18 && $x >= 11 && $x <= 18) {
                            $r = 71; $g = 85; $b = 105; // Slate line
                        } elseif ($y >= 21 && $y <= 22 && $x >= 11 && $x <= 15) {
                            $r = 148; $g = 163; $b = 184; // Light slate line
                        }
                    }
                }

                // AI Badge on lower right corner: center at (23, 21)
                $d2 = ($x - 23)*($x - 23) + ($y - 21)*($y - 21);
                if ($d2 <= 26) {
                    if ($d2 > 16) {
                        $r = 15; $g = 23; $b = 42; // Dark border
                    } else {
                        $r = 34; $g = 197; $b = 94; // Vibrant green badge
                    }
                    // Star highlight
                    if ($d2 <= 2 || ($x == 23 && abs($y - 21) <= 2) || ($y == 21 && abs($x - 23) <= 2)) {
                        $r = 255; $g = 255; $b = 255;
                    }
                }
            }

            $raw .= chr($r) . chr($g) . chr($b) . chr($a);
        }
    }

    $ihdr = pack('NNCCCCC', $width, $height, 8, 6, 0, 0, 0);
    $idat = gzdeflate($raw, 9);

    $png = "\x89PNG\r\n\x1a\n"
         . createChunk('IHDR', $ihdr)
         . createChunk('IDAT', $idat)
         . createChunk('IEND', '');

    return $png;
}

$rootDir = __DIR__;
$pngData = buildResumePng();

// 1. Save favicon.png to root and assets
file_put_contents($rootDir . '/favicon.png', $pngData);
file_put_contents($rootDir . '/assets/images/favicon.png', $pngData);

// 2. Build 32x32 ICO file containing PNG payload
$icoHeader = pack('vvv', 0, 1, 1);
$icoEntry  = pack('CCCCvvVV', 32, 32, 0, 0, 1, 32, strlen($pngData), 22);
$icoData   = $icoHeader . $icoEntry . $pngData;

file_put_contents($rootDir . '/favicon.ico', $icoData);
file_put_contents($rootDir . '/assets/images/favicon.ico', $icoData);

echo "SUCCESS! Generated favicon.png (" . strlen($pngData) . " bytes) and favicon.ico (" . strlen($icoData) . " bytes).\n";
