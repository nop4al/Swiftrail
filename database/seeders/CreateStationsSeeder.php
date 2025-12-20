<?php

namespace Database\Seeders;

use App\Models\Station;
use Illuminate\Database\Seeder;

class CreateStationsSeeder extends Seeder
{
    public function run(): void
    {
        Station::firstOrCreate(['code' => 'JKT'], ['name' => 'Jakarta Pusat', 'city' => 'Jakarta', 'active' => true]);
        Station::firstOrCreate(['code' => 'GMB'], ['name' => 'Gambir', 'city' => 'Jakarta', 'active' => true]);
        Station::firstOrCreate(['code' => 'BDG'], ['name' => 'Bandung', 'city' => 'Bandung', 'active' => true]);
        Station::firstOrCreate(['code' => 'SBY'], ['name' => 'Surabaya Tawang', 'city' => 'Surabaya', 'active' => true]);
        Station::firstOrCreate(['code' => 'SBY-P'], ['name' => 'Surabaya Pasar Turi', 'city' => 'Surabaya', 'active' => true]);
        Station::firstOrCreate(['code' => 'MLG'], ['name' => 'Malang', 'city' => 'Malang', 'active' => true]);
    }
}
