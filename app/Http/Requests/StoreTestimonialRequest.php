<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestimonialRequest extends FormRequest
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
            'user_name' => 'required|string|max:255',
            'user_image' => 'required|image|mimes:jpg,jpeg,png,gif,svg,webp|max:2048',
            'opinion' => 'required|array',
            'opinion.ar' => 'required|string|max:255',
            'opinion.en' => 'required|string|max:255',
            'order' => 'nullable|integer',
        ];
    }
}
