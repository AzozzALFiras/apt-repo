<?php

namespace App\Http\Requests\Dashboard\Tweaks;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTweakRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'file' => 'nullable|file|mimes:deb|max:102400', // 100MB max
            'name' => 'required|string|max:255',
            'version' => 'required|string|max:50',
            'description' => 'nullable|string|max:1000',
            'author' => 'nullable|string|max:255',
            'maintainer' => 'nullable|string|max:255',
            'section' => 'required|string|max:100',
            'homepage' => 'nullable|url|max:500',
            'changelog' => 'nullable|string|max:5000',
            'force_changelog' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'file.mimes' => 'The file must be a .deb package file.',
            'file.max' => 'The file size must not exceed 100MB.',
            'name.required' => 'The tweak name is required.',
            'version.required' => 'The version is required.',
            'section.required' => 'The section is required.',
            'homepage.url' => 'The homepage must be a valid URL.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'file' => 'deb file',
            'name' => 'tweak name',
            'version' => 'version',
            'description' => 'description',
            'author' => 'author',
            'maintainer' => 'maintainer',
            'section' => 'section',
            'homepage' => 'homepage URL',
            'changelog' => 'changelog',
        ];
    }
}
