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


            'members' => 'sometimes|array',
            'members.*.id' => 'sometimes|exists:family_members,id',
            'members.*.name' => 'sometimes|required|string|max:255',
            'members.*.gender' => 'sometimes|required|in:male,female',
            'members.*.dob' => 'sometimes|required|date',
            'members.*.relationship_id' => 'sometimes|required|exists:relationships,id',
            'members.*.medical_condition_id' => 'nullable|exists:medical_conditions,id',
            'members.*.file' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,zip,txt,jpg,jpeg,png|max:10240',
            'members.*.national_id' => [
                'sometimes',
                'required',
                'string',
                'distinct',
                function ($attribute, $value, $fail) {
                    $index = explode('.', $attribute)[1]; 
                    $memberId = $this->input("members.$index.id");

                    $exists = \App\Models\FamilyMember::where('national_id', $value)
                        ->when($memberId, fn($q) => $q->where('id', '!=', $memberId))
                        ->exists();

                    if ($exists) {
                        $fail("رقم الهوية {$value} مستخدم بالفعل.");
                    }
                }
            ],
        ];
    }
}