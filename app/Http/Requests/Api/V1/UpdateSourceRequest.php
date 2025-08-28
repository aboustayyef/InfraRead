<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules for updating a source.
     *
     * All fields are optional for updates - only include what you want to change.
     * Updated to allow editing both website URL and RSS feed URL for better admin UX.
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:500',
            'url' => 'sometimes|nullable|url',
            'fetcher_source' => 'sometimes|url',
            'category_id' => 'sometimes|integer|exists:categories,id',
            'active' => 'sometimes|boolean',
            'why_deactivated' => 'sometimes|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'name.max' => 'Source name cannot exceed 255 characters.',
            'description.max' => 'Description cannot exceed 500 characters.',
            'url.url' => 'Website URL must be a valid URL.',
            'fetcher_source.url' => 'RSS feed URL must be a valid URL.',
            'category_id.exists' => 'The selected category does not exist.',
            'active.boolean' => 'Active status must be true or false.',
            'why_deactivated.max' => 'Deactivation reason cannot exceed 500 characters.',
        ];
    }
}
