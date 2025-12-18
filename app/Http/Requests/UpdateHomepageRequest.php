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

            'title' => 'sometimes|array',
            'title.ar' => 'sometimes|string|max:255',
            'title.en' => 'sometimes|string|max:255',

            'description' => 'sometimes|array',
            'description.ar' => 'sometimes|string',
            'description.en' => 'sometimes|string',

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


            'sections' => 'sometimes|array',
            'sections.*.id' => 'required|integer|exists:homepage_sections,id',
            'sections.*.title' => 'sometimes|array',
            'sections.*.title.ar' => 'sometimes|string|max:255',
            'sections.*.title.en' => 'sometimes|string|max:255',
            'sections.*.description' => 'sometimes|array',
            'sections.*.description.ar' => 'sometimes|string',
            'sections.*.description.en' => 'sometimes|string',
            'sections.*.image' => 'sometimes|image|max:2048',


            'complaint_image' => 'sometimes|file|mimes:jpg,jpeg,png,webp|max:10240',
            'contact_image'   => 'sometimes|file|mimes:jpg,jpeg,png,webp|max:10240',


        ];
    }
}
