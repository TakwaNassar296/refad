<?php

namespace Database\Seeders;

use App\Models\Page;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // من نحن - About Us
        $aboutUs = Page::create([
            'type' => 'about_us', 
            'is_active' => true
        ]);

        $aboutUs->sections()->create([
            'title' => [
                'en' => 'About Us',
                'ar' => 'من نحن'
            ],
            'description' => [
                'en' => 'Refad is a humanitarian platform dedicated to supporting families in Gaza through sustainable projects and community contributions.',
                'ar' => 'رفد هي منصة إنسانية مخصصة لدعم العائلات في غزة من خلال المشاريع المستدامة ومساهمات المجتمع.'
            ],
            'image' => 'pages/about-us.jpg',
            'order' => 1
        ]);

        // شروط الاستخدام - Terms of Use
        $terms = Page::create([
            'type' => 'terms',
            'is_active' => true
        ]);

        $terms->sections()->create([
            'title' => [
                'en' => 'Terms of Use',
                'ar' => 'شروط الاستخدام'
            ],
            'description' => [
                'en' => 'By using Refad platform, you agree to comply with the following terms and conditions. Please read them carefully before using the platform.',
                'ar' => 'باستخدامك لمنصة رفد، فإنك توافق على الالتزام بالشروط والأحكام التالية. يرجى قراءتها بعناية قبل استخدام المنصة.'
            ],
            'image' => 'pages/terms.jpg',
            'order' => 1
        ]);

        // الخصوصية - Privacy Policy
        $privacy = Page::create([
            'type' => 'privacy',
            'is_active' => true
        ]);

        $privacy->sections()->create([
            'title' => [
                'en' => 'Privacy Policy',
                'ar' => 'سياسة الخصوصية'
            ],
            'description' => [
                'en' => 'At Refad, we are committed to protecting the privacy of our users data. This policy explains how we collect, use, and protect your personal information.',
                'ar' => 'نحن في رفد نحرص على حماية خصوصية بيانات مستخدمينا. توضح هذه السياسة كيفية جمعنا واستخدامنا وحماية معلوماتكم الشخصية.'
            ],
            'image' => 'pages/privacy.jpg',
            'order' => 1
        ]);

        // الشفافية - Transparency
        $transparency = Page::create([
            'type' => 'transparency',
            'is_active' => true
        ]);

        $transparency->sections()->create([
            'title' => [
                'en' => 'Transparency',
                'ar' => 'الشفافية'
            ],
            'description' => [
                'en' => 'We believe in full transparency in all our operations and allow everyone to see how donations are used and projects are implemented.',
                'ar' => 'نؤمن بالشفافية الكاملة في جميع عملياتنا ونتيح للجميع الاطلاع على كيفية استخدام التبرعات والمشاريع المنفذة.'
            ],
            'image' => 'pages/transparency.jpg',
            'order' => 1
        ]);

        // الرسالة - Mission
        $mission = Page::create([
            'type' => 'mission',
            'is_active' => true
        ]);

        $mission->sections()->create([
            'title' => [
                'en' => 'Our Mission',
                'ar' => 'رسالتنا'
            ],
            'description' => [
                'en' => 'Our mission is to provide sustainable support to families in Gaza through development projects that improve quality of life and empower the community.',
                'ar' => 'رسالتنا هي توفير الدعم المستدام للأسر في غزة من خلال مشاريع تنموية تعمل على تحسين جودة الحياة وتمكين المجتمع.'
            ],
            'image' => 'pages/mission.jpg',
            'order' => 1
        ]);

        // الرؤية - Vision
        $vision = Page::create([
            'type' => 'vision',
            'is_active' => true
        ]);

        $vision->sections()->create([
            'title' => [
                'en' => 'Our Vision',
                'ar' => 'رؤيتنا'
            ],
            'description' => [
                'en' => 'We strive to achieve an empowered community capable of facing challenges, where every family in Gaza can live with dignity and hope.',
                'ar' => 'نسعى لتحقيق مجتمع متمكن وقادر على مواجهة التحديات، حيث تكون كل أسرة في غزة قادرة على العيش بكرامة وأمل.'
            ],
            'image' => 'pages/vision.jpg',
            'order' => 1
        ]);

        // الأهداف - Goals
        $goals = Page::create([
            'type' => 'goals',
            'is_active' => true
        ]);

        $goals->sections()->create([
            'title' => [
                'en' => 'Our Goals',
                'ar' => 'أهدافنا'
            ],
            'description' => [
                'en' => 'We seek to achieve clear goals including supporting needy families, implementing sustainable projects, and building effective community partnerships.',
                'ar' => 'نسعى لتحقيق أهداف واضحة تشمل دعم الأسر المحتاجة، تنفيذ مشاريع مستدامة، وبناء شراكات مجتمعية فاعلة.'
            ],
            'image' => 'pages/goals.jpg',
            'order' => 1
        ]);
    }
}