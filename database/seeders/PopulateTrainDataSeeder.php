<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Train;
use App\Models\Station;
use App\Models\TrainStop;

class PopulateTrainDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all stations
        $stations = Station::all()->keyBy('code');

        // Train data
        $trains = [
            [
                'code' => 'KA1',
                'name' => 'Argo Bromo Anggrek',
                'type' => 'executive',
                'capacity' => 200,
                'status' => 'active'
            ],
            [
                'code' => 'KA2',
                'name' => 'Argo Bromo Anggrek',
                'type' => 'executive',
                'capacity' => 200,
                'status' => 'active'
            ],
            [
                'code' => 'KA3',
                'name' => 'Argo Bromo Anggrek',
                'type' => 'compartment',
                'capacity' => 200,
                'status' => 'active'
            ],
            [
                'code' => 'KA4',
                'name' => 'Argo Bromo Anggrek',
                'type' => 'compartment',
                'capacity' => 200,
                'status' => 'active'
            ],
            [
                'code' => 'AP1',
                'name' => 'Argo Parahyangan',
                'type' => 'economy',
                'capacity' => 200,
                'status' => 'active'
            ],
            [
                'code' => 'AP2',
                'name' => 'Argo Parahyangan',
                'type' => 'economy',
                'capacity' => 200,
                'status' => 'active'
            ],
            [
                'code' => 'EU1',
                'name' => 'Ekspres Utara',
                'type' => 'economy',
                'capacity' => 200,
                'status' => 'active'
            ],
            [
                'code' => 'EU2',
                'name' => 'Ekspres Utara',
                'type' => 'economy',
                'capacity' => 200,
                'status' => 'active'
            ],
        ];

        // Routes: [train_code => [[station_code, seq, arrival, departure], ...]]
        $routes = [
            'KA1' => [
                ['SBY', 1, null, '09:10'],
                ['BJN', 2, '10:16', '10:21'],
                ['SMG', 3, '12:02', '12:17'],
                ['PKL', 4, '13:17', '13:22'],
                ['CRB', 5, '14:40', '14:45'],
                ['GMB', 6, '17:15', null],
            ],
            'KA2' => [
                ['GMB', 1, null, '08:20'],
                ['CRB', 2, '10:01', '10:46'],
                ['PKL', 3, '12:02', '12:07'],
                ['SMG', 4, '13:07', '13:12'],
                ['BJN', 5, '15:06', '15:11'],
                ['SBY', 6, '16:25', null],
            ],
            'KA3' => [
                ['SBY', 1, null, '21:15'],
                ['BJN', 2, '22:21', '22:26'],
                ['SMG', 3, '00:17', '00:22'],
                ['PKL', 4, '01:22', '01:27'],
                ['CRB', 5, '02:45', '02:50'],
                ['GMB', 6, '05:20', null],
            ],
            'KA4' => [
                ['GMB', 1, null, '20:30'],
                ['CRB', 2, '22:51', '22:56'],
                ['PKL', 3, '00:12', '00:17'],
                ['SMG', 4, '01:17', '01:22'],
                ['BJN', 5, '03:16', '03:21'],
                ['SBY', 6, '04:35', null],
            ],
            'AP1' => [
                ['GMB', 1, null, '08:00'],
                ['CRB', 2, '09:40', '09:45'],
                ['PKL', 3, '10:55', '11:00'],
                ['SMG', 4, '12:17', null],
            ],
            'AP2' => [
                ['SMG', 1, null, '13:00'],
                ['PKL', 2, '14:12', '14:17'],
                ['CRB', 3, '15:27', '15:32'],
                ['GMB', 4, '17:17', null],
            ],
            'EU1' => [
                ['SBY', 1, null, '10:21'],
                ['BJN', 2, '10:16', '10:21'],
                ['SMG', 3, '12:02', null],
            ],
            'EU2' => [
                ['SMG', 1, null, '13:07'],
                ['BJN', 2, '15:06', '15:11'],
                ['SBY', 3, '16:30', null],
            ],
        ];

        // Create trains and train stops
        foreach ($trains as $trainData) {
            $train = Train::firstOrCreate(
                ['code' => $trainData['code']],
                $trainData
            );

            // Clear existing stops
            TrainStop::where('train_id', $train->id)->delete();

            // Add stops
            if (isset($routes[$trainData['code']])) {
                foreach ($routes[$trainData['code']] as $stopData) {
                    [$stationCode, $seq, $arrival, $departure] = $stopData;
                    
                    if (isset($stations[$stationCode])) {
                        TrainStop::create([
                            'train_id' => $train->id,
                            'station_id' => $stations[$stationCode]->id,
                            'sequence' => $seq,
                            'arrival_time' => $arrival,
                            'departure_time' => $departure,
                        ]);
                    }
                }
            }
        }

        $this->command->info('Train data populated successfully!');
    }
}
