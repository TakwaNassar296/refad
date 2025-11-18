<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateFamilyMemberRequest extends FormRequest
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
            'name' => 'sometimes|required|string|max:255',
            'gender' => 'sometimes|required|in:male,female',
            'dob' => 'sometimes|date',
            'national_id' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('family_members' , 'national_id')->ignore($this->family_member)
            ]
        ];
    }
}
