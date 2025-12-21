<?php
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Station;

$stations = [
    ['code' => 'GMB', 'name' => 'Gambir', 'city' => 'Jakarta', 'latitude' => -6.1744, 'longitude' => 106.8294],
    ['code' => 'KOTA', 'name' => 'Kota', 'city' => 'Jakarta', 'latitude' => -6.1371, 'longitude' => 106.8083],
    ['code' => 'BDG', 'name' => 'Bandung', 'city' => 'Bandung', 'latitude' => -6.9147, 'longitude' => 107.6098],
    ['code' => 'CBY', 'name' => 'Cirebon', 'city' => 'Cirebon', 'latitude' => -6.7034, 'longitude' => 108.4689],
    ['code' => 'SBY-P', 'name' => 'Surabaya Pasar Turi', 'city' => 'Surabaya', 'latitude' => -7.2575, 'longitude' => 112.7521],
    ['code' => 'SBY-B', 'name' => 'Surabaya Besar', 'city' => 'Surabaya', 'latitude' => -7.2137, 'longitude' => 112.7277],
    ['code' => 'KTG', 'name' => 'Ketanggungan', 'city' => 'Brebes', 'latitude' => -6.8539, 'longitude' => 108.8856],
];

foreach ($stations as $data) {
    Station::updateOrCreate(
        ['code' => $data['code']],
        $data
    );
    echo "Updated: " . $data['name'] . "\n";
}

echo "\nAll stations updated with correct coordinates!\n";
