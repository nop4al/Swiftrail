<?php

namespace Database\Seeders;

use App\Models\Schedule;
use Illuminate\Database\Seeder;

class CreateSchedulesSeeder extends Seeder
{
    public function run(): void
    {
        // Define days as empty string so schedules operate every day
        Schedule::create(['train_id' => 1, 'route_id' => 1, 'departure_time' => '14:30', 'arrival_time' => '19:45', 'days' => '', 'status' => 'active']);
        Schedule::create(['train_id' => 2, 'route_id' => 2, 'departure_time' => '06:00', 'arrival_time' => '11:30', 'days' => '', 'status' => 'active']);
        Schedule::create(['train_id' => 2, 'route_id' => 2, 'departure_time' => '14:00', 'arrival_time' => '19:30', 'days' => '', 'status' => 'active']);
        Schedule::create(['train_id' => 3, 'route_id' => 3, 'departure_time' => '15:30', 'arrival_time' => '18:00', 'days' => '', 'status' => 'active']);
    }
}