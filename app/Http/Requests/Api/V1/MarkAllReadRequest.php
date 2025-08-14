<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class MarkAllReadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for mark-all operations.
     *
     * This endpoint supports optional filtering:
     * - No filters = mark ALL posts
     * - source_id = mark all posts in a specific source
     * - category_id = mark all posts in a specific category
     * - posted_before = mark all posts posted before a timestamp
     *
     * These can be combined (e.g., source + date range)
     */
    public function rules(): array
    {
        return [
            'read' => 'required|boolean',
            'source_id' => 'sometimes|integer|exists:sources,id',
            'category_id' => 'sometimes|integer|exists:categories,id',
            'posted_before' => 'sometimes|date_format:Y-m-d H:i:s'
        ];
    }

    public function messages(): array
    {
        return [
            'read.required' => 'Read status is required.',
            'read.boolean' => 'Read status must be true or false.',
            'source_id.integer' => 'Source ID must be a valid integer.',
            'source_id.exists' => 'The specified source does not exist.',
            'category_id.integer' => 'Category ID must be a valid integer.',
            'category_id.exists' => 'The specified category does not exist.',
            'posted_before.date_format' => 'Posted before must be in Y-m-d H:i:s format.',
        ];
    }
}
