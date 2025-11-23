<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $existingAdmin = User::where('email', 'admin@example.com')->first();
        
        if ($existingAdmin) {
            $this->command->info('Admin user already exists.');
            return;
        }

        $admin = User::create([
            'name' => 'System Administrator',
            'email' => 'admin@example.com',
            'password' => Hash::make('admin123'),
            'id_number' => 'ADMIN001',
            'phone' => '+1234567890',
            'role' => 'admin',
            'license_number' => 'ADMIN-LICENSE-001',
            'accept_terms' => true,
            'status' => 'approved',
            'admin_position' => 'Super Administrator',
            'backup_phone' => '+1234567891',
            'camp_id' => null, 
            'email_verified_at' => now(),
        ]);

        $this->command->info('Admin user created successfully!');
        $this->command->info('Email: admin@example.com');
        $this->command->info('Password: admin123');
        $this->command->info('Role: admin');
        $this->command->info('Camp: None (Administrative user)');
    }
}
