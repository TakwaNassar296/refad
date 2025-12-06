<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomepageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'heroTitle' => $this->getTranslations('hero_title'),
            'heroDescription' => $this->getTranslations('hero_description'),
            'heroSubtitle' => $this->getTranslations( 'hero_subtitle'),
            'heroImage' => $this->hero_image ? asset('storage/' . $this->hero_image) : null,
            'smallHeroImage' => $this->small_hero_image ? asset('storage/' . $this->small_hero_image) : null,
            'campsCount' => \App\Models\Camp::count(),
            'contributorsCount' => \App\Models\Contribution::count(),
            'projectsCount' => \App\Models\Project::count(),
            'familiesCount' => \App\Models\Family::count(),
        ];
    }
}
