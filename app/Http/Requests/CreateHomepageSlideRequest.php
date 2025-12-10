<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateHomepageSlideRequest extends FormRequest
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
            'hero_title' => 'required|array',
            'hero_title.ar' => 'required|string|max:255',
            'hero_title.en' => 'required|string|max:255',
            'hero_description' => 'required|array',
            'hero_description.ar' => 'required|string',
            'hero_description.en' => 'required|string',
            'hero_subtitle' => 'sometimes|array',
            'hero_subtitle.ar' => 'sometimes|string',
            'hero_subtitle.en' => 'sometimes|string',
            'hero_image' => 'sometimes|image|max:2048',
            'small_hero_image' => 'sometimes|image|max:2048',
        ];
    }
}
