<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ContributionResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'totalQuantity' => $this->total_quantity,
            'notes' => $this->notes,
            'status' => $this->status, 
            'project' => $this->whenLoaded('project', function () {
                return new ProjectResource($this->project);
            }),
            'families' => $this->whenLoaded('families', function () {
                return FamilyResource::collection($this->families);
            }),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
