<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        Page::create([
            'type' => 'terms',
            'title' => [
                'en' => 'Terms of Use',
                'ar' => 'شروط الاستخدام'
            ],
            'description' => [
                'en' => 'By using Refad platform, you agree to comply with the following terms and conditions. Please read them carefully before using the platform.',
                'ar' => 'باستخدامك لمنصة رفد، فإنك توافق على الالتزام بالشروط والأحكام التالية. يرجى قراءتها بعناية قبل استخدام المنصة.'
            ],
            'image' => 'pages/terms.jpg'
        ]);
        Page::create([
            'type' => 'privacy',
            'title' => [
                'en' => 'Privacy Policy',
                'ar' => 'سياسة الخصوصية'
            ],
            'description' => [
                'en' => 'At Refad, we are committed to protecting the privacy of our users data. This policy explains how we collect, use, and protect your personal information.',
                'ar' => 'نحن في رفد نحرص على حماية خصوصية بيانات مستخدمينا. توضح هذه السياسة كيفية جمعنا واستخدامنا وحماية معلوماتكم الشخصية.'
            ],
            'image' => 'pages/privacy.jpg'
        ]);

        Page::create([
            'type' => 'transparency',
            'title' => [
                'en' => 'Transparency',
                'ar' => 'الشفافية'
            ],
            'description' => [
                'en' => 'We believe in full transparency in all our operations and allow everyone to see how donations are used and projects are implemented.',
                'ar' => 'نؤمن بالشفافية الكاملة في جميع عملياتنا ونتيح للجميع الاطلاع على كيفية استخدام التبرعات والمشاريع المنفذة.'
            ],
            'image' => 'pages/transparency.jpg'
        ]);

        Page::create([
            'type' => 'mission',
            'title' => [
                'en' => 'Our Mission',
                'ar' => 'رسالتنا'
            ],
            'description' => [
                'en' => 'Our mission is to provide sustainable support to families in Gaza through development projects that improve quality of life and empower the community.',
                'ar' => 'رسالتنا هي توفير الدعم المستدام للأسر في غزة من خلال مشاريع تنموية تعمل على تحسين جودة الحياة وتمكين المجتمع.'
            ],
            'image' => 'pages/mission.jpg'
        ]);

        Page::create([
            'type' => 'vision',
            'title' => [
                'en' => 'Our Vision',
                'ar' => 'رؤيتنا'
            ],
            'description' => [
                'en' => 'We strive to achieve an empowered community capable of facing challenges, where every family in Gaza can live with dignity and hope.',
                'ar' => 'نسعى لتحقيق مجتمع متمكن وقادر على مواجهة التحديات، حيث تكون كل أسرة في غزة قادرة على العيش بكرامة وأمل.'
            ],
            'image' => 'pages/vision.jpg'
        ]);

        Page::create([
            'type' => 'goals',
            'title' => [
                'en' => 'Our Goals',
                'ar' => 'أهدافنا'
            ],
            'description' => [
                'en' => 'We seek to achieve clear goals including supporting needy families, implementing sustainable projects, and building effective community partnerships.',
                'ar' => 'نسعى لتحقيق أهداف واضحة تشمل دعم الأسر المحتاجة، تنفيذ مشاريع مستدامة، وبناء شراكات مجتمعية فاعلة.'
            ],
            'image' => 'pages/goals.jpg'
        ]);
    }
}
