<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class AdminCreateUserRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|regex:/^\+?[0-9\s\-\(\)]{7,20}$/',
            'backup_phone' => ['nullable', 'string',  'regex:/^\+?[0-9\s\-\(\)]{7,20}$/'],
            'id_number' => 'required|string|max:50|unique:users,id_number',
            'role' => 'required|in:delegate,contributor',
            'password' => 'required|string|confirmed|min:8',
            'admin_position_id' => [
                'nullable',
                'exists:admin_positions,id',
                'required_if:role,delegate',
            ],
            'admin_position' => [
                'nullable',
                'string',
                'max:255',
                Rule::in(['مبادر', 'فريق', 'جمعية']),
                'required_if:role,contributor'
            ],

            'license_number' => 'required_if:admin_position,جمعية|string|max:100',
        ];
    }

    public function withValidator($validator)
    {
        $validator->sometimes('camp_id', 'required|exists:camps,id', function ($input) {
            return isset($input->role) && $input->role === 'delegate';
        });
    }
}
