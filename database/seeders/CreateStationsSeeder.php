<?php

namespace Database\Seeders;

use App\Models\Station;
use Illuminate\Database\Seeder;

class CreateStationsSeeder extends Seeder
{
    public function run(): void
    {
        Station::firstOrCreate(['code' => 'JKT'], ['name' => 'Stasiun Pusat Jakarta', 'city' => 'Jakarta', 'active' => true]);
        Station::firstOrCreate(['code' => 'GMB'], ['name' => 'Stasiun Gambir', 'city' => 'Jakarta', 'active' => true]);
        Station::firstOrCreate(['code' => 'BDG'], ['name' => 'Stasiun Bandung', 'city' => 'Bandung', 'active' => true]);
        Station::firstOrCreate(['code' => 'SBY'], ['name' => 'Stasiun Tawang Alun Surabaya', 'city' => 'Surabaya', 'active' => true]);
        Station::firstOrCreate(['code' => 'SBY-P'], ['name' => 'Stasiun Pasar Turi Surabaya', 'city' => 'Surabaya', 'active' => true]);
        Station::firstOrCreate(['code' => 'MLG'], ['name' => 'Stasiun Malang', 'city' => 'Malang', 'active' => true]);
    }
}
