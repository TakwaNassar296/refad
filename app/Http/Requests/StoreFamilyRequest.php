<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFamilyRequest extends FormRequest
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
            'family_name' => 'required|string|max:255',
            'father_name' => 'required|string|max:255',
            'national_id' => 'required|string|unique:families,national_id',
            'dob' => 'required|date',
            'phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9]{7,20}$/', 'unique:families,phone'],
            'email' => ['nullable', 'email', 'max:255', 'unique:families,email'],
            'total_members' => 'required|integer|min:1',
            'elderly_count' => 'required|integer|min:0',
            'medical_conditions_count' => 'required|integer|min:0',
            'children_count' => 'required|integer|min:0',
            'tent_number' => 'required|string|max:50',
            'location' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
