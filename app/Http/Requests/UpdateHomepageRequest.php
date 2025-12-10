<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHomepageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'slides' => 'sometimes|array',
            'slides.*.id' => 'required|integer|exists:homepage_slides,id',
            'slides.*.hero_title' => 'sometimes|array',
            'slides.*.hero_title.ar' => 'sometimes|string|max:255',
            'slides.*.hero_title.en' => 'sometimes|string|max:255',
            'slides.*.hero_description' => 'sometimes|array',
            'slides.*.hero_description.ar' => 'sometimes|string',
            'slides.*.hero_description.en' => 'sometimes|string',
            'slides.*.hero_subtitle' => 'sometimes|array',
            'slides.*.hero_subtitle.ar' => 'sometimes|string',
            'slides.*.hero_subtitle.en' => 'sometimes|string',
            'slides.*.hero_image' => 'sometimes|image|max:2048',
            'slides.*.small_hero_image' => 'sometimes|image|max:2048',
        ];
    }
}
