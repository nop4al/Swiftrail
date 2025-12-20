<?php

namespace Database\Seeders;

use App\Models\Train;
use Illuminate\Database\Seeder;

class TrainSeeder extends Seeder
{
    public function run(): void
    {
        Train::create(['code' => 'AB-5001', 'name' => 'Argo Bromo Ekspress', 'type' => 'Eksekutif', 'capacity' => 120, 'status' => 'active']);
        Train::create(['code' => 'EX-3002', 'name' => 'Ekspres Utama Timur', 'type' => 'Bisnis', 'capacity' => 180, 'status' => 'active']);
        Train::create(['code' => 'GA-2003', 'name' => 'Gajayana Regional', 'type' => 'Ekonomi', 'capacity' => 250, 'status' => 'active']);
    }
}
