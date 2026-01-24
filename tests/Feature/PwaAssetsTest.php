<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\File;
use Tests\TestCase;

class PwaAssetsTest extends TestCase
{
    public function test_manifest_is_present_and_valid(): void
    {
        $manifestPath = public_path('manifest.webmanifest');

        $this->assertFileExists($manifestPath);

        $manifest = json_decode(File::get($manifestPath), true);

        $this->assertIsArray($manifest);
        $this->assertSame('InfraRead', $manifest['name'] ?? null);
        $this->assertSame('/', $manifest['start_url'] ?? null);
        $this->assertNotEmpty($manifest['icons'] ?? []);

        foreach ($manifest['icons'] as $icon) {
            if (!isset($icon['src'])) {
                continue;
            }

            $this->assertFileExists(public_path(ltrim($icon['src'], '/')));
        }
    }

    public function test_service_worker_is_present(): void
    {
        $serviceWorkerPath = public_path('service-worker.js');

        $this->assertFileExists($serviceWorkerPath);
        $this->assertStringContainsString('self.addEventListener', File::get($serviceWorkerPath));
    }

    public function test_ios_startup_images_exist(): void
    {
        $sizes = [
            '640x1136',
            '750x1334',
            '828x1792',
            '1125x2436',
            '1170x2532',
            '1179x2556',
            '1242x2208',
            '1242x2688',
            '1284x2778',
            '1290x2796',
            '1536x2048',
            '1668x2224',
            '1668x2388',
            '2048x2732',
        ];

        foreach ($sizes as $size) {
            $this->assertFileExists(public_path("img/ios-splash/ios-splash-{$size}.png"));
        }
    }
}
