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
            'nationalId' => $this->national_id,
            'dob' => $this->dob?->format('Y-m-d'),
            'phone' => $this->phone,
            'backupPhone' => $this->backup_phone,
            'totalMembers' => $this->total_members,
            'fileUrl' => $this->file ? asset('storage/' . $this->file) : null,
            'maritalStatus' => optional($this->maritalStatus)->name,
            'femalesCount' => $this->whenLoaded('members', function () {
                return $this->members->where('gender', 'female')->count();
            }),
            'malesCount' => $this->whenLoaded('members', function () {
                return $this->members->where('gender', 'male')->count();
            }),
            'tentNumber' => $this->tent_number,
            'location' => $this->location,
            'notes' => $this->notes,
            'camp' => $this->camp ? $this->camp->name : null,
            'members' => FamilyMemberResource::collection($this->whenLoaded('members')),

            'pivot' => $this->when($this->pivot, function () {
                return [
                    
                    'receivedQuantity' => $this->pivot->received_quantity,
                    'notes' => $this->pivot->notes,
                ];
            }),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}