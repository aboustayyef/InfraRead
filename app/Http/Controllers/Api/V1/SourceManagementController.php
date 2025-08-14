<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\CreateSourceRequest;
use App\Http\Requests\Api\V1\UpdateSourceRequest;
use App\Http\Resources\SourceResource;
use App\Models\Source;
use App\UrlAnalyzer;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class SourceManagementController extends Controller
{
    /**
     * Create a new source with automatic feed discovery.
     *
     * This endpoint demonstrates several advanced concepts:
     * 1. Automatic feed discovery from webpage URLs
     * 2. Feed validation before storage
     * 3. Database transactions for data integrity
     * 4. Integration of existing services (UrlAnalyzer)
     * 5. Comprehensive error handling
     */
    public function store(CreateSourceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $url = $validated['url'];

        // Step 1: Use our existing UrlAnalyzer for feed discovery
        $analyzer = new UrlAnalyzer($url);

        if ($analyzer->status === 'error') {
            return response()->json([
                'message' => 'Failed to analyze URL',
                'errors' => [
                    'url' => $analyzer->error_messages
                ]
            ], 422);
        }

        // Step 2: Validate that we found a usable RSS feed
        if (!$analyzer->hasRssFeed()) {
            return response()->json([
                'message' => 'No RSS feed found',
                'errors' => [
                    'url' => ['No RSS or Atom feed could be discovered at this URL']
                ]
            ], 422);
        }

        // Step 3: Check for duplicate sources
        $rssUrl = $analyzer->getRssFeed();
        $existingSource = Source::where('fetcher_source', $rssUrl)->first();
        if ($existingSource) {
            return response()->json([
                'message' => 'Source already exists',
                'errors' => [
                    'url' => ['A source with this RSS feed already exists: ' . $existingSource->name]
                ]
            ], 422);
        }

        // Step 4: Create the source in a transaction
        DB::beginTransaction();

        try {
            $metadata = $analyzer->getMetadata();

            $source = Source::create([
                'name' => $validated['name'] ?? $metadata['title'],
                'description' => $validated['description'] ?? $metadata['description'],
                'url' => $metadata['canonical_url'],
                'author' => $metadata['author'],
                'fetcher_kind' => 'rss',  // Currently only RSS supported
                'fetcher_source' => $rssUrl,
                'category_id' => $validated['category_id'],
                'active' => true,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Source created successfully',
                'data' => new SourceResource($source->load('category'))
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Failed to create source',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Update an existing source.
     */
    public function update(UpdateSourceRequest $request, Source $source): JsonResponse
    {
        $validated = $request->validated();

        $source->update($validated);

        return response()->json([
            'message' => 'Source updated successfully',
            'data' => new SourceResource($source->fresh('category'))
        ]);
    }

    /**
     * Delete a source.
     *
     * This will also handle cleanup of related posts if needed.
     */
    public function destroy(Source $source): JsonResponse
    {
        // Optional: Add soft delete or archive instead of hard delete
        $sourceName = $source->name;

        DB::beginTransaction();

        try {
            // Note: Posts will remain due to potential historical value
            // You might want to add a 'source_deleted' flag to posts instead
            $source->delete();

            DB::commit();

            return response()->json([
                'message' => "Source '{$sourceName}' deleted successfully"
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Failed to delete source',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }

    /**
     * Manually refresh a source's posts.
     *
     * This triggers an immediate fetch of new posts from the RSS feed.
     */
    public function refresh(Source $source): JsonResponse
    {
        try {
            // Use the existing updatePosts method from your Source model
            $result = $source->updatePosts();

            return response()->json([
                'message' => 'Source refreshed successfully',
                'data' => [
                    'source_id' => $source->id,
                    'result' => $result
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to refresh source',
                'error' => 'Could not fetch new posts from RSS feed'
            ], 500);
        }
    }
}
