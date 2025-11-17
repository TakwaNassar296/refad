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
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
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
            'id_number' => ['required', 'string', 'max:50', 'unique:users,id_number'],
            'phone' => ['required', 'string', 'max:20', 'unique:users,phone','regex:/^(\+?2)?01[0125][0-9]{8}$/'],
            'backup_phone' => ['nullable', 'string', 'max:20' ,'regex:/^(\+?2)?01[0125][0-9]{8}$/'],
            'role' => ['required', 'in:delegate,contributor'],
            'admin_position' => ['nullable', 'string', 'max:255'],
            'license_number' => ['required_with:admin_position', 'string', 'max:100'],
            'accept_terms' => ['required', 'boolean', 'accepted'],
        ];
    }
}
