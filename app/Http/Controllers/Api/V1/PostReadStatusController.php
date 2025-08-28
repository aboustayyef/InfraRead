<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\UpdatePostReadStatusRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;

class PostReadStatusController extends Controller
{
    /**
     * Update the read status of a post.
     *
     * This endpoint allows marking a post as read or unread.
     * It's designed to be idempotent - calling it multiple times
     * with the same data should have the same result.
     */
    public function update(UpdatePostReadStatusRequest $request, Post $post): JsonResponse
    {
        // Extract the validated read status from the request
        $readStatus = $request->validated()['read'];

        // Update the post's read status
        $post->update(['read' => $readStatus]);

        // Return the updated post as a JSON resource
        return response()->json([
            'message' => 'Post read status updated successfully',
            'data' => new PostResource($post->fresh())
        ]);
    }
}
