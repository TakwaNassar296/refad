<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFamilyRequest extends FormRequest
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
            'family_name' => 'sometimes|required|string|max:255',
            'national_id' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('families','national_id')->ignore($this->family)
            ],
            'dob' => 'sometimes|date',
            'phone' => ['sometimes', 'string', 'max:20',  'regex:/^\+?[0-9\s\-\(\)]{7,20}$/', Rule::unique('families', 'phone')->ignore($this->family)],
            'backup_phone' => ['nullable', 'string', 'max:20','regex:/^\+?[0-9\s\-\(\)]{7,20}$/'],
            'total_members' => 'sometimes|integer|min:1',
            'file' => 'sometimes|file|mimes:pdf,jpg,jpeg,png,txt|max:2048',
            'tent_number' => 'sometimes|string|max:50',
            'location' => 'sometimes|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'marital_status_id' => ['sometimes','required','integer', Rule::exists('marital_statuses','id')],
        ];
    }
}
