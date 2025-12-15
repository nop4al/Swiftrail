<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Admin user sudah dibuat oleh migration: 2025_12_15_120000_create_admin_user.php
        // Email: admin@swiftrail.my.id
        // Password: SwiftRailGACOR
        
        // Create sample user untuk testing
        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'user_id' => 'SWR-USER_00001',
                'first_name' => 'Test',
                'last_name' => 'User',
                'password' => bcrypt('password123'),
                'role' => 'user'
            ]
        );
        
        // Buat sample data untuk stations, routes, trains, schedules
        $this->call([
            CreateStationsSeeder::class,
            CreateTrainsSeeder::class,
            CreateRoutesSeeder::class,
            CreateSchedulesSeeder::class,
            CreateBookingsSeeder::class
        ]);
    }
}