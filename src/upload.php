<?php

declare(strict_types=1);

function store_image(array $file, string $directory, bool $required = true): ?string
{
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error === UPLOAD_ERR_NO_FILE && !$required) {
        return null;
    }
    if ($error !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('Upload gambar gagal. Silakan pilih ulang gambarnya.');
    }

    $size = (int) ($file['size'] ?? 0);
    $storedMaxBytes = (int) config('security.upload_max_bytes', 500 * 1024);
    $sourceMaxBytes = (int) config('security.upload_source_max_bytes', 25 * 1024 * 1024);
    if ($size < 1 || $size > $sourceMaxBytes) {
        throw new InvalidArgumentException('Foto tidak dapat diproses. Pilih foto asli berukuran maksimal 25 MB.');
    }

    $temporary = (string) ($file['tmp_name'] ?? '');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($temporary);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    if (!isset($allowed[$mime])) {
        throw new InvalidArgumentException('Format gambar harus JPG, PNG, atau WebP.');
    }

    $dimensions = @getimagesize($temporary);
    if (!$dimensions || ($dimensions[0] * $dimensions[1]) > (int) config('security.upload_max_pixels')) {
        throw new InvalidArgumentException('Dimensi gambar tidak valid atau terlalu besar.');
    }

    $targetDirectory = PUBLIC_ROOT . '/uploads/' . trim($directory, '/');
    if (!is_dir($targetDirectory) && !mkdir($targetDirectory, 0755, true) && !is_dir($targetDirectory)) {
        throw new RuntimeException('Folder upload belum dapat ditulis.');
    }

    if ($size > $storedMaxBytes) {
        return compress_and_store_image($temporary, $mime, $targetDirectory, $storedMaxBytes, $dimensions);
    }

    $filename = bin2hex(random_bytes(18)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($temporary, $targetDirectory . '/' . $filename)) {
        throw new RuntimeException('Gambar tidak dapat disimpan. Periksa izin folder uploads.');
    }
    return $filename;
}

/**
 * Fallback server-side. Alur normal sudah mengompres di browser agar upload cepat,
 * tetapi server tetap dapat mengoptimalkan foto besar jika ekstensi GD tersedia.
 */
function compress_and_store_image(string $source, string $mime, string $targetDirectory, int $maxBytes, array $dimensions): string
{
    $loaders = [
        'image/jpeg' => 'imagecreatefromjpeg',
        'image/png' => 'imagecreatefrompng',
        'image/webp' => 'imagecreatefromwebp',
    ];
    $loader = $loaders[$mime] ?? null;
    if (!$loader || !function_exists($loader) || !function_exists('imagecreatetruecolor')) {
        throw new InvalidArgumentException('Foto belum berhasil dioptimalkan otomatis. Aktifkan JavaScript lalu pilih ulang fotonya.');
    }

    $sourceImage = @$loader($source);
    if (!$sourceImage) {
        throw new InvalidArgumentException('Isi foto tidak dapat dibaca. Pilih foto JPG, PNG, atau WebP lain.');
    }

    $sourceWidth = (int) $dimensions[0];
    $sourceHeight = (int) $dimensions[1];
    $maxDimension = 2200;
    $scale = min(1, $maxDimension / max($sourceWidth, $sourceHeight));
    $baseWidth = max(1, (int) round($sourceWidth * $scale));
    $baseHeight = max(1, (int) round($sourceHeight * $scale));
    $useWebp = function_exists('imagewebp');
    $extension = $useWebp ? 'webp' : 'jpg';
    $filename = bin2hex(random_bytes(18)) . '.' . $extension;
    $target = $targetDirectory . '/' . $filename;
    $temporaryTarget = $target . '.part';

    try {
        for ($pass = 0; $pass < 5; $pass++) {
            $shrink = 0.84 ** $pass;
            $width = max(1, (int) round($baseWidth * $shrink));
            $height = max(1, (int) round($baseHeight * $shrink));
            $canvas = imagecreatetruecolor($width, $height);
            if (!$canvas) {
                break;
            }
            $white = imagecolorallocate($canvas, 255, 255, 255);
            imagefill($canvas, 0, 0, $white);
            imagecopyresampled($canvas, $sourceImage, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

            for ($quality = 88; $quality >= 46; $quality -= 7) {
                @unlink($temporaryTarget);
                $written = $useWebp
                    ? imagewebp($canvas, $temporaryTarget, $quality)
                    : imagejpeg($canvas, $temporaryTarget, $quality);
                clearstatcache(true, $temporaryTarget);
                if ($written && is_file($temporaryTarget) && filesize($temporaryTarget) <= $maxBytes) {
                    imagedestroy($canvas);
                    if (!rename($temporaryTarget, $target)) {
                        throw new RuntimeException('Foto yang sudah dioptimalkan tidak dapat disimpan.');
                    }
                    return $filename;
                }
            }
            imagedestroy($canvas);
        }
    } finally {
        imagedestroy($sourceImage);
        if (is_file($temporaryTarget)) {
            @unlink($temporaryTarget);
        }
    }

    throw new InvalidArgumentException('Foto tidak dapat diperkecil hingga 500 KB. Coba pilih foto lain.');
}

function delete_image(?string $filename, string $directory): void
{
    if (!$filename || basename($filename) !== $filename) {
        return;
    }
    $path = PUBLIC_ROOT . '/uploads/' . trim($directory, '/') . '/' . $filename;
    if (is_file($path)) {
        unlink($path);
    }
}
