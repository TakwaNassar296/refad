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
            'website' => 'https://www.social.gov.ps',
            'order' => 1,
            'is_active' => true
        ]);

        Partner::create([
            'name' => 'الأونروا',
            'logo' => 'partners/unrwa.jpg',
            'website' => 'https://www.unrwa.org',
            'order' => 2,
            'is_active' => true
        ]);

        Partner::create([
            'name' => 'برنامج الأمم المتحدة الإنمائي',
            'logo' => 'partners/undp.jpg',
            'website' => 'https://www.ps.undp.org',
            'order' => 3,
            'is_active' => true
        ]);

        Partner::create([
            'name' => 'منظمة الصحة العالمية',
            'logo' => 'partners/who.jpg',
            'website' => 'https://www.emro.who.int',
            'order' => 4,
            'is_active' => true
        ]);

        Partner::create([
            'name' => 'اليونيسف',
            'logo' => 'partners/unicef.jpg',
            'website' => 'https://www.unicef.org',
            'order' => 5,
            'is_active' => true
        ]);

        Partner::create([
            'name' => 'الصليب الأحمر',
            'logo' => 'partners/red-cross.jpg',
            'website' => 'https://www.icrc.org',
            'order' => 6,
            'is_active' => true
        ]);
    }
}