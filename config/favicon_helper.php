<?php
// Ensure favicons exist on disk
(function () {
    $rootDir = dirname(__DIR__);
    $icoPath = $rootDir . '/favicon.ico';
    $pngPath = $rootDir . '/assets/images/favicon.png';
    $assetsDir = $rootDir . '/assets/images';

    if (!is_dir($assetsDir)) {
        @mkdir($assetsDir, 0777, true);
    }

    if (file_exists($icoPath) && file_exists($pngPath)) {
        return;
    }

    if (!extension_loaded('gd')) {
        file_put_contents(__DIR__ . '/favicon_debug.txt', 'gd extension not loaded');
        return;
    }
    file_put_contents(__DIR__ . '/favicon_debug.txt', 'gd loaded, starting generation');

    $size = 64;
    $img = imagecreatetruecolor($size, $size);
    if (!$img) {
        return;
    }

    imagesavealpha($img, true);
    $trans = imagecolorallocatealpha($img, 0, 0, 0, 127);
    imagefill($img, 0, 0, $trans);

    // Color definitions
    $blue = imagecolorallocate($img, 37, 99, 235);        // #2563eb
    $white = imagecolorallocate($img, 255, 255, 255);     // #ffffff
    $lightBlue = imagecolorallocate($img, 147, 197, 253); // #93c5fd
    $darkText = imagecolorallocate($img, 30, 41, 59);     // #1e293b
    $subText = imagecolorallocate($img, 100, 116, 139);   // #64748b
    $accentGreen = imagecolorallocate($img, 34, 197, 94); // #22c55e
    $badgeBg = imagecolorallocate($img, 15, 23, 42);      // #0f172a

    // 1. Draw rounded background squircle
    $r = 14;
    imagefilledellipse($img, $r, $r, $r * 2, $r * 2, $blue);
    imagefilledellipse($img, $size - $r, $r, $r * 2, $r * 2, $blue);
    imagefilledellipse($img, $r, $size - $r, $r * 2, $r * 2, $blue);
    imagefilledellipse($img, $size - $r, $size - $r, $r * 2, $r * 2, $blue);
    imagefilledrectangle($img, $r, 0, $size - $r, $size, $blue);
    imagefilledrectangle($img, 0, $r, $size, $size - $r, $blue);

    // 2. Document base (white sheet)
    imagefilledrectangle($img, 17, 11, 47, 51, $white);

    // 3. Document fold
    $cutPts = [35, 11, 47, 11, 47, 23];
    imagefilledpolygon($img, $cutPts, 3, $blue);
    $foldPts = [35, 11, 35, 23, 47, 23];
    imagefilledpolygon($img, $foldPts, 3, $lightBlue);

    // 4. Text lines
    imagefilledrectangle($img, 22, 27, 40, 29, $blue);
    imagefilledrectangle($img, 22, 33, 35, 35, $darkText);
    imagefilledrectangle($img, 22, 39, 30, 41, $subText);

    // 5. AI Sparkle badge in bottom right
    imagefilledellipse($img, 45, 43, 22, 22, $badgeBg);
    imagefilledellipse($img, 45, 43, 18, 18, $accentGreen);
    $starPts = [45, 37, 46, 42, 51, 43, 46, 44, 45, 49, 44, 44, 39, 43, 44, 42];
    imagefilledpolygon($img, $starPts, 8, $white);

    // Save PNG files
    imagepng($img, $pngPath);
    @copy($pngPath, $rootDir . '/favicon.png');

    // Create valid ICO file containing PNG payload
    $pngData = file_get_contents($pngPath);
    if ($pngData) {
        $pngLen = strlen($pngData);
        $icoHeader = pack('vvv', 0, 1, 1);
        $icoEntry = pack('CCCCvvVV', $size, $size, 0, 0, 1, 32, $pngLen, 22);
        file_put_contents($icoPath, $icoHeader . $icoEntry . $pngData);
        @copy($icoPath, $assetsDir . '/favicon.ico');
    }

    imagedestroy($img);
})();
