<?php

namespace Database\Seeders;

use App\Models\Route;
use Illuminate\Database\Seeder;

class CreateRoutesSeeder extends Seeder
{
    public function run(): void
    {
        Route::firstOrCreate(['code' => 'JKT-SBY'], ['departure_station_id' => 1, 'arrival_station_id' => 3, 'distance' => 758, 'duration' => '5 jam 15 menit', 'base_price' => 450000, 'status' => 'active']);
        Route::firstOrCreate(['code' => 'GMB-SBY-P'], ['departure_station_id' => 2, 'arrival_station_id' => 5, 'distance' => 780, 'duration' => '5 jam 30 menit', 'base_price' => 480000, 'status' => 'active']);
        Route::firstOrCreate(['code' => 'BDG-JKT'], ['departure_station_id' => 3, 'arrival_station_id' => 1, 'distance' => 211, 'duration' => '2 jam 45 menit', 'base_price' => 280000, 'status' => 'active']);
        Route::firstOrCreate(['code' => 'SBY-MLG'], ['departure_station_id' => 4, 'arrival_station_id' => 6, 'distance' => 101, 'duration' => '2 jam 30 menit', 'base_price' => 150000, 'status' => 'active']);
    }
}
