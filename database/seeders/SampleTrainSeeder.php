<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Train;
use App\Models\Station;
use App\Models\TrainStop;

class SampleTrainSeeder extends Seeder
{
    public function run(): void
    {
        $train = Train::updateOrCreate(
            ['code' => 'KA001'],
            [
                'name' => 'Argo Bromo Anggrek',
                'type' => 'Executive',
                'capacity' => 500,
                'status' => 'active'
            ]
        );

        $stops = [
            ['code' => 'SBY', 'sequence' => 1, 'departure_time' => '06:00:00'],
            ['code' => 'BJN', 'sequence' => 2, 'arrival_time' => '07:30:00', 'departure_time' => '07:45:00'],
            ['code' => 'SMG', 'sequence' => 3, 'arrival_time' => '09:00:00', 'departure_time' => '09:15:00'],
            ['code' => 'PKL', 'sequence' => 4, 'arrival_time' => '11:00:00', 'departure_time' => '11:15:00'],
            ['code' => 'CRB', 'sequence' => 5, 'arrival_time' => '13:30:00', 'departure_time' => '13:45:00'],
            ['code' => 'GMB', 'sequence' => 6, 'arrival_time' => '16:30:00'],
        ];

        foreach ($stops as $stop) {
            $station = Station::where('code', $stop['code'])->first();
            if ($station) {
                TrainStop::updateOrCreate(
                    ['train_id' => $train->id, 'sequence' => $stop['sequence']],
                    [
                        'station_id' => $station->id,
                        'arrival_time' => $stop['arrival_time'] ?? null,
                        'departure_time' => $stop['departure_time'] ?? null,
                    ]
                );
            }
        }

        $this->command->info('Sample train KA001 created with stops!');
    }
}
