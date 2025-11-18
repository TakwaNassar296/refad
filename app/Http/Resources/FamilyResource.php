<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class FamilyResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'familyName' => $this->family_name,
            'fatherName' => $this->father_name,
            'nationalId' => $this->national_id,
            'dob' => $this->dob,
            'phone' => $this->phone,
            'email' => $this->email,
            'totalMembers' => $this->total_members,
            'elderlyCount' => $this->elderly_count,
            'medicalConditionsCount' => $this->medical_conditions_count,
            'childrenCount' => $this->children_count,
            'femalesCount' => $this->whenLoaded('members', function () {
                return $this->members->where('gender', 'female')->count();
            }),
            'malesCount' => $this->whenLoaded('members', function () {
                return $this->members->where('gender', 'male')->count();
            }),
            'tentNumber' => $this->tent_number,
            'location' => $this->location,
            'notes' => $this->notes,
            'camp' => new CampResource($this->whenLoaded('camp')),
            'delegate' => new UserResource($this->whenLoaded('delegate')),
            'members' => FamilyMemberResource::collection($this->whenLoaded('members')),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}