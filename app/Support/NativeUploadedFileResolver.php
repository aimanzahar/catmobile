<?php

namespace App\Support;

use App\Http\Controllers\NativeFileController;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NativeUploadedFileResolver
{
    /**
     * Read a path posted by the NativePHP image picker (a file inside the
     * app's cache dir) and wrap it in an UploadedFile that Laravel/PocketBase
     * can treat exactly like a multipart upload.
     */
    public static function resolveFromRequest(Request $request, string $key): ?UploadedFile
    {
        $path = trim((string) $request->input($key, ''));
        if ($path === '') {
            return null;
        }

        Log::info('[NativeUploadedFileResolver] resolving native picker path', [
            'key' => $key,
            'path' => $path,
        ]);

        try {
            $real = NativeFileController::resolveSafePath($path);
        } catch (NotFoundHttpException $e) {
            Log::warning('[NativeUploadedFileResolver] rejected unsafe path', [
                'key' => $key,
                'path' => $path,
            ]);
            return null;
        }

        $mime = self::detectMime($real);
        $filename = basename($real);
        if (! str_contains($filename, '.')) {
            $filename .= '.' . self::extensionFromMime($mime);
        }

        Log::info('[NativeUploadedFileResolver] adopted file', [
            'real' => $real,
            'mime' => $mime,
            'name' => $filename,
            'size' => @filesize($real),
        ]);

        return new UploadedFile($real, $filename, $mime, null, true);
    }

    private static function detectMime(string $path): string
    {
        if (function_exists('mime_content_type')) {
            $mime = @mime_content_type($path);
            if (is_string($mime) && $mime !== '') {
                return $mime;
            }
        }

        return 'application/octet-stream';
    }

    private static function extensionFromMime(string $mime): string
    {
        return match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
            default => 'bin',
        };
    }
}
