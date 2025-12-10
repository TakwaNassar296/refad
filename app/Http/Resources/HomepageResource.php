<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HomepageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'slides' => $this->slides->map(function ($slide) {
                return [
                    'id' => $slide->id,
                    'heroTitle' => $slide->getTranslations('hero_title'),
                    'heroDescription' => $slide->getTranslations('hero_description'),
                    'heroSubtitle' => $slide->getTranslations('hero_subtitle'),
                    'heroImage' => $slide->hero_image ? asset('storage/' . $slide->hero_image) : null,
                    'smallHeroImage' => $slide->small_hero_image ? asset('storage/' . $slide->small_hero_image) : null,
                ];
            }),
            'campsCount' => \App\Models\Camp::count(),
            'contributorsCount' => \App\Models\Contribution::count(),
            'projectsCount' => \App\Models\Project::count(),
            'familiesCount' => \App\Models\Family::count(),
        ];
    }
}
