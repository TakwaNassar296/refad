<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;
use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'string', 
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(), 
            ],
            'camp_name' => ['required_if:role,delegate', 'nullable', 'string', 'max:255'],
            'id_number' => ['required', 'string', 'max:50', 'unique:users,id_number'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone', 'regex:/^\+?[0-9\s\-\(\)]{7,20}$/'],
            'backup_phone' => ['nullable', 'string', 'max:20' , 'regex:/^\+?[0-9\s\-\(\)]{7,20}$/'],
            'role' => ['required', 'in:delegate,contributor'],
            'admin_position' => ['nullable', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'accept_terms' => ['required', 'boolean', 'accepted'],
        ];
    }
}
