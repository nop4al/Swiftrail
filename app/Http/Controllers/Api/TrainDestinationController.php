<?php

namespace App\Http\Controllers\Api;

use App\Models\Train;
use App\Models\TrainStop;
use App\Constants\TransitConstants;

class TrainDestinationController extends ApiBaseController
{
    /**
     * Get destinasi recommendations untuk sebuah kereta
     * GET /api/v1/trains/{train_code}/destinations
     */
    public function getDestinationsByTrain($trainCode)
    {
        try {
            // Cari train berdasarkan code
            $train = Train::where('code', $trainCode)->first();
            if (!$train) {
                return $this->error('Train not found', null, 404);
            }

            // Get semua stop untuk train ini, diorder berdasarkan sequence
            $stops = TrainStop::where('train_id', $train->id)
                ->with('station')
                ->orderBy('sequence')
                ->get();

            if ($stops->isEmpty()) {
                return $this->error('No stops found for this train', null, 404);
            }

            // Mapping kota dengan destinasi
            $cityDestinationMap = [
                'Jakarta' => 'Jakarta',
                'Gambir' => 'Jakarta',
                'Bandung' => 'Bandung',
                'Semarang' => 'Yogyakarta',
                'Yogyakarta' => 'Yogyakarta',
                'Surabaya' => 'Surabaya',
                'Pekalongan' => 'Yogyakarta',
                'Cirebon' => 'Bandung',
                'Bojonegoro' => 'Surabaya'
            ];

            // Kumpulkan destinasi unik yang dilalui rute
            $destinationNames = [];
            foreach ($stops as $stop) {
                if ($stop->station) {
                    $city = $this->extractCityFromStationName($stop->station->name);
                    if ($city && isset($cityDestinationMap[$city])) {
                        $destName = $cityDestinationMap[$city];
                        if (!in_array($destName, $destinationNames)) {
                            $destinationNames[] = $destName;
                        }
                    }
                }
            }

            // Get destinasi details dari constants
            $destinations = [];
            foreach (TransitConstants::DESTINATIONS as $dest) {
                if (in_array($dest['name'], $destinationNames)) {
                    // Tambahkan tourism recommendation untuk destinasi ini
                    $dest['tourism'] = $this->getTourismRecommendations($dest['name']);
                    $destinations[] = $dest;
                }
            }

            return $this->success([
                'train' => [
                    'code' => $train->code,
                    'name' => $train->name,
                    'type' => $train->type
                ],
                'route_destinations' => $destinations,
                'stations_count' => $stops->count()
            ], 'Destinations retrieved successfully');

        } catch (\Exception $e) {
            return $this->error('Failed to retrieve destinations: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get destinasi recommendations untuk setiap station di rute
     * GET /api/v1/trains/{train_code}/destinations/by-station
     */
    public function getDestinationsByStation($trainCode)
    {
        try {
            // Cari train berdasarkan code
            $train = Train::where('code', $trainCode)->first();
            if (!$train) {
                return $this->error('Train not found', null, 404);
            }

            // Get semua stop untuk train ini
            $stops = TrainStop::where('train_id', $train->id)
                ->with('station')
                ->orderBy('sequence')
                ->get();

            if ($stops->isEmpty()) {
                return $this->error('No stops found for this train', null, 404);
            }

            // Map setiap station dengan destinasi terdekat dan wisata
            $stationsWithDestinations = [];
            foreach ($stops as $stop) {
                if ($stop->station) {
                    $city = $this->extractCityFromStationName($stop->station->name);
                    $destName = $this->mapCityToDestination($city);

                    if ($destName) {
                        $destination = $this->getDestinationByName($destName);
                        if ($destination) {
                            $stationsWithDestinations[] = [
                                'sequence' => $stop->sequence,
                                'station' => [
                                    'id' => $stop->station->id,
                                    'code' => $stop->station->code,
                                    'name' => $stop->station->name,
                                    'city' => $stop->station->city,
                                    'latitude' => $stop->station->latitude,
                                    'longitude' => $stop->station->longitude,
                                    'arrival_time' => $stop->arrival_time,
                                    'departure_time' => $stop->departure_time
                                ],
                                'destination' => [
                                    'id' => $destination['id'],
                                    'name' => $destination['name'],
                                    'subtitle' => $destination['subtitle'],
                                    'description' => $destination['description'],
                                    'highlights' => $destination['highlights'],
                                    'bestTime' => $destination['bestTime']
                                ],
                                'tourism' => $this->getTourismRecommendations($destName)
                            ];
                        }
                    }
                }
            }

            return $this->success([
                'train' => [
                    'code' => $train->code,
                    'name' => $train->name,
                    'type' => $train->type
                ],
                'stations_with_destinations' => $stationsWithDestinations
            ], 'Station destinations retrieved successfully');

        } catch (\Exception $e) {
            return $this->error('Failed to retrieve station destinations: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Extract city name dari station name
     */
    private function extractCityFromStationName($stationName): ?string
    {
        // Remove common station suffixes
        $cityName = preg_replace('/\s+(Pasar\s+\w+|Kota|Gubeng|Pusat|Sentosa|Madiun|Jaya|Selatan|Utara|Timur|Barat)$/i', '', $stationName);
        $cityName = trim($cityName);

        // Specific mappings
        $mappings = [
            'Gambir' => 'Gambir',
            'Bandung' => 'Bandung',
            'Semarang' => 'Semarang',
            'Yogyakarta' => 'Yogyakarta',
            'Surabaya' => 'Surabaya',
            'Pekalongan' => 'Pekalongan',
            'Cirebon' => 'Cirebon',
            'Bojonegoro' => 'Bojonegoro',
            'Jakarta' => 'Jakarta'
        ];

        foreach ($mappings as $key => $value) {
            if (stripos($cityName, $key) !== false) {
                return $key;
            }
        }

        return $cityName;
    }

    /**
     * Map city ke destination name
     */
    private function mapCityToDestination($city): ?string
    {
        $map = [
            'Jakarta' => 'Jakarta',
            'Gambir' => 'Jakarta',
            'Bandung' => 'Bandung',
            'Semarang' => 'Yogyakarta',
            'Yogyakarta' => 'Yogyakarta',
            'Surabaya' => 'Surabaya',
            'Pekalongan' => 'Yogyakarta',
            'Cirebon' => 'Bandung',
            'Bojonegoro' => 'Surabaya'
        ];

        return $map[$city] ?? null;
    }

    /**
     * Get destination dari constants by name
     */
    private function getDestinationByName($name): ?array
    {
        foreach (TransitConstants::DESTINATIONS as $dest) {
            if (strtolower($dest['name']) === strtolower($name)) {
                return $dest;
            }
        }
        return null;
    }

    /**
     * Get tourism recommendations untuk sebuah kota/destinasi
     */
    private function getTourismRecommendations($destinationName): array
    {
        $tourismData = [
            'Jakarta' => [
                [
                    'name' => 'Monumen Nasional (Monas)',
                    'image' => 'https://images.unsplash.com/photo-1585268341965-ab7cc9cdc34f?w=500&h=300&fit=crop',
                    'category' => 'Monumen Bersejarah',
                    'description' => 'Ikon kemerdekaan Indonesia yang megah dengan ketinggian 132 meter. Dari atas, nikmati pemandangan 360 derajat kota Jakarta.',
                    'rating' => 4.6,
                    'reviews' => 2840,
                    'address' => 'Jl. Medan Merdeka Barat, Jakarta Pusat',
                    'hours' => '09:00 - 16:00 (Senin - Minggu)',
                    'price' => 'Rp 15.000 - Rp 30.000'
                ],
                [
                    'name' => 'Kota Tua Jakarta',
                    'image' => 'https://images.unsplash.com/photo-1548013146-72479768bada?w=500&h=300&fit=crop',
                    'category' => 'Wisata Sejarah',
                    'description' => 'Kawasan bersejarah dengan arsitektur kolonial Belanda. Tempat sempurna untuk fotografi dan menjelajahi sejarah Jakarta.',
                    'rating' => 4.5,
                    'reviews' => 3120,
                    'address' => 'Jl. Thamrin, Jakarta Pusat',
                    'hours' => '24 Jam',
                    'price' => 'Gratis'
                ],
                [
                    'name' => 'Taman Mini Indonesia Indah',
                    'image' => 'https://images.unsplash.com/photo-1517457373614-b7152f800fd1?w=500&h=300&fit=crop',
                    'category' => 'Taman Hiburan',
                    'description' => 'Miniatur budaya seluruh Indonesia dalam satu taman. Tempat edukatif dan menyenangkan untuk keluarga.',
                    'rating' => 4.3,
                    'reviews' => 1950,
                    'address' => 'Jl. Taman Mini, Jakarta Timur',
                    'hours' => '09:00 - 17:00',
                    'price' => 'Rp 25.000 - Rp 100.000'
                ]
            ],
            'Bandung' => [
                [
                    'name' => 'Tangkuban Perahu',
                    'image' => 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=500&h=300&fit=crop',
                    'category' => 'Alam & Gunung',
                    'description' => 'Gunung berapi aktif dengan pemandangan spektakuler. Bisa naik ke puncak dan melihat kawah yang masih berasap.',
                    'rating' => 4.7,
                    'reviews' => 4250,
                    'address' => 'Kp. Tangkuban Perahu, Bandung Utara',
                    'hours' => '07:00 - 17:00',
                    'price' => 'Rp 20.000 - Rp 50.000'
                ],
                [
                    'name' => 'Kawah Putih',
                    'image' => 'https://images.unsplash.com/photo-1551632440-68b02f2ddb7a?w=500&h=300&fit=crop',
                    'category' => 'Alam Unik',
                    'description' => 'Danau kawah dengan air berwarna putih unik. Pemandangan yang eksotis dan tempat yang sempurna untuk foto.',
                    'rating' => 4.5,
                    'reviews' => 3680,
                    'address' => 'Ciwidey, Bandung Selatan',
                    'hours' => '07:00 - 17:00',
                    'price' => 'Rp 15.000 - Rp 45.000'
                ],
                [
                    'name' => 'Strawberry Farm',
                    'image' => 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=500&h=300&fit=crop',
                    'category' => 'Agro Wisata',
                    'description' => 'Kebun strawberry organik dimana Anda bisa petik buah langsung. Pengalaman unik dan seru untuk keluarga.',
                    'rating' => 4.4,
                    'reviews' => 2890,
                    'address' => 'Lembang, Bandung Utara',
                    'hours' => '08:00 - 17:00',
                    'price' => 'Rp 35.000 - Rp 75.000'
                ]
            ],
            'Yogyakarta' => [
                [
                    'name' => 'Candi Borobudur',
                    'image' => 'https://images.unsplash.com/photo-1552733266-e831426f2e2f?w=500&h=300&fit=crop',
                    'category' => 'Candi Bersejarah',
                    'description' => 'Candi Buddha terbesar di dunia dengan 506 patung Buddha. Tempat spiritual yang megah dengan pemandangan indah.',
                    'rating' => 4.8,
                    'reviews' => 6420,
                    'address' => 'Borobudur, Magelang',
                    'hours' => '06:00 - 17:00',
                    'price' => 'Rp 50.000 - Rp 375.000'
                ],
                [
                    'name' => 'Candi Prambanan',
                    'image' => 'https://images.unsplash.com/photo-1508736267299-8d17ed34c909?w=500&h=300&fit=crop',
                    'category' => 'Candi Bersejarah',
                    'description' => 'Candi Hindu dengan arsitektur megah berarsitektur dengan 3 candi utama. UNESCO World Heritage Site yang memukau.',
                    'rating' => 4.7,
                    'reviews' => 5180,
                    'address' => 'Prambanan, Sleman',
                    'hours' => '06:00 - 18:00',
                    'price' => 'Rp 50.000 - Rp 325.000'
                ],
                [
                    'name' => 'Malioboro Street',
                    'image' => 'https://images.unsplash.com/photo-1559211615-cd4628902249?w=500&h=300&fit=crop',
                    'category' => 'Belanja & Kuliner',
                    'description' => 'Jalan perdagangan tradisional dengan ribuan toko souvenir, batik, dan kuliner lokal. Jantung komersial Yogyakarta.',
                    'rating' => 4.4,
                    'reviews' => 4920,
                    'address' => 'Jl. Malioboro, Yogyakarta',
                    'hours' => '09:00 - 18:00',
                    'price' => 'Gratis (Belanja sesuai budget)'
                ]
            ],
            'Surabaya' => [
                [
                    'name' => 'Museum Kapal Selam',
                    'image' => 'https://images.unsplash.com/photo-1595121603385-f2e4adf32e6b?w=500&h=300&fit=crop',
                    'category' => 'Museum Militer',
                    'description' => 'Kapal selam pertama di Asia yang bisa dimasuki. Pengalaman unik menjelajahi interior kapal bersejarah.',
                    'rating' => 4.6,
                    'reviews' => 3450,
                    'address' => 'Jl. Pemuda, Surabaya',
                    'hours' => '09:00 - 17:00',
                    'price' => 'Rp 40.000 - Rp 80.000'
                ],
                [
                    'name' => 'Jembatan Suramadu',
                    'image' => 'https://images.unsplash.com/photo-1513531334635-cd301937a598?w=500&h=300&fit=crop',
                    'category' => 'Infrastruktur Ikonik',
                    'description' => 'Jembatan gantung terpanjang Indonesia yang menghubungkan Surabaya dan Madura. Pemandangan spektakuler dari atas.',
                    'rating' => 4.5,
                    'reviews' => 2890,
                    'address' => 'Jembatan Suramadu, Surabaya',
                    'hours' => '24 Jam',
                    'price' => 'Rp 20.000 (Kendaraan)'
                ],
                [
                    'name' => 'Taman Bungkul',
                    'image' => 'https://images.unsplash.com/photo-1511632765486-a01980e01a18?w=500&h=300&fit=crop',
                    'category' => 'Taman Publik',
                    'description' => 'Ruang publik interaktif dan edukatif dengan fasilitas lengkap. Tempat sempurna untuk bersantai dan bermain keluarga.',
                    'rating' => 4.3,
                    'reviews' => 2120,
                    'address' => 'Jl. Bungkul, Surabaya',
                    'hours' => '08:00 - 21:00',
                    'price' => 'Gratis'
                ]
            ]
        ];

        return $tourismData[$destinationName] ?? [];
    }
}
