<?php

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SummarySkeletonTest extends TestCase
{
    #[Test]
    public function post_summary_uses_skeleton_while_loading(): void
    {
        $postComponent = file_get_contents(base_path('resources/js/components/Post.vue'));
        $summarySkeletonComponent = file_get_contents(base_path('resources/js/components/partials/ui/SummarySkeleton.vue'));

        $this->assertStringContainsString('<SummarySkeleton />', $postComponent);
        $this->assertStringContainsString('SummarySkeleton', $postComponent);
        $this->assertStringContainsString('animate-pulse', $summarySkeletonComponent);
    }
}
