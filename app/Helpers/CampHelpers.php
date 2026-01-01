<?php

namespace App\Helpers;

use App\Models\MedicalCondition;

class CampHelpers
{
    
    public static function allMedicalConditions(): array
    {
        return MedicalCondition::pluck('name')->unique()->values()->toArray();
    }

    
    public static function ageGroupsNames(): array
    {
        return [
            'newborns',
            'infants',
            'veryEarlyChildhood',
            'toddlers',
            'earlyChildhood',
            'children',
            'adolescents',
            'youth',
            'youngAdults',
            'middleAgeAdults',
            'lateMiddleAge',
            'seniors',
        ];
    }
}
