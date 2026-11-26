<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class WebpImageService
{
    /**
     * MIME types eligible for GD raster-to-WebP conversion.
     *
     * @var list<string>
     */
    private const CONVERTIBLE_MIMES = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/bmp',
        'image/webp',
    ];

    /**
     * Active or executable MIME types that must never be stored on the public disk.
     *
     * @var list<string>
     */
    private const DISALLOWED_MIMES = [
        'image/svg+xml',
        'text/html',
        'application/xhtml+xml',
        'application/javascript',
        'text/javascript',
        'application/x-httpd-php',
        'text/x-php',
        'application/x-php',
    ];

    /**
     * File extensions that must never be stored on the public disk.
     *
     * @var list<string>
     */
    private const DISALLOWED_EXTENSIONS = [
        'svg',
        'svgz',
        'html',
        'htm',
        'xhtml',
        'js',
        'mjs',
        'php',
        'phtml',
        'phar',
        'exe',
        'sh',
        'bat',
    ];

    /**
     * Convert an uploaded raster image to WebP (optionally resizing if wider than $maxWidth)
     * and store it on the target disk. Safe non-image files (e.g. PDFs) are stored as-is;
     * active or executable file types are rejected.
     */
    public function storeUploadedFile(
        TemporaryUploadedFile|UploadedFile $file,
        string $directory,
        string $disk = 'public',
        int $quality = 82,
        int $maxWidth = 1920,
    ): string {
        $directory = trim($directory, '/');
        $mimeType = strtolower((string) $file->getMimeType());
        $extension = strtolower((string) $file->getClientOriginalExtension());

        if (
            in_array($mimeType, self::DISALLOWED_MIMES, true)
            || in_array($extension, self::DISALLOWED_EXTENSIONS, true)
        ) {
            throw new InvalidArgumentException('Uploaded file type is not permitted.');
        }

        if (! function_exists('imagewebp') || ! in_array($mimeType, self::CONVERTIBLE_MIMES, true)) {
            return (string) $file->store($directory, $disk);
        }

        $rawContents = @file_get_contents($file->getRealPath());

        if ($rawContents === false) {
            return (string) $file->store($directory, $disk);
        }

        $image = @imagecreatefromstring($rawContents);

        if ($image === false) {
            return (string) $file->store($directory, $disk);
        }

        $width = imagesx($image);
        $height = imagesy($image);

        if ($maxWidth > 0 && $width > $maxWidth && $height > 0) {
            $newHeight = (int) round(($height / $width) * $maxWidth);
            $resized = imagecreatetruecolor($maxWidth, $newHeight);

            if ($resized !== false) {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                imagefilledrectangle($resized, 0, 0, $maxWidth, $newHeight, $transparent);
                imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $resized;
            }
        }

        if (! imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);

        $tempPath = tempnam(sys_get_temp_dir(), 'webp_');

        if ($tempPath === false) {
            imagedestroy($image);

            return (string) $file->store($directory, $disk);
        }

        $converted = @imagewebp($image, $tempPath, $quality);
        imagedestroy($image);

        if (! $converted || ! is_file($tempPath)) {
            @unlink($tempPath);

            return (string) $file->store($directory, $disk);
        }

        $webpBytes = file_get_contents($tempPath);
        @unlink($tempPath);

        if ($webpBytes === false) {
            return (string) $file->store($directory, $disk);
        }

        $relativePath = ($directory !== '' ? $directory.'/' : '').strtolower((string) Str::ulid()).'.webp';
        Storage::disk($disk)->put($relativePath, $webpBytes);

        return $relativePath;
    }

    /**
     * Convert an existing local image file on disk to WebP and store it on the target disk.
     */
    public function storeLocalFile(
        string $sourceAbsolutePath,
        string $targetRelativePath,
        string $disk = 'public',
        int $quality = 82,
        int $maxWidth = 1920,
    ): ?string {
        if (! is_file($sourceAbsolutePath)) {
            return null;
        }

        if (Storage::disk($disk)->exists($targetRelativePath)) {
            return $targetRelativePath;
        }

        $rawContents = @file_get_contents($sourceAbsolutePath);

        if ($rawContents === false) {
            return null;
        }

        if (! function_exists('imagewebp')) {
            Storage::disk($disk)->put($targetRelativePath, $rawContents);

            return $targetRelativePath;
        }

        $image = @imagecreatefromstring($rawContents);

        if ($image === false) {
            return null;
        }

        $width = imagesx($image);
        $height = imagesy($image);

        if ($maxWidth > 0 && $width > $maxWidth && $height > 0) {
            $newHeight = (int) round(($height / $width) * $maxWidth);
            $resized = imagecreatetruecolor($maxWidth, $newHeight);

            if ($resized !== false) {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                imagefilledrectangle($resized, 0, 0, $maxWidth, $newHeight, $transparent);
                imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $newHeight, $width, $height);
                imagedestroy($image);
                $image = $resized;
            }
        }

        if (! imageistruecolor($image)) {
            imagepalettetotruecolor($image);
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);

        $tempPath = tempnam(sys_get_temp_dir(), 'webp_');

        if ($tempPath === false) {
            imagedestroy($image);

            return null;
        }

        $converted = @imagewebp($image, $tempPath, $quality);
        imagedestroy($image);

        if (! $converted || ! is_file($tempPath)) {
            @unlink($tempPath);

            return null;
        }

        $webpBytes = file_get_contents($tempPath);
        @unlink($tempPath);

        if ($webpBytes === false) {
            return null;
        }

        Storage::disk($disk)->put($targetRelativePath, $webpBytes);

        return $targetRelativePath;
    }
}
