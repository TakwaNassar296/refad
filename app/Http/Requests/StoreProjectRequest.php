<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProjectRequest extends FormRequest
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
            'type' => 'required|string|max:255',
            'beneficiary_count' => 'required|integer|min:0',
            'college' => 'required|string|max:255',
            'project_number' => 'required|string|max:100',
            'status' => 'nullable|in:pending,in_progress,delivered,cancelled',
            'notes' => 'nullable|string|max:1000',
            'file' => 'nullable|file|max:10240',
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator, response()->json([
            'success' => false,
            'message' => __('messages.validation_failed'),
            'errors' => $validator->errors(),
        ], 422));
    }

}
