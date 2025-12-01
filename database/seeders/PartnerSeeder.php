<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Partner::create([
            'name' => 'وزارة التنمية الاجتماعية',
            'logo' => 'partners/social-development.jpg',
            'order' => 1,
        ]);

        Partner::create([
            'name' => 'الأونروا',
            'logo' => 'partners/unrwa.jpg',
            'order' => 2,
        ]);

        Partner::create([
            'name' => 'برنامج الأمم المتحدة الإنمائي',
            'logo' => 'partners/undp.jpg',
            'order' => 3,
        ]);

        Partner::create([
            'name' => 'منظمة الصحة العالمية',
            'logo' => 'partners/who.jpg',
            'order' => 4,
        ]);

        Partner::create([
            'name' => 'اليونيسف',
            'logo' => 'partners/unicef.jpg',
            'order' => 5,
        ]);

        Partner::create([
            'name' => 'الصليب الأحمر',
            'logo' => 'partners/red-cross.jpg',
            'order' => 6,
           
        ]);
    }
}