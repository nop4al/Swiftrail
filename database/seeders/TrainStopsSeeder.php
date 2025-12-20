<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TrainStopsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // AB-5001 (Argo Bromo Ekspress) - Jakarta to Surabaya
        $stops = [
            [
                'train_id' => 1,
                'station_id' => 1, // Gambir
                'sequence' => 1,
                'arrival_time' => null,
                'departure_time' => '14:30:00',
            ],
            [
                'train_id' => 1,
                'station_id' => 2, // Cirebon
                'sequence' => 2,
                'arrival_time' => '16:45:00',
                'departure_time' => '17:00:00',
            ],
            [
                'train_id' => 1,
                'station_id' => 3, // Pekalongan
                'sequence' => 3,
                'arrival_time' => '18:15:00',
                'departure_time' => '18:30:00',
            ],
            [
                'train_id' => 1,
                'station_id' => 4, // Semarang Tawang
                'sequence' => 4,
                'arrival_time' => '20:00:00',
                'departure_time' => '20:15:00',
            ],
            [
                'train_id' => 1,
                'station_id' => 5, // Bojonegoro
                'sequence' => 5,
                'arrival_time' => '22:30:00',
                'departure_time' => '22:45:00',
            ],
            [
                'train_id' => 1,
                'station_id' => 6, // Surabaya Pasar Turi
                'sequence' => 6,
                'arrival_time' => '23:59:00',
                'departure_time' => null,
            ],
        ];

        foreach ($stops as $stop) {
            DB::table('train_stops')->insert([
                'train_id' => $stop['train_id'],
                'station_id' => $stop['station_id'],
                'sequence' => $stop['sequence'],
                'arrival_time' => $stop['arrival_time'],
                'departure_time' => $stop['departure_time'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
