<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class BrowserCacheHeadersTest extends TestCase
{
    public function test_api_responses_disable_browser_cache(): void
    {
        $response = $this->getJson('/api/test');

        $response->assertOk();
        $this->assertCacheControlContains($response->headers->get('Cache-Control'), [
            'no-store',
            'no-cache',
            'must-revalidate',
            'max-age=0',
        ]);
        $response->assertHeader('Pragma', 'no-cache');
        $response->assertHeader('Expires', '0');
    }

    public function test_web_responses_can_still_be_cacheable(): void
    {
        Route::middleware('web')->get('/_cache-test/web-cacheable', function () {
            return response('console.log("ok");', 200, [
                'Cache-Control' => 'public, max-age=31536000',
                'Content-Type' => 'application/javascript',
            ]);
        });

        $response = $this->get('/_cache-test/web-cacheable');

        $response->assertOk();
        $this->assertCacheControlContains($response->headers->get('Cache-Control'), [
            'public',
            'max-age=31536000',
        ]);
        $this->assertStringNotContainsString('no-store', $response->headers->get('Cache-Control', ''));
    }

    public function test_image_responses_keep_existing_cache_headers(): void
    {
        Route::middleware('web')->get('/_cache-test/image', function () {
            return response('image', 200, [
                'Cache-Control' => 'public, max-age=3600',
                'Content-Type' => 'image/png',
            ]);
        });

        $response = $this->get('/_cache-test/image');

        $response->assertOk();
        $this->assertCacheControlContains($response->headers->get('Cache-Control'), [
            'public',
            'max-age=3600',
        ]);
        $this->assertStringNotContainsString('no-store', $response->headers->get('Cache-Control', ''));
    }

    protected function assertCacheControlContains(?string $cacheControlHeader, array $expectedDirectives): void
    {
        $this->assertNotNull($cacheControlHeader);

        $directives = array_map('trim', explode(',', strtolower($cacheControlHeader)));

        foreach ($expectedDirectives as $expectedDirective) {
            $this->assertContains(strtolower($expectedDirective), $directives);
        }
    }
}
