<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCampRequest extends FormRequest
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
            'name' => 'sometimes|array',
            'name.ar' => 'sometimes|string|max:255',
            'name.en' => 'sometimes|string|max:255',
            'description' => 'nullable|array',
            'description.ar' => 'nullable|string|max:1000',
            'description.en' => 'nullable|string|max:1000',
            'family_count' => 'sometimes|integer|min:0',
            'children_count' => 'sometimes|integer|min:0',
            'elderly_count' => 'sometimes|integer|min:0',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'bank_account' => 'sometimes|string|max:255',
            'location' => 'nullable|string|max:255',
            'camp_img' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'governorate_id' => 'sometimes|exists:governorates,id',
        ];
    }
}
