<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FeedsConfigTest extends TestCase
{
    #[Test]
    public function feed_sanitizer_preserves_embed_and_fallback_tags(): void
    {
        $feedConfig = config('feeds');
        $strippedTags = $feedConfig['strip_html_tags.tags'] ?? null;

        $this->assertIsArray($strippedTags);
        $this->assertNotContains('iframe', $strippedTags);
        $this->assertNotContains('noscript', $strippedTags);
        $this->assertContains('script', $strippedTags);
        $this->assertContains('style', $strippedTags);
    }
}
