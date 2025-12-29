<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FamilyMemberResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'gender' => $this->gender,
            'dob' => $this->dob?->format('Y-m-d'),
            'nationalId' => $this->national_id,
            'relationship' => $this->relationship?->name,
            'medicalCondition' => $this->medicalCondition ? $this->medicalCondition->name : null,
           // 'otherMedicalCondition' => $this->other_medical_condition ?? null,
           // 'file' => $this->file ? asset('storage/' . $this->file) : null,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}