<?php

namespace Database\Seeders;

use App\Models\Station;
use Illuminate\Database\Seeder;

class CreateStationsSeeder extends Seeder
{
    public function run(): void
    {
        Station::create(['code' => 'JKT', 'name' => 'Stasiun Pusat Jakarta', 'city' => 'Jakarta', 'active' => true]);
        Station::create(['code' => 'BDG', 'name' => 'Stasiun Bandung', 'city' => 'Bandung', 'active' => true]);
        Station::create(['code' => 'SBY', 'name' => 'Stasiun Tawang Alun Surabaya', 'city' => 'Surabaya', 'active' => true]);
        Station::create(['code' => 'MLG', 'name' => 'Stasiun Malang', 'city' => 'Malang', 'active' => true]);
    }
}
