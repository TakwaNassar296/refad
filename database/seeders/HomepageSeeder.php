<?php

namespace Database\Seeders;

use App\Models\Homepage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HomepageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Homepage::create([
            'hero_title' => [
                'en' => 'Refad - Rebuilding Hope for Gaza',
                'ar' => 'رفد - إعادة بناء الأمل لغزة'
            ],
            'hero_description' => [
                'en' => 'A comprehensive platform connecting donors with families in need across Gaza. Together we rebuild, restore, and renew hope for a better tomorrow.',
                'ar' => 'منصة شاملة تربط المتبرعين مع العائلات المحتاجة في جميع أنحاء غزة. معاً نبني، نستعيد، ونحيي الأمل لغد أفضل.'
            ],
            'hero_subtitle' => [
                'en' => 'Our goal is to support those in need and nurture hope.',
                'ar' => 'هدفنا دعم المحتاجين وتنمية الأمل.'
            ],
            'hero_image' => 'homepage/refad-hero.jpg',
            'small_hero_image' => 'homepage/refad-hero.jpg',
            'camps_count' => 6500,
            'contributors_count' => 3200,
            'projects_count' => 1400,
            'families_count' => 30,
        ]);
    }
}