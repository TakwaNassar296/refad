<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTestimonialRequest extends FormRequest
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
            'user_name' => 'sometimes|required|string|max:255',
            'user_image' => 'sometimes|image|mimes:jpg,jpeg,png,gif,svg|max:2048',
            'opinion' => 'sometimes|required|array',
            'opinion.ar' => 'sometimes|string|max:255',
            'opinion.en' => 'sometimes|string|max:255',
            'order' => 'sometimes|integer',
        ];
    }
}
