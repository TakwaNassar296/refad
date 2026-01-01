<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
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
            'email' => [
                'required',
                'email',
                Rule::unique('users')->whereNull('deleted_at'),
            ],
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
            'admin_position_id' => [
                'nullable',
                'required_if:role,delegate',
                'exists:admin_positions,id',
            ],
            'id_number' => [
                'required',
                Rule::unique('users')->whereNull('deleted_at'),
            ],
            'phone' => ['required', 'string', 'max:20', Rule::unique('users')->whereNull('deleted_at') , 'regex:/^\+?[0-9\s\-\(\)]{7,20}$/'],
            'backup_phone' => ['nullable', 'string', 'max:20' , 'regex:/^\+?[0-9\s\-\(\)]{7,20}$/'],
            'role' => ['required', 'in:delegate,contributor'],
            'admin_position' => [
                'nullable',
                Rule::in(['مبادر', 'فريق', 'جمعية']),
                'required_if:role,contributor' 
            ],
            'license_number' => [
                'nullable',
                'string',
                'max:100',
                'required_if:admin_position,جمعية' 
            ],
            'accept_terms' => ['required', 'boolean', 'accepted'],
            'camp_id' => 'nullable|exists:camps,id',
        ];
    }

   /* public function withValidator($validator)
    {
        $validator->sometimes('camp_id', 'required|exists:camps,id', function ($input) {
            return isset($input->role) && $input->role === 'delegate';
        });
    }*/
}
