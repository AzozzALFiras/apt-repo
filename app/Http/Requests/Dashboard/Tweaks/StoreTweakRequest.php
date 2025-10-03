<?php

namespace App\Http\Requests\Dashboard\Tweaks;

use Illuminate\Foundation\Http\FormRequest;

class StoreTweakRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:deb',
                'max:10240', // 10MB max
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'Please select a .deb file to upload.',
            'file.file' => 'The uploaded file is not valid.',
            'file.mimes' => 'Only .deb files are allowed.',
            'file.max' => 'The file size must not exceed 10MB.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'file' => 'tweak file',
        ];
    }
}
