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
            'contributorFamilies' => $this->whenLoaded('contributorFamilies', function () {
                return FamilyResource::collection($this->contributorFamilies);
            }),
            'delegateFamilies' => $this->whenLoaded('delegateFamilies', function () {
                return FamilyResource::collection($this->delegateFamilies);
            }),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
