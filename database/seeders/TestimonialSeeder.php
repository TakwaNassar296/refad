<?php

namespace Database\Seeders;

use App\Models\Testimonial;
use Illuminate\Database\Seeder;

class TestimonialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Testimonial::create([
            'user_name' => 'محمد أحمد',
            'user_image' => 'testimonials/user1.jpg',
            'opinion' => [
                'en' => 'Refad platform provided us with essential support during difficult times. The assistance reached us quickly and made a real difference in our lives.',
                'ar' => 'منصة رفد وفرت لنا الدعم الأساسي خلال الأوقات الصعبة. المساعدة وصلت إلينا بسرعة وأحدثت فرقاً حقيقياً في حياتنا.'
            ],
            'order' => 1,
        ]);

        Testimonial::create([
            'user_name' => 'فاطمة حسن',
            'user_image' => 'testimonials/user2.jpg',
            'opinion' => [
                'en' => 'Thanks to Refad, my family received the necessary support to start a small project that now provides us with sustainable income.',
                'ar' => 'بفضل رفد، تلقت عائلتي الدعم اللازم لبدء مشروع صغير يوفر لنا الآن دخلاً مستداماً.'
            ],
            'order' => 2,
        ]);

        Testimonial::create([
            'user_name' => 'أحمد محمود',
            'user_image' => 'testimonials/user3.jpg',
            'opinion' => [
                'en' => 'I have been volunteering with Refad for a year now. Their transparency and commitment to helping Gaza families is truly remarkable.',
                'ar' => 'أنا أتطوع مع رفد منذ عام الآن. شفافيتهم والتزامهم بمساعدة عائلات غزة رائعة حقاً.'
            ],
            'order' => 3,
        ]);

        Testimonial::create([
            'user_name' => 'سارة خليل',
            'user_image' => 'testimonials/user4.jpg',
            'opinion' => [
                'en' => 'The educational support my children received through Refad helped them continue their studies despite all challenges.',
                'ar' => 'الدعم التعليمي الذي تلقاه أطفالي من خلال رفد ساعدهم على متابعة دراستهم رغم كل التحديات.'
            ],
            'order' => 4,
        ]);

        Testimonial::create([
            'user_name' => 'يوسف إبراهيم',
            'user_image' => 'testimonials/user5.jpg',
            'opinion' => [
                'en' => 'Working with Refad has been a great experience. Their professional approach and dedication to the community is inspiring.',
                'ar' => 'كان العمل مع رفد تجربة رائعة. نهجهم المهني وتفانيهم تجاه المجتمع ملهم.'
            ],
            'order' => 5,
        ]);
    }
}