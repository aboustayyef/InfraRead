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
     * Note: We don't allow changing the RSS feed URL directly -
     * that would require re-validation and is better handled as delete + create.
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:500',
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
            'category_id.exists' => 'The selected category does not exist.',
            'active.boolean' => 'Active status must be true or false.',
            'why_deactivated.max' => 'Deactivation reason cannot exceed 500 characters.',
        ];
    }
}
