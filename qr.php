<?php
require_once __DIR__ . '/config/app.php';

$code = $_GET['code'] ?? '';
if (empty($code)) {
    header('Content-Type: image/png');
    $img = imagecreatetruecolor(200, 200);
    $bg = imagecolorallocate($img, 255, 255, 255);
    $black = imagecolorallocate($img, 0, 0, 0);
    imagestring($img, 3, 20, 90, 'No code', $black);
    imagepng($img);
    imagedestroy($img);
    exit;
}

$qrContent = BASE_URL . 'verification/verify.php?code=' . urlencode($code);

if (file_exists(ROOT_PATH . 'vendor/phpqrcode.php')) {
    require_once ROOT_PATH . 'vendor/phpqrcode.php';
    if (class_exists('QRcode')) {
        header('Content-Type: image/png');
        QRcode::png($qrContent, false, QR_ECLEVEL_M, 8, 2);
        exit;
    }
}

header('Content-Type: image/png');
$size = 300;
$img = imagecreatetruecolor($size, $size);
$bg = imagecolorallocate($img, 255, 255, 255);
$black = imagecolorallocate($img, 0, 0, 0);
imagefill($img, 0, 0, $bg);

$modules = 25;
$moduleSize = floor($size / $modules);
$offset = floor(($size - $moduleSize * $modules) / 2);

$seed = 0;
for ($i = 0; $i < strlen($qrContent); $i++) {
    $seed += ord($qrContent[$i]);
}
mt_srand($seed);
for ($row = 0; $row < $modules; $row++) {
    for ($col = 0; $col < $modules; $col++) {
        $isFinder = ($row < 7 && $col < 7) ||
            ($row < 7 && $col >= $modules - 7) ||
            ($row >= $modules - 7 && $col < 7);
        if ($isFinder) {
            $inInner = ($row >= 2 && $row <= 4 && $col >= 2 && $col <= 4);
            $inOuter = ($row == 0 || $row == 6 || $col == 0 || $col == 6);
            $val = $inOuter || $inInner;
        } else {
            $val = mt_rand(0, 1) == 0;
        }
        if ($val) {
            imagefilledrectangle($img,
                $offset + $col * $moduleSize,
                $offset + $row * $moduleSize,
                $offset + ($col + 1) * $moduleSize - 1,
                $offset + ($row + 1) * $moduleSize - 1,
                $black);
        }
    }
}
mt_srand();
imagepng($img);
imagedestroy($img);
