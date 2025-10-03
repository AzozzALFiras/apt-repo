<?php

namespace App\Http\Requests\Dashboard\Tweaks;

use Illuminate\Foundation\Http\FormRequest;

class ChangeLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'version' => ['required', 'string', 'max:50'],
            'changelog' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'version.required' => 'The version field is required.',
            'version.max' => 'The version must not exceed 50 characters.',
            'changelog.required' => 'The changelog field is required.',
        ];
    }
}
