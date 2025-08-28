<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\BulkUpdatePostReadStatusRequest;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BulkPostReadStatusController extends Controller
{
    /**
     * Bulk update read status for multiple posts.
     *
     * This endpoint demonstrates several advanced Laravel concepts:
     * 1. Database transactions for atomicity
     * 2. Bulk operations for performance
     * 3. Validation of arrays of data
     * 4. Error handling for partial failures
     */
    public function update(BulkUpdatePostReadStatusRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $postIds = $validated['post_ids'];
        $readStatus = $validated['read'];

        // Start a database transaction
        // This ensures either ALL posts are updated or NONE are
        DB::beginTransaction();

        try {
            // First, verify all posts exist
            // Using whereIn for efficient single query
            $existingPosts = Post::whereIn('id', $postIds)->pluck('id')->toArray();

            // Check if any posts are missing
            $missingIds = array_diff($postIds, $existingPosts);
            if (!empty($missingIds)) {
                return response()->json([
                    'message' => 'Some posts were not found',
                    'errors' => [
                        'post_ids' => ['Posts not found: ' . implode(', ', $missingIds)]
                    ]
                ], 422);
            }

            // Perform the bulk update
            $updatedCount = Post::whereIn('id', $postIds)
                ->update(['read' => $readStatus]);

            // Commit the transaction
            DB::commit();

            return response()->json([
                'message' => "Successfully updated {$updatedCount} posts",
                'data' => [
                    'updated_count' => $updatedCount,
                    'read_status' => $readStatus
                ]
            ]);

        } catch (\Exception $e) {
            // If anything goes wrong, rollback all changes
            DB::rollback();

            return response()->json([
                'message' => 'Failed to update posts',
                'error' => 'An unexpected error occurred'
            ], 500);
        }
    }
}
