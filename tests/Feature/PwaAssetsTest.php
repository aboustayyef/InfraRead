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
}
