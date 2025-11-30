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
            'addedBy' => $this->addedBy->name,
            'beneficiaryCount' => $this->beneficiary_count,
            'college' => $this->college,
            'projectNumber' => $this->project_number,
            'status' => $this->status,
            'isApproved' => $this->is_approved,
            'notes' => $this->notes,
            'file' => $this->when($this->file_path, [
                'path' => $this->file_path ? asset('storage/' . $this->file_path) : null,
                'originalName' => $this->file_original_name,
                'type' => $this->file_type,
                'size' => $this->file_size,
            ]),
            'totalReceived' => $this->total_received,
            'totalRemaining' =>$this->total_remaining,
            'camp' => $this->whenLoaded('camp', fn () => $this->camp->name),
            'contributions' => ContributionResource::collection($this->whenLoaded('contributions')),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
