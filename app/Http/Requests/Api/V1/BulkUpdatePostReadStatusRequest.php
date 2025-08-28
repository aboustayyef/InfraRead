<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class BulkUpdatePostReadStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for bulk operations.
     *
     * This demonstrates Laravel's array validation capabilities:
     * - post_ids must be an array
     * - Each element must be an integer
     * - Array can't be empty (min:1)
     * - Reasonable limit (max:1000) to prevent abuse
     */
    public function rules(): array
    {
        return [
            'post_ids' => 'required|array|min:1|max:1000',
            'post_ids.*' => 'integer|min:1',
            'read' => 'required|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'post_ids.required' => 'Post IDs are required.',
            'post_ids.array' => 'Post IDs must be an array.',
            'post_ids.min' => 'At least one post ID is required.',
            'post_ids.max' => 'Cannot update more than 1000 posts at once.',
            'post_ids.*.integer' => 'Each post ID must be a valid integer.',
            'post_ids.*.min' => 'Post IDs must be positive numbers.',
            'read.required' => 'Read status is required.',
            'read.boolean' => 'Read status must be true or false.'
        ];
    }
}
