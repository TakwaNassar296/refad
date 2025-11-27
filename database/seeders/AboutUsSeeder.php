<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\AboutUs;

class AboutUsSeeder extends Seeder
{
    public function run()
    {
        AboutUs::create([
            'title' => [
                'en' => 'About our camp',
                'ar' => 'عن المخيم'
            ],
            'description' => [
                'en' => 'Default about us description.',
                'ar' => 'وصف افتراضي لصفحة من نحن.'
            ],
            'image' => null,
        ]);
    }
}
