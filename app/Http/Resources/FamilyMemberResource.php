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
            'dob' => $this->dob,
            'nationalId' => $this->national_id,
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}