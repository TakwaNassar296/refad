<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProjectRequest extends FormRequest
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
            'type' => 'sometimes|string|max:255',
            'beneficiary_count' => 'sometimes|integer|min:0',
            'college' => 'sometimes|string|max:255',
            'status' => 'nullable|in:pending,in_progress,delivered,cancelled',
            'notes' => 'nullable|string|max:1000',
            'project_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:10240',
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
