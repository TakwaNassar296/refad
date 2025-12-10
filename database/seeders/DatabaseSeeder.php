<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
           AdminSeeder::class,
           HomepageSeeder::class,
           PageSeeder::class,
           AboutUsSeeder::class,
           PartnerSeeder::class,
           SettingSeeder::class,
           TestimonialSeeder::class,
           MaritalStatusSeeder::class,
           MedicalConditionSeeder::class,
           RelationshipSeeder::class
        ]);

    }
}
