<?php

namespace Tests\Feature;

use App\Http\Controllers\NativeFileController;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class NativeFilePathTest extends TestCase
{
    public function test_only_cache_and_camera_plugin_dirs_are_readable(): void
    {
        // Mirrors Android's app data dir: cache/ (tempdir) and no_backup/ side by side.
        $base = sys_get_temp_dir().DIRECTORY_SEPARATOR.'nativefile-'.uniqid();
        $files = [
            'cache/pick.jpg', 'no_backup/Gallery/gallery_selected_1', 'no_backup/Camera/captured_1.jpg',
            'no_backup/other/secret', 'files/database.sqlite',
        ];
        foreach ($files as $f) {
            @mkdir(dirname("$base/$f"), 0777, true);
            file_put_contents("$base/$f", 'x');
        }
        config(['nativephp-internal.tempdir' => "$base/cache"]);

        try {
            foreach (['cache/pick.jpg', 'no_backup/Gallery/gallery_selected_1', 'no_backup/Camera/captured_1.jpg'] as $ok) {
                $this->assertSame(realpath("$base/$ok"), NativeFileController::resolveSafePath("$base/$ok"));
            }

            foreach (['no_backup/other/secret', 'files/database.sqlite', 'no_backup/Gallery/../other/secret'] as $bad) {
                try {
                    NativeFileController::resolveSafePath("$base/$bad");
                    $this->fail("$bad should be rejected");
                } catch (NotFoundHttpException) {
                    $this->addToAssertionCount(1);
                }
            }
        } finally {
            (new Filesystem)->deleteDirectory($base);
        }
    }
}
