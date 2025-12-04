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
            'national_id' => 'required|string|unique:families,national_id',
            'dob' => 'required|date',
            'phone' => ['required', 'string', 'max:20','regex:/^\+?[0-9\s\-\(\)]{7,20}$/', 'unique:families,phone'],
            'total_members' => 'required|integer|min:1',
            'elderly_count' => 'required|integer|min:0',
            'medical_conditions_count' => 'required|integer|min:0',
            'file' => 'required_unless:medical_conditions_count,0|file|mimes:pdf,jpg,jpeg,png,txt|max:2048',
            'children_count' => 'required|integer|min:0',
            'tent_number' => 'required|string|max:50',
            'location' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ];
    }
}
