<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\MarkAllReadRequest;
use App\Models\Post;
use Illuminate\Http\JsonResponse;

class MarkAllReadController extends Controller
{
    /**
     * Mark posts as read using efficient bulk operations.
     *
     * This controller demonstrates Laravel's efficient query builder
     * methods for bulk updates without loading models into memory.
     * Much more performant than the array-based bulk approach.
     */
    public function markAll(MarkAllReadRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $readStatus = $validated['read'];

        // Build the base query - only unread posts if marking as read,
        // only read posts if marking as unread (for efficiency)
        $query = Post::query();

        if ($readStatus) {
            // Only mark currently unread posts as read
            $query->where('read', false);
        } else {
            // Only mark currently read posts as unread
            $query->where('read', true);
        }

        // Apply filters if provided
        if (isset($validated['source_id'])) {
            $query->where('source_id', $validated['source_id']);
        }

        if (isset($validated['category_id'])) {
            $query->where('category_id', $validated['category_id']);
        }

        if (isset($validated['posted_before'])) {
            $query->where('posted_at', '<=', $validated['posted_before']);
        }

        // Perform the efficient bulk update
        $updatedCount = $query->update(['read' => $readStatus]);

        $action = $readStatus ? 'read' : 'unread';

        return response()->json([
            'message' => "Successfully marked {$updatedCount} posts as {$action}",
            'data' => [
                'updated_count' => $updatedCount,
                'read_status' => $readStatus,
                'filters_applied' => array_filter([
                    'source_id' => $validated['source_id'] ?? null,
                    'category_id' => $validated['category_id'] ?? null,
                    'posted_before' => $validated['posted_before'] ?? null,
                ])
            ]
        ]);
    }
}
