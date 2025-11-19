<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CampResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->getTranslation('name', app()->getLocale()),
            'description' =>  $this->getTranslation('description', app()->getLocale()),
            'slug' => $this->slug,
            'familyCount' => $this->family_count,
            'childrenCount' => $this->children_count,
            'elderlyCount' => $this->elderly_count,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'bankAccount' => $this->bank_account,
            'delegates' => UserResource::collection($this->whenLoaded('delegates')),
            'families' => FamilyResource::collection($this->whenLoaded('families')),
            'projects' => ProjectResource::collection($this->whenLoaded('projects')),
            'createdAt' => $this->created_at,
            'updatedAt' => $this->updated_at,
        ];
    }
}
