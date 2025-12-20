<?php

namespace Database\Seeders;

use App\Models\TrainStop;
use App\Models\Train;
use App\Models\Station;
use Illuminate\Database\Seeder;

class CreateTrainStopsSeeder extends Seeder
{
    public function run(): void
    {
        // Get trains and stations
        $trains = Train::all();
        $stations = Station::all();

        if ($trains->isEmpty() || $stations->isEmpty()) {
            echo "Trains or Stations not found. Please seed them first.\n";
            return;
        }

        // Train 1: AB-5001 (Argo Bromo Ekspres) - Route: Jakarta to Surabaya
        // Stations: Gambir -> Cirebon -> Pekalongan -> Semarang -> Bojonegoro -> Surabaya
        $train1 = $trains->firstWhere('code', 'AB-5001');
        if ($train1) {
            $stationSequence = [
                ['name' => 'Gambir', 'arrival' => null, 'departure' => '14:30'],
                ['name' => 'Cirebon', 'arrival' => '16:45', 'departure' => '17:00'],
                ['name' => 'Pekalongan', 'arrival' => '17:45', 'departure' => '18:00'],
                ['name' => 'Semarang Tawang', 'arrival' => '18:45', 'departure' => '19:00'],
                ['name' => 'Bojonegoro', 'arrival' => '20:30', 'departure' => '20:45'],
                ['name' => 'Surabaya Pasar Turi', 'arrival' => '22:00', 'departure' => null],
            ];

            foreach ($stationSequence as $seq => $stationData) {
                $station = $stations->firstWhere('name', $stationData['name']);
                if ($station) {
                    TrainStop::create([
                        'train_id' => $train1->id,
                        'station_id' => $station->id,
                        'sequence' => $seq + 1,
                        'arrival_time' => $stationData['arrival'],
                        'departure_time' => $stationData['departure'],
                    ]);
                }
            }
        }

        // Train 2: EX-3002 (Ekspres Utama Timur) - Route: Jakarta to Surabaya
        $train2 = $trains->firstWhere('code', 'EX-3002');
        if ($train2) {
            $stationSequence = [
                ['name' => 'Gambir', 'arrival' => null, 'departure' => '06:00'],
                ['name' => 'Pekalongan', 'arrival' => '08:00', 'departure' => '08:15'],
                ['name' => 'Semarang Tawang', 'arrival' => '09:15', 'departure' => '09:30'],
                ['name' => 'Bojonegoro', 'arrival' => '11:00', 'departure' => '11:15'],
                ['name' => 'Surabaya Pasar Turi', 'arrival' => '12:30', 'departure' => null],
            ];

            foreach ($stationSequence as $seq => $stationData) {
                $station = $stations->firstWhere('name', $stationData['name']);
                if ($station) {
                    TrainStop::create([
                        'train_id' => $train2->id,
                        'station_id' => $station->id,
                        'sequence' => $seq + 1,
                        'arrival_time' => $stationData['arrival'],
                        'departure_time' => $stationData['departure'],
                    ]);
                }
            }
        }

        // Train 3: GA-2003 (Gajayana Regional) - Route: Jakarta to Surabaya
        $train3 = $trains->firstWhere('code', 'GA-2003');
        if ($train3) {
            $stationSequence = [
                ['name' => 'Gambir', 'arrival' => null, 'departure' => '15:30'],
                ['name' => 'Cirebon', 'arrival' => '17:30', 'departure' => '17:45'],
                ['name' => 'Pekalongan', 'arrival' => '18:30', 'departure' => '18:45'],
                ['name' => 'Semarang Tawang', 'arrival' => '19:30', 'departure' => '19:45'],
                ['name' => 'Bojonegoro', 'arrival' => '21:15', 'departure' => '21:30'],
                ['name' => 'Surabaya Pasar Turi', 'arrival' => '23:00', 'departure' => null],
            ];

            foreach ($stationSequence as $seq => $stationData) {
                $station = $stations->firstWhere('name', $stationData['name']);
                if ($station) {
                    TrainStop::create([
                        'train_id' => $train3->id,
                        'station_id' => $station->id,
                        'sequence' => $seq + 1,
                        'arrival_time' => $stationData['arrival'],
                        'departure_time' => $stationData['departure'],
                    ]);
                }
            }
        }

        echo "Train stops created successfully!\n";
    }
}
