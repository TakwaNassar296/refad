<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ContributionResource;

class ProjectResource extends JsonResource
{
    public function toArray($request)
    {

        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'addedBy' => optional($this->addedBy)->name,
            'beneficiaryCount' => $this->beneficiary_count,
            'college' => $this->college,
            'status' => $this->status,
            'isApproved' => $this->is_approved,
            'notes' => $this->notes,
            'projectImage' => $this->project_image ? asset('storage/' . $this->project_image) : null,
            'totalReceived' => $this->total_received,
            'totalRemaining' =>$this->total_remaining,
            'camp' => $this->whenLoaded('camp', fn () => $this->camp->name),
            'contributions' => ContributionResource::collection($this->whenLoaded('contributions')),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
