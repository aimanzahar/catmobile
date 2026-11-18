<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NativeFileController extends Controller
{
    /**
     * Preview of an image just picked from the gallery, as a base64 data URL.
     *
     * Raw bytes can't be returned: the NativePHP bridge passes every response
     * body to Java as a UTF-8 string, and binary data aborts the app (JNI
     * NewStringUTF). The path must live in one of the allowed app dirs.
     */
    public function show(Request $request): JsonResponse
    {
        $real = self::resolveSafePath((string) $request->query('path', ''));
        $mime = @mime_content_type($real) ?: 'application/octet-stream';

        return response()->json([
            'data_url' => 'data:'.$mime.';base64,'.base64_encode((string) file_get_contents($real)),
        ], 200, ['Cache-Control' => 'no-store']);
    }

    public static function resolveSafePath(string $path): string
    {
        if ($path === '') {
            Log::warning('[NativeFileController] empty path');
            throw new NotFoundHttpException();
        }

        $real = realpath($path);
        if ($real === false || ! is_file($real)) {
            Log::warning('[NativeFileController] file does not exist', ['path' => $path]);
            throw new NotFoundHttpException();
        }

        $tempDir = (string) config('nativephp-internal.tempdir', '');
        if ($tempDir === '') {
            Log::warning('[NativeFileController] tempdir not configured; refusing to serve', ['path' => $real]);
            throw new NotFoundHttpException();
        }

        $tempReal = realpath($tempDir);
        if ($tempReal === false) {
            throw new NotFoundHttpException();
        }

        // The camera plugin saves picks/captures under the app's noBackupFilesDir,
        // a sibling of the cache dir (tempdir) inside the private app data dir.
        $noBackup = dirname($tempReal).DIRECTORY_SEPARATOR.'no_backup'.DIRECTORY_SEPARATOR;
        $allowedRoots = [$tempReal.DIRECTORY_SEPARATOR, $noBackup.'Gallery'.DIRECTORY_SEPARATOR, $noBackup.'Camera'.DIRECTORY_SEPARATOR];

        $allowed = array_filter($allowedRoots, fn (string $root) => str_starts_with($real, $root));
        if ($allowed === []) {
            Log::warning('[NativeFileController] path outside allowed dirs', [
                'path' => $real,
                'tempdir' => $tempReal,
            ]);
            throw new NotFoundHttpException();
        }

        Log::info('[NativeFileController] resolved safe path', ['path' => $real]);

        return $real;
    }
}
