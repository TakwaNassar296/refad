<?php

namespace Database\Seeders;

use App\Models\Homepage;
use App\Models\HomepageSlide;
use Illuminate\Database\Seeder;

class HomepageSeeder extends Seeder
{
    public function run(): void
    {
        $homepage = Homepage::create([
            'camps_count' => 6500,
            'contributors_count' => 3200,
            'projects_count' => 1400,
            'families_count' => 30,
        ]);

        HomepageSlide::create([
            'homepage_id' => $homepage->id,
            'hero_title' => ['en' => 'Refad - Rebuilding Hope for Gaza', 'ar' => 'رفد - إعادة بناء الأمل لغزة'],
            'hero_description' => [
                'en' => 'A comprehensive platform connecting donors with families in need across Gaza.',
                'ar' => 'منصة شاملة تربط المتبرعين مع العائلات المحتاجة في جميع أنحاء غزة.'
            ],
            'hero_subtitle' => [
                'en' => 'Our goal is to support those in need and nurture hope.',
                'ar' => 'هدفنا دعم المحتاجين وتنمية الأمل.'
            ],
            'hero_image' => 'homepage/refad-hero.jpg',
            'small_hero_image' => 'homepage/refad-hero-small.jpg'
        ]);

        HomepageSlide::create([
            'homepage_id' => $homepage->id,
            'hero_title' => ['en' => 'Second Slide Title', 'ar' => 'عنوان الشريحة الثانية'],
            'hero_description' => [
                'en' => 'This is the second slide description in English.',
                'ar' => 'هذا وصف الشريحة الثانية باللغة العربية.'
            ],
            'hero_subtitle' => [
                'en' => 'Second slide subtitle.',
                'ar' => 'العنوان الفرعي للشريحة الثانية.'
            ],
            'hero_image' => 'homepage/second-slide.jpg',
            'small_hero_image' => 'homepage/second-slide-small.jpg'
        ]);
    }
}
