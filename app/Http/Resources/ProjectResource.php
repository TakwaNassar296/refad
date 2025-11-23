<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray($request)
    {
        $totalReceived = $this->beneficiaryFamilies->sum('pivot.received_quantity');
        $totalRemaining = max(0, $this->college - $totalReceived);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'beneficiaryCount' => $this->beneficiary_count,
            'college' => $this->college,
            'projectNumber' => $this->project_number,
            'status' => $this->status,
            'notes' => $this->notes,
            'file' => $this->when($this->file_path, [
                'path' => $this->file_path ? asset('storage/' . $this->file_path) : null,
                'originalName' => $this->file_original_name,
                'type' => $this->file_type,
                'size' => $this->file_size,
            ]),
            'totalReceived' => $totalReceived, // تم تسليم
            'totalRemaining' => $totalRemaining, // المتبقي
            'camp' => new CampResource($this->whenLoaded('camp')),
            'delegate' => new UserResource($this->whenLoaded('delegate')),
            'beneficiaryFamilies' => FamilyResource::collection($this->whenLoaded('beneficiaryFamilies')),
            'contributions' => ContributionResource::collection($this->whenLoaded('contributions')),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}