<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettingRequest extends FormRequest
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
            'site_name' => 'required|array',
            'site_name.ar' => 'required|string|max:255',
            'site_name.en' => 'required|string|max:255',
            'site_logo' => 'nullable|image|max:2048',
            'favicon' => 'nullable|image|max:1024',
            'phone' =>  ['nullable', 'string', 'max:20', 'regex:/^\+?\d{7,15}$/'],
            'email' => 'nullable|email|max:255',
            'facebook' => 'nullable|url',
            'twitter' => 'nullable|url',
            'instagram' => 'nullable|url',
            'linkedin' => 'nullable|url',
            'youtube' => 'nullable|url',
            'whatsapp' => 'nullable|string|max:20',

        ];
    }
}
