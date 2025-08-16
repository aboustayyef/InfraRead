<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Source;
use App\Models\Post;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * MetricsController - API endpoints for system and source metrics
 *
 * This controller provides read-only access to system performance metrics,
 * source health information, and processing statistics. These endpoints are
 * useful for:
 *
 * 1. **Monitoring dashboards**: External tools can track system health
 * 2. **Debugging**: Developers can see which sources are having issues
 * 3. **Performance analysis**: Understanding feed processing patterns
 * 4. **Capacity planning**: Knowing how much work the system is doing
 *
 * Teaching Note: Metrics endpoints are a common pattern in APIs. They provide
 * observability into your system without exposing sensitive data. These are
 * particularly important for RSS aggregators where feed reliability varies
 * significantly across sources.
 */
class MetricsController extends Controller
{
    /**
     * Get detailed metrics for a specific source.
     *
     * This provides comprehensive health and performance data for debugging
     * or monitoring individual RSS sources.
     */
    public function sourceMetrics(Source $source): JsonResponse
    {
        // Get the source with fresh metrics data
        $metrics = $source->getMetrics();

        // Add some additional computed metrics
        $additionalData = [
            'posts_count' => \App\Models\Post::where('source_id', $source->id)->count(),
            'unread_posts_count' => \App\Models\Post::where('source_id', $source->id)->where('read', false)->count(),
            'latest_post_date' => \App\Models\Post::where('source_id', $source->id)->latest('posted_at')->value('posted_at'),
            'is_healthy' => $source->isHealthy(),
            'is_failed' => $source->isFailed(),
        ];

        return response()->json([
            'message' => 'Source metrics retrieved successfully',
            'data' => [
                'source_id' => $source->id,
                'source_name' => $source->name,
                'source_url' => $source->fetcher_source,
                'metrics' => array_merge($metrics, $additionalData)
            ]
        ]);
    }

    /**
     * Get system-wide processing statistics.
     *
     * This provides an overview of the entire RSS processing system,
     * useful for monitoring overall health and performance.
     */
    public function systemStats(): JsonResponse
    {
        // Use caching for expensive queries since this data doesn't change frequently
        $stats = Cache::remember('system_processing_stats', 300, function () {
            return $this->calculateSystemStats();
        });

        return response()->json([
            'message' => 'System processing statistics retrieved successfully',
            'data' => $stats
        ]);
    }

    /**
     * Get health summary for all sources.
     *
     * This provides a quick overview of which sources are healthy,
     * failing, or need attention.
     */
    public function sourcesHealth(): JsonResponse
    {
        // Get source health counts using the scopes we defined
        $healthCounts = [
            'total' => Source::count(),
            'active' => Source::where('active', true)->count(),
            'inactive' => Source::where('active', false)->count(),
            'healthy' => Source::healthy()->count(),
            'warning' => Source::where('status', Source::STATUS_WARNING)->count(),
            'failed' => Source::failed()->count(),
        ];

        // Get sources that need attention (failing or failed)
        $problematicSources = Source::where('status', '!=', Source::STATUS_ACTIVE)
            ->where('active', true)
            ->select(['id', 'name', 'status', 'consecutive_failures', 'last_error_at', 'last_error_message'])
            ->limit(10) // Limit to prevent huge responses
            ->get()
            ->map(function ($source) {
                return [
                    'id' => $source->id,
                    'name' => $source->name,
                    'status' => $source->status,
                    'consecutive_failures' => $source->consecutive_failures,
                    'last_error_at' => $source->last_error_at,
                    'last_error_message' => $source->last_error_message,
                    'status_description' => $source->getStatusDescription()
                ];
            });

        return response()->json([
            'message' => 'Sources health summary retrieved successfully',
            'data' => [
                'summary' => $healthCounts,
                'problematic_sources' => $problematicSources,
                'generated_at' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Get recent processing activity.
     *
     * This shows recent feed processing activity for monitoring
     * and debugging purposes.
     */
    public function recentActivity(): JsonResponse
    {
        // Get sources that have been updated recently
        $recentlyUpdated = Source::where('last_fetched_at', '>', now()->subHours(24))
            ->orderBy('last_fetched_at', 'desc')
            ->limit(20)
            ->select([
                'id', 'name', 'last_fetched_at', 'last_fetch_duration_ms',
                'status', 'consecutive_failures'
            ])
            ->get()
            ->map(function ($source) {
                return [
                    'source_id' => $source->id,
                    'source_name' => $source->name,
                    'last_fetched_at' => $source->last_fetched_at,
                    'duration_ms' => $source->last_fetch_duration_ms,
                    'status' => $source->status,
                    'consecutive_failures' => $source->consecutive_failures
                ];
            });

        // Get recent posts (last 24 hours)
        $recentPostsCount = Post::where('created_at', '>', now()->subHours(24))->count();

        return response()->json([
            'message' => 'Recent processing activity retrieved successfully',
            'data' => [
                'recent_posts_count' => $recentPostsCount,
                'recently_updated_sources' => $recentlyUpdated,
                'time_range' => 'Last 24 hours',
                'generated_at' => now()->toISOString()
            ]
        ]);
    }

    /**
     * Calculate comprehensive system statistics.
     *
     * This is a private method that does the heavy lifting for system stats.
     * It's cached to avoid expensive queries on every request.
     */
    private function calculateSystemStats(): array
    {
        // Source statistics
        $sourceStats = [
            'total_sources' => Source::count(),
            'active_sources' => Source::where('active', true)->count(),
            'healthy_sources' => Source::healthy()->count(),
            'warning_sources' => Source::where('status', Source::STATUS_WARNING)->count(),
            'failed_sources' => Source::failed()->count(),
        ];

        // Post statistics
        $postStats = [
            'total_posts' => Post::count(),
            'unread_posts' => Post::where('read', false)->count(),
            'posts_today' => Post::whereDate('created_at', today())->count(),
            'posts_this_week' => Post::where('created_at', '>', now()->subWeek())->count(),
            'posts_this_month' => Post::where('created_at', '>', now()->subMonth())->count(),
        ];

        // Category statistics
        $categoryStats = [
            'total_categories' => Category::count(),
            'categories_with_sources' => Category::has('sources')->count(),
        ];

        // Performance statistics (last 24 hours)
        $performanceStats = [
            'sources_updated_today' => Source::whereDate('last_fetched_at', today())->count(),
            'average_fetch_duration_ms' => (int) Source::where('last_fetched_at', '>', now()->subDay())
                ->whereNotNull('last_fetch_duration_ms')
                ->avg('last_fetch_duration_ms'),
            'fastest_source_ms' => (int) Source::where('last_fetched_at', '>', now()->subDay())
                ->whereNotNull('last_fetch_duration_ms')
                ->min('last_fetch_duration_ms'),
            'slowest_source_ms' => (int) Source::where('last_fetched_at', '>', now()->subDay())
                ->whereNotNull('last_fetch_duration_ms')
                ->max('last_fetch_duration_ms'),
        ];

        // Error statistics
        $errorStats = [
            'sources_with_errors' => Source::where('consecutive_failures', '>', 0)->count(),
            'total_consecutive_failures' => (int) Source::sum('consecutive_failures'),
            'sources_in_backoff' => Source::where('consecutive_failures', '>', 0)
                ->where('last_error_at', '>', now()->subDay())
                ->count(),
        ];

        return [
            'sources' => $sourceStats,
            'posts' => $postStats,
            'categories' => $categoryStats,
            'performance' => $performanceStats,
            'errors' => $errorStats,
            'generated_at' => now()->toISOString(),
            'cache_duration' => '5 minutes'
        ];
    }
}
