<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AboutUs;

class AboutUsSeeder extends Seeder
{
    public function run()
    {
        $aboutUs = AboutUs::first();
        if ($aboutUs) {
            $aboutUs->update([
                'page_type' => 'about_us',
            ]);
        }

        $pages = [
            [
                'page_type' => 'mission',
                'title' => [
                    'ar' => 'رؤيتنا',
                    'en' => 'رؤيتنا'
                ],
                'description' => [
                    'ar' => '<p>حوكمة إنسانية ذكية في <strong>قلب </strong><em>الأزمات</em>...</p><p></p>',
                    'en' => '<p>Humanitarian Smart Governance at the Heart of Crises</p>'
                ],
                'image' => 'https://reffad.cloud/storage/pages/mission.jpg',
                'second_image' => null,
                'file' => null,
            ],
            [
                'page_type' => 'vision',
                'title' => [
                    'ar' => 'رؤيتنا',
                    'en' => 'vision'
                ],
                'description' => [
                    'ar' => '<p>حوكمة إنسانية ذكية في قلب الأزمات.</p><p></p>',
                    'en' => '<p>(Humanitarian Smart Governance at the Heart of Crises)</p>'
                ],
                'image' => 'https://reffad.cloud/storage/pages/vision.jpg',
                'second_image' => null,
                'file' => null,
            ],
            [
                'page_type' => 'goals',
                'title' => [
                    'ar' => 'أهدافنا',
                    'en' => 'Our Goals'
                ],
                'description' => [
                    'ar' => '<p>1- إرساء نموذج حوكمة إنسانية ذكية في بيئات الأزمات.</p><p>2- قياس أداء الجهات الشريكة وتتبع جودة وسرعة ودقة الاستجابة الإنسانية.</p><p>3- توثيق المشاريع الإنسانية وإظهار أثرها بشفافية وقابلية للتحقق.</p><p>4- بناء قاعدة بيانات إنسانية موثوقة تُبرز الاحتياجات والفئات الأكثر هشاشة بالأرقام.</p><p>تمكين آليات التغذية الراجعة والشكاوى لتعزيز المساءلة والتحسين المستمر.</p><p>5- دعم التخطيط الإنساني عبر الخرائط التفاعلية وتحديد مواقع الخدمات والاستجابة.</p><p>تطوير نموذج رقمي قابل للتطبيق والتوسع عالميًا في سياقات الأزمات والصراعات.</p><p></p>',
                    'en' => '<p>Establish a smart humanitarian governance model in crisis contexts.</p><p>Measure partner performance and track the quality, speed, and accuracy of humanitarian responses.</p><p>Document humanitarian projects and transparently demonstrate their field-level impact.</p><p>Build a reliable humanitarian data system highlighting needs and vulnerable groups through evidence-based metrics.</p><p>Enable structured feedback, complaints, and accountability mechanisms for continuous improvement.</p><p>Support humanitarian planning through interactive mapping of services and response locations.</p><p>Develop a scalable digital model applicable across diverse crisis and conflict settings globally.</p>'
                ],
                'image' => 'https://reffad.cloud/storage/pages/7KVJXDN6RmTGlxIxjQc41ZXHAjrJQn15qdYzArpS.png',
                'second_image' => null,
                'file' => null,
            ],
        ];

        foreach ($pages as $page) {
            AboutUs::firstOrCreate(
                ['page_type' => $page['page_type']],
                $page
            );
        }
    }
}
