<?php

namespace Database\Seeders;

use App\Models\Route;
use Illuminate\Database\Seeder;

class RouteSeeder extends Seeder
{
    public function run(): void
    {
        Route::create(['code' => 'JKT-SBY', 'departure_station_id' => 1, 'arrival_station_id' => 3, 'distance' => 758, 'duration' => '5 jam 15 menit', 'base_price' => 450000, 'status' => 'active']);
        Route::create(['code' => 'BDG-JKT', 'departure_station_id' => 2, 'arrival_station_id' => 1, 'distance' => 211, 'duration' => '2 jam 45 menit', 'base_price' => 280000, 'status' => 'active']);
        Route::create(['code' => 'SBY-MLG', 'departure_station_id' => 3, 'arrival_station_id' => 4, 'distance' => 101, 'duration' => '2 jam 30 menit', 'base_price' => 150000, 'status' => 'active']);
    }
}
