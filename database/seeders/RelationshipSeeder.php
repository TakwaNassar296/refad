<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Relationship;

class RelationshipSeeder extends Seeder
{
    public function run(): void
    {
        $relationships = ['أب', 'أم', 'ابن', 'ابنة', 'أخ', 'أخت', 'جد', 'جدة', 'عم', 'عمة', 'خال', 'خالة'];

        foreach ($relationships as $name) {
            Relationship::updateOrCreate(['name' => $name]);
        }
    }
}
