<?php

$source = __DIR__ . '/../resources/image/logo.jpeg';
$resDir = __DIR__ . '/../nativephp/android/app/src/main/res';

$densities = [
    'mdpi'    => ['launcher' => 48,  'foreground' => 108],
    'hdpi'    => ['launcher' => 72,  'foreground' => 162],
    'xhdpi'   => ['launcher' => 96,  'foreground' => 216],
    'xxhdpi'  => ['launcher' => 144, 'foreground' => 324],
    'xxxhdpi' => ['launcher' => 192, 'foreground' => 432],
];

$src = imagecreatefromjpeg($source);
if (!$src) {
    fwrite(STDERR, "Failed to read $source\n");
    exit(1);
}

$sw = imagesx($src);
$sh = imagesy($src);
$square = min($sw, $sh);
$sx = intdiv($sw - $square, 2);
$sy = intdiv($sh - $square, 2);

foreach ($densities as $dir => $sizes) {
    $outDir = "$resDir/mipmap-$dir";
    if (!is_dir($outDir)) {
        fwrite(STDERR, "Missing $outDir\n");
        continue;
    }

    foreach (['ic_launcher' => $sizes['launcher'], 'ic_launcher_round' => $sizes['launcher'], 'ic_launcher_foreground' => $sizes['foreground']] as $name => $size) {
        $dst = imagecreatetruecolor($size, $size);
        imagecopyresampled($dst, $src, 0, 0, $sx, $sy, $size, $size, $square, $square);
        $path = "$outDir/$name.webp";
        imagewebp($dst, $path, 90);
        imagedestroy($dst);
        echo "wrote $path ({$size}x{$size})\n";
    }
}

imagedestroy($src);
