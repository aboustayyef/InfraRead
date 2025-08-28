<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ImportOpmlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'opml' => [
                'required',
                'file',
                'mimes:opml,xml',
                'max:1024', // 1MB max
            ],
            'mode' => [
                'sometimes',
                Rule::in(['replace', 'merge'])
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'opml.required' => 'Please select an OPML file to import.',
            'opml.file' => 'The uploaded file is not valid.',
            'opml.mimes' => 'Only OPML and XML files are allowed.',
            'opml.max' => 'The OPML file must not exceed 1MB.',
            'mode.in' => 'Import mode must be either "replace" or "merge".'
        ];
    }
}
