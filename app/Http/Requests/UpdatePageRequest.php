<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|array',
            'title.ar' => 'sometimes|string|max:255',
            'title.en' => 'sometimes|string|max:255',
            'description' => 'sometimes|array',
            'description.ar' => 'sometimes|string',
            'description.en' => 'sometimes|string',
            'image' => 'nullable|image|max:2048',
        ];
    }
}
