<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCampRequest extends FormRequest
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
            'name' => 'required|array',
            'name.ar' => 'required|string|max:255',
            'name.en' => 'required|string|max:255',
            'description' => 'nullable|array',
            'description.ar' => 'nullable|string|max:1000',
            'description.en' => 'nullable|string|max:1000',
            'location' => 'required|string|max:255',
          //  'family_count' => 'sometimes|integer|min:0',
          //  'children_count' => 'sometimes|integer|min:0',
           // 'elderly_count' => 'sometimes|integer|min:0',
           // 'latitude' => 'sometimes|numeric|between:-90,90',
           // 'longitude' => 'sometimes|numeric|between:-180,180',
           // 'bank_account' => 'sometimes|string|max:255',
            'camp_img' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
            'governorate_id' => 'required|exists:governorates,id', 
        ];
    }
}
