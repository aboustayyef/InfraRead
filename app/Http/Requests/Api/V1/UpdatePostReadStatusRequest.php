<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePostReadStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * For this endpoint, we rely on the auth:sanctum middleware
     * to handle authentication. Any authenticated user can mark
     * their own posts as read/unread.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * We only accept a boolean 'read' field.
     * Laravel will automatically return a 422 response
     * with validation errors if this fails.
     */
    public function rules(): array
    {
        return [
            'read' => 'required|boolean'
        ];
    }

    /**
     * Custom error messages for validation failures.
     *
     * This provides more user-friendly error messages
     * than Laravel's defaults.
     */
    public function messages(): array
    {
        return [
            'read.required' => 'The read status is required.',
            'read.boolean' => 'The read status must be true or false.'
        ];
    }
}
