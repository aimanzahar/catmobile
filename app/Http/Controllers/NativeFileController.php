<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class NativeFileController extends Controller
{
    /**
     * Serve a file from the NativePHP app's cache directory (e.g. an image
     * just picked from the gallery) so the WebView can preview it.
     *
     * The path is verified to live under the configured tempdir to avoid
     * exposing arbitrary files on the device.
     */
    public function show(Request $request): BinaryFileResponse
    {
        $path = (string) $request->query('path', '');
        Log::info('[NativeFileController] preview request', ['path' => $path]);

        $real = $this->resolveSafePath($path);

        return response()->file($real, [
            'Cache-Control' => 'no-store',
        ]);
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
        if ($tempReal === false || ! str_starts_with($real, $tempReal . DIRECTORY_SEPARATOR)) {
            Log::warning('[NativeFileController] path outside tempdir', [
                'path' => $real,
                'tempdir' => $tempReal,
            ]);
            throw new NotFoundHttpException();
        }

        Log::info('[NativeFileController] resolved safe path', ['path' => $real]);

        return $real;
    }
}
