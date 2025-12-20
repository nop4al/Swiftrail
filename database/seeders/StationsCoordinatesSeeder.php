<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Station;

class StationsCoordinatesSeeder extends Seeder
{
    public function run(): void
    {
        $stations = [
            ['name' => 'Surabaya Pasar Turi', 'code' => 'SBY', 'city' => 'Surabaya', 'lat' => -7.2485583, 'lng' => 112.7309194],
            ['name' => 'Bojonegoro',         'code' => 'BJN', 'city' => 'Bojonegoro', 'lat' => -7.1639889, 'lng' => 111.8866639],
            ['name' => 'Semarang Tawang',    'code' => 'SMG', 'city' => 'Semarang', 'lat' => -6.9644400, 'lng' => 110.4277800],
            ['name' => 'Pekalongan',         'code' => 'PKL', 'city' => 'Pekalongan', 'lat' => -6.8900000, 'lng' => 109.6640000],
            ['name' => 'Cirebon',            'code' => 'CRB', 'city' => 'Cirebon', 'lat' => -6.7052694, 'lng' => 108.5554417],
            ['name' => 'Gambir',             'code' => 'GMB', 'city' => 'Jakarta', 'lat' => -6.1775340, 'lng' => 106.8312720],
        ];

        foreach ($stations as $s) {
            Station::updateOrCreate(
                ['name' => $s['name']],
                [
                    'code' => $s['code'],
                    'city' => $s['city'],
                    'latitude' => $s['lat'],
                    'longitude' => $s['lng'],
                    'active' => true,
                ]
            );
        }
    }
}
