<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
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
            'site_name' => 'sometimes|array',
            'site_name.ar' => 'sometimes|string|max:255',
            'site_name.en' => 'sometimes|string|max:255',
            'site_logo' => 'sometimes|image|max:2048',
            'favicon' => 'sometimes|image|max:1024',
            'phone' => 'sometimes|string|max:20',
            'email' => 'sometimes|email|max:255',
            'facebook' => 'sometimes|url',
            'twitter' => 'sometimes|url',
            'instagram' => 'sometimes|url',
            'linkedin' => 'sometimes|url',
            'youtube' => 'sometimes|url',
            'whatsapp' => 'sometimes|string|max:20',
        ];
    }
}
