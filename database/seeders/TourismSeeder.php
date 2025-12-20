<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TourismSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tourismData = [
            // Gambir Station (Jakarta)
            1 => [
                [
                    'name' => 'Kota Tua Jakarta',
                    'category' => 'Sejarah & Budaya',
                    'description' => 'Kawasan bersejarah dengan bangunan kolonial yang indah, museum, dan restoran heritage.',
                    'image' => 'https://images.unsplash.com/photo-1606445832811-f6f0cfb66810?w=400',
                    'rating' => 4.5,
                    'reviews' => 2850,
                    'address' => 'Jl. Taman Fatahillah No.1, Jakarta Barat',
                    'hours' => '09:00 - 22:00',
                    'phone' => '(021) 692-9042',
                    'website' => 'kotatuajakarta.com',
                    'latitude' => -6.1355,
                    'longitude' => 106.8067,
                ],
                [
                    'name' => 'Monumen Nasional (Monas)',
                    'category' => 'Landmark',
                    'description' => 'Monumen ikonik Indonesia dengan tinggi 132 meter, museum di dalam, dan pemandangan kota dari atas.',
                    'image' => 'https://images.unsplash.com/photo-1537225228614-b4fad34a8b5f?w=400',
                    'rating' => 4.6,
                    'reviews' => 4200,
                    'address' => 'Jl. Merdeka Utara, Jakarta Pusat',
                    'hours' => '08:00 - 16:00',
                    'phone' => '(021) 381-5017',
                    'website' => 'monas.go.id',
                    'latitude' => -6.1751,
                    'longitude' => 106.8249,
                ],
                [
                    'name' => 'Museum Nasional Indonesia',
                    'category' => 'Museum',
                    'description' => 'Museum terbesar di Asia Tenggara dengan koleksi arkeologi, etnografi, dan sejarah Indonesia.',
                    'image' => 'https://images.unsplash.com/photo-1517990979637-7f23ee89ae78?w=400',
                    'rating' => 4.4,
                    'reviews' => 1950,
                    'address' => 'Jl. Merdeka Barat No.12, Jakarta Pusat',
                    'hours' => '08:00 - 16:00',
                    'phone' => '(021) 381-5544',
                    'website' => 'nationalmuseum.or.id',
                    'latitude' => -6.1913,
                    'longitude' => 106.8060,
                ],
            ],
            // Cirebon Station
            2 => [
                [
                    'name' => 'Keraton Cirebon (Istana Kesepuhan)',
                    'category' => 'Sejarah & Budaya',
                    'description' => 'Istana bersejarah dengan arsitektur Cirebon yang unik, menyatukan gaya Jawa, Islam, dan Belanda.',
                    'image' => 'https://images.unsplash.com/photo-1559027615-cd2628902d4a?w=400',
                    'rating' => 4.7,
                    'reviews' => 1650,
                    'address' => 'Jl. Lemahwungkuk, Cirebon',
                    'hours' => '09:00 - 16:00',
                    'phone' => '(0231) 237-321',
                    'website' => 'keratoncirebon.com',
                    'latitude' => -6.7050,
                    'longitude' => 108.4763,
                ],
                [
                    'name' => 'Batik Cirebon',
                    'category' => 'Seni & Kerajinan',
                    'description' => 'Pusat produksi batik tradisional Cirebon dengan workshop dan galeri penjualan langsung.',
                    'image' => 'https://images.unsplash.com/photo-1578926314433-3e95c58a85d7?w=400',
                    'rating' => 4.3,
                    'reviews' => 890,
                    'address' => 'Jl. Bahagia No.45, Cirebon',
                    'hours' => '08:00 - 17:00',
                    'phone' => '(0231) 209-876',
                    'website' => 'batikcirebon.co.id',
                    'latitude' => -6.7081,
                    'longitude' => 108.4852,
                ],
            ],
            // Pekalongan Station
            3 => [
                [
                    'name' => 'Museum Batik Pekalongan',
                    'category' => 'Museum & Seni',
                    'description' => 'Museum khusus batik dengan koleksi lengkap sejarah batik dari berbagai daerah di Indonesia.',
                    'image' => 'https://images.unsplash.com/photo-1563461661033-07b43a285d39?w=400',
                    'rating' => 4.5,
                    'reviews' => 1200,
                    'address' => 'Jl. Oey Hay Djoen No.6, Pekalongan',
                    'hours' => '09:00 - 17:00',
                    'phone' => '(0285) 413-500',
                    'website' => 'museumbatik.pekalongan.go.id',
                    'latitude' => -6.8884,
                    'longitude' => 109.6769,
                ],
                [
                    'name' => 'Pantai Pasir Kencana',
                    'category' => 'Pantai & Rekreasi',
                    'description' => 'Pantai indah dengan pasir putih, cocok untuk bersantai dan bermain air di pinggir Laut Jawa.',
                    'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=400',
                    'rating' => 4.2,
                    'reviews' => 1450,
                    'address' => 'Jl. Pantai Utama, Pekalongan',
                    'hours' => '06:00 - 18:00',
                    'phone' => '(0285) 421-234',
                    'website' => 'pantaipekalongan.com',
                    'latitude' => -6.8962,
                    'longitude' => 109.6910,
                ],
            ],
            // Semarang Tawang Station
            4 => [
                [
                    'name' => 'Lawang Sewu',
                    'category' => 'Sejarah & Budaya',
                    'description' => 'Bangunan bersejarah bergaya Eropa dengan ribuan pintu dan jendela, kini menjadi destinasi wisata heritage.',
                    'image' => 'https://images.unsplash.com/photo-1518895949257-7621c3c786d7?w=400',
                    'rating' => 4.6,
                    'reviews' => 3200,
                    'address' => 'Jl. Pemuda No.118, Semarang',
                    'hours' => '08:00 - 17:00',
                    'phone' => '(024) 840-2755',
                    'website' => 'lawangsewu.com',
                    'latitude' => -7.0029,
                    'longitude' => 110.4209,
                ],
                [
                    'name' => 'Sam Poo Kong',
                    'category' => 'Tempat Ibadah & Budaya',
                    'description' => 'Klenteng tertua dan terbesar di Asia Tenggara dengan arsitektur tradisional Cina yang megah.',
                    'image' => 'https://images.unsplash.com/photo-1518707268779-3e88e0b9b9a1?w=400',
                    'rating' => 4.5,
                    'reviews' => 2100,
                    'address' => 'Jl. Simongan No.118, Semarang',
                    'hours' => '07:00 - 17:00',
                    'phone' => '(024) 743-2275',
                    'website' => 'sampookong.com',
                    'latitude' => -7.0314,
                    'longitude' => 110.3863,
                ],
                [
                    'name' => 'Taman Gajah Mada',
                    'category' => 'Taman & Rekreasi',
                    'description' => 'Taman kota yang indah dengan koleksi flora beragam, sempurna untuk jogging dan bermain keluarga.',
                    'image' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400',
                    'rating' => 4.1,
                    'reviews' => 1680,
                    'address' => 'Jl. Gajah Mada, Semarang',
                    'hours' => '06:00 - 18:00',
                    'phone' => '(024) 831-5467',
                    'website' => '',
                    'latitude' => -7.0154,
                    'longitude' => 110.4291,
                ],
            ],
            // Bojonegoro Station
            5 => [
                [
                    'name' => 'Museum Liyangan',
                    'category' => 'Museum & Budaya',
                    'description' => 'Museum yang menampilkan sejarah kerajaan Mataram dan budaya lokal Jawa Timur.',
                    'image' => 'https://images.unsplash.com/photo-1459749411175-04bf5292ceea?w=400',
                    'rating' => 4.3,
                    'reviews' => 560,
                    'address' => 'Jl. Raya Bojonegoro, Bojonegoro',
                    'hours' => '09:00 - 16:00',
                    'phone' => '(0353) 881-234',
                    'website' => '',
                    'latitude' => -7.1610,
                    'longitude' => 111.8826,
                ],
                [
                    'name' => 'Taman Hutan Raya Bojonegoro',
                    'category' => 'Alam & Rekreasi',
                    'description' => 'Hutan lindung dengan fasilitas rekreasi, hiking trails, dan viewpoint dengan pemandangan alam yang menakjubkan.',
                    'image' => 'https://images.unsplash.com/photo-1441974231531-c6227db76b6e?w=400',
                    'rating' => 4.4,
                    'reviews' => 920,
                    'address' => 'Jl. Pesona Alam, Bojonegoro',
                    'hours' => '07:00 - 17:00',
                    'phone' => '(0353) 887-654',
                    'website' => 'tamanbojonegoro.go.id',
                    'latitude' => -7.1854,
                    'longitude' => 111.8542,
                ],
            ],
            // Surabaya Pasar Turi Station
            6 => [
                [
                    'name' => 'Jembatan Merah',
                    'category' => 'Landmark Sejarah',
                    'description' => 'Jembatan ikonik bersejarah yang menjadi simbol perlawanan dan keberanian rakyat Surabaya.',
                    'image' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400',
                    'rating' => 4.4,
                    'reviews' => 2340,
                    'address' => 'Jl. Pemuda, Surabaya',
                    'hours' => '24/7',
                    'phone' => '(031) 503-4505',
                    'website' => '',
                    'latitude' => -7.2420,
                    'longitude' => 112.7381,
                ],
                [
                    'name' => 'Museum House of Sampoerna',
                    'category' => 'Museum & Budaya',
                    'description' => 'Rumah bersejarah dengan museum tentang sejarah industri rokok dan kehidupan di era kolonial Belanda.',
                    'image' => 'https://images.unsplash.com/photo-1524995997946-a1c2e315a42f?w=400',
                    'rating' => 4.7,
                    'reviews' => 2890,
                    'address' => 'Jl. Tunjungan No.6-8, Surabaya',
                    'hours' => '10:00 - 17:00',
                    'phone' => '(031) 545-7555',
                    'website' => 'houseofsampoerna.com',
                    'latitude' => -7.2521,
                    'longitude' => 112.7429,
                ],
                [
                    'name' => 'Taman Bungkul',
                    'category' => 'Taman & Rekreasi',
                    'description' => 'Taman favorit warga Surabaya dengan area hijau yang luas, spot foto Instagram, dan fasilitas playground.',
                    'image' => 'https://images.unsplash.com/photo-1517457373614-b7152f800fd1?w=400',
                    'rating' => 4.3,
                    'reviews' => 1750,
                    'address' => 'Jl. Bungkul, Surabaya',
                    'hours' => '06:00 - 18:00',
                    'phone' => '(031) 605-2233',
                    'website' => 'taman-bungkul.id',
                    'latitude' => -7.2874,
                    'longitude' => 112.7321,
                ],
            ],
        ];

        foreach ($tourismData as $stationId => $attractions) {
            foreach ($attractions as $attraction) {
                DB::table('tourism')->insert([
                    'station_id' => $stationId,
                    'name' => $attraction['name'],
                    'category' => $attraction['category'],
                    'description' => $attraction['description'],
                    'image' => $attraction['image'],
                    'rating' => $attraction['rating'],
                    'reviews' => $attraction['reviews'],
                    'address' => $attraction['address'],
                    'hours' => $attraction['hours'],
                    'phone' => $attraction['phone'],
                    'website' => $attraction['website'],
                    'latitude' => $attraction['latitude'],
                    'longitude' => $attraction['longitude'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
