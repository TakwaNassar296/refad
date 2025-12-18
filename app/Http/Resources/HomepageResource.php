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

            'ageGroupsCount' => [
                'newborns' => \App\Models\FamilyMember::whereNotNull('dob')
                    ->whereRaw('TIMESTAMPDIFF(DAY, dob, CURDATE()) <= 28')
                    ->count(),

                'infants' => \App\Models\FamilyMember::whereNotNull('dob')
                    ->whereRaw('TIMESTAMPDIFF(DAY, dob, CURDATE()) = 29')
                    ->count(),

                'veryEarlyChildhood' => \App\Models\FamilyMember::whereNotNull('dob')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 1 AND 2')
                    ->count(),

                'toddlers' => \App\Models\FamilyMember::whereNotNull('dob')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 2 AND 3')
                    ->count(),

                'earlyChildhood' => \App\Models\FamilyMember::whereNotNull('dob')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 3 AND 5')
                    ->count(),

                'children' => \App\Models\FamilyMember::whereNotNull('dob')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 5 AND 10')
                    ->count(),

                'adolescents' => \App\Models\FamilyMember::whereNotNull('dob')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 10 AND 18')
                    ->count(),

                'youth' => \App\Models\FamilyMember::whereNotNull('dob')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 18 AND 25')
                    ->count(),

                'youngAdults' => \App\Models\FamilyMember::whereNotNull('dob')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 25 AND 40')
                    ->count(),

                'middleAgeAdults' => \App\Models\FamilyMember::whereNotNull('dob')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 40 AND 50')
                    ->count(),

                'lateMiddleAge' => \App\Models\FamilyMember::whereNotNull('dob')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 50 AND 60')
                    ->count(),

                'seniors' => \App\Models\FamilyMember::whereNotNull('dob')
                    ->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) > 60')
                    ->count(),
            ],


            'title' => $this->getTranslations('title'),
            'description' => $this->getTranslations('description'),

            'sections' => $this->sections->map(function ($section) {
                return [
                    'id' => $section->id,
                    'title' => $section->getTranslations('title'),
                    'description' => $section->getTranslations('description'),
                    'image' => $section->image
                        ? asset('storage/' . $section->image)
                        : null,
                ];
            }),


            'complaintImage' => $this->complaint_image ? asset('storage/' . $this->complaint_image) : null,
            'contactImage'   => $this->contact_image ? asset('storage/' . $this->contact_image) : null,



        ];
    }
}
