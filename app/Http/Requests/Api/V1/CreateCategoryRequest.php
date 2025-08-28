<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'description' => [
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('categories', 'description')
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'Category description is required.',
            'description.min' => 'Category description must be at least 3 characters.',
            'description.max' => 'Category description cannot exceed 255 characters.',
            'description.unique' => 'A category with this description already exists.',
        ];
    }
}
