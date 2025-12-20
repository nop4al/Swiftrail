<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'user_id' => 'SWR-USER-' . strtoupper(bin2hex(random_bytes(3))),
            'first_name' => 'Admin',
            'last_name' => 'SwiftRail',
            'email' => 'admin@swiftrail.my.id',
            'password' => Hash::make('SwiftRailGACOR'),
            'gender' => 'Laki-laki',
            'role' => 'admin',
        ]);
    }
}
