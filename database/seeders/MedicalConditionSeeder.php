<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MedicalCondition;

class MedicalConditionSeeder extends Seeder
{
    public function run(): void
    {
        $conditions = [
            'ضغط دم مرتفع',
            'سكري',
            'أمراض قلبية',
            'أمراض تنفسية',
            'حساسية',
            'إعاقة جسدية',
        ];

        foreach ($conditions as $condition) {
            $existing = MedicalCondition::withTrashed()->where('name', $condition)->first();
            
            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
            } else {
                MedicalCondition::create(['name' => $condition]);
            }
        }
    }
}
