<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CreateSourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for creating a new source.
     *
     * This demonstrates flexible API design:
     * - Only URL and category_id are required
     * - Name and description are optional (will be auto-filled from URL analysis)
     * - URL can be a webpage or direct RSS feed
     */
    public function rules(): array
    {
        return [
            'url' => 'required|url:http,https',
            'category_id' => 'required|integer|exists:categories,id',
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'url.required' => 'A URL is required to create a source.',
            'url.url' => 'Please provide a valid URL (must start with http:// or https://).',
            'category_id.required' => 'A category is required for the source.',
            'category_id.exists' => 'The selected category does not exist.',
            'name.max' => 'Source name cannot exceed 255 characters.',
            'description.max' => 'Description cannot exceed 500 characters.',
        ];
    }
}
