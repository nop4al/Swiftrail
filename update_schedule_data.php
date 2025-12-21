<?php
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Station;
use App\Models\Train;
use App\Models\TrainStop;

// First, ensure all stations exist with correct coordinates
$stations = [
    ['code' => 'GMB', 'name' => 'Gambir', 'city' => 'Jakarta', 'latitude' => -6.1744, 'longitude' => 106.8294],
    ['code' => 'KOTA', 'name' => 'Kota', 'city' => 'Jakarta', 'latitude' => -6.1371, 'longitude' => 106.8083],
    ['code' => 'CBY', 'name' => 'Cirebon', 'city' => 'Cirebon', 'latitude' => -6.7034, 'longitude' => 108.4689],
    ['code' => 'PKL', 'name' => 'Pekalongan', 'city' => 'Pekalongan', 'latitude' => -6.8839, 'longitude' => 109.6778],
    ['code' => 'SMG', 'name' => 'Semarang Tawang', 'city' => 'Semarang', 'latitude' => -6.9728, 'longitude' => 110.4208],
    ['code' => 'BJN', 'name' => 'Bojonegoro', 'city' => 'Bojonegoro', 'latitude' => -7.1575, 'longitude' => 111.8844],
    ['code' => 'SBY-P', 'name' => 'Surabaya Pasar Turi', 'city' => 'Surabaya', 'latitude' => -7.2575, 'longitude' => 112.7521],
];

foreach ($stations as $data) {
    Station::updateOrCreate(
        ['code' => $data['code']],
        $data
    );
    echo "Updated Station: " . $data['name'] . "\n";
}

// Get the EX-3002 train
$train = Train::where('code', 'EX-3002')->first();

if ($train) {
    // Clear ALL existing train stops for this train
    TrainStop::where('train_id', $train->id)->delete();
    echo "Cleared existing train stops\n";
    
    // Get the first schedule for this train
    $schedule = $train->schedules()->first();
    
    if ($schedule) {
        // Add train stops with correct schedule
        $stops = [
            ['station_code' => 'GMB', 'sequence' => 1, 'arrival_time' => null, 'departure_time' => '06:00'],
            ['station_code' => 'CBY', 'sequence' => 2, 'arrival_time' => '08:41', 'departure_time' => '08:46'],
            ['station_code' => 'PKL', 'sequence' => 3, 'arrival_time' => '10:02', 'departure_time' => '10:07'],
            ['station_code' => 'SMG', 'sequence' => 4, 'arrival_time' => '11:07', 'departure_time' => '11:12'],
            ['station_code' => 'BJN', 'sequence' => 5, 'arrival_time' => '11:50', 'departure_time' => '11:55'],
            ['station_code' => 'SBY-P', 'sequence' => 6, 'arrival_time' => '12:30', 'departure_time' => null],
        ];

        foreach ($stops as $stop) {
            $station = Station::where('code', $stop['station_code'])->first();
            
            if ($station) {
                TrainStop::create([
                    'train_id' => $train->id,
                    'schedule_id' => $schedule->id,
                    'station_id' => $station->id,
                    'sequence' => $stop['sequence'],
                    'arrival_time' => $stop['arrival_time'],
                    'departure_time' => $stop['departure_time'],
                ]);
                echo "Added Stop: {$station->name} - Arrival: {$stop['arrival_time']}, Departure: {$stop['departure_time']}\n";
            }
        }
        
        echo "\nAll train stops updated successfully!\n";
    } else {
        echo "Schedule not found for train EX-3002\n";
    }
} else {
    echo "Train EX-3002 not found\n";
}
