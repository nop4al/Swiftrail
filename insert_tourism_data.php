<?php
require __DIR__ . '/vendor/autoload.php';

$app = require __DIR__ . '/bootstrap/app.php';

$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Station;
use App\Models\Tourism;

// Data wisata untuk setiap stasiun
$tourismData = [
    'GMB' => [ // Gambir - Jakarta
        [
            'name' => 'Kota Tua Jakarta',
            'category' => 'Sejarah',
            'description' => 'Kawasan bersejarah pusat kota Jakarta dengan arsitektur kolonial Belanda yang indah.',
            'rating' => 4.5,
            'reviews' => 1250,
            'address' => 'Jl. Thamrin, Jakarta Pusat',
            'hours' => '09:00 - 17:00',
            'phone' => '021-3857108',
            'website' => 'kotatuajakarta.com',
            'image' => 'https://images.unsplash.com/photo-1602088113235-229c19758e9f?w=400&h=300&fit=crop'
        ],
        [
            'name' => 'Masjid Istiqlal',
            'category' => 'Religi',
            'description' => 'Masjid terbesar di Indonesia dengan arsitektur modern yang memukau.',
            'rating' => 4.6,
            'reviews' => 890,
            'address' => 'Jl. Veteran, Jakarta Pusat',
            'hours' => 'Buka 24 Jam',
            'phone' => '021-3924040',
            'website' => 'istiqlal.org',
            'image' => 'https://images.unsplash.com/photo-1564241144-cac8dff8fdc0?w=400&h=300&fit=crop'
        ],
    ],
    'CBY' => [ // Cirebon
        [
            'name' => 'Keraton Kasepuhan Cirebon',
            'category' => 'Sejarah',
            'description' => 'Istana kerajaan Cirebon yang masih berdiri dengan arsitektur unik perpaduan budaya.',
            'rating' => 4.7,
            'reviews' => 650,
            'address' => 'Jl. Kasepuhan, Cirebon',
            'hours' => '08:00 - 16:00',
            'phone' => '0231-200434',
            'website' => 'keraton-kasepuhan.com',
            'image' => 'https://images.unsplash.com/photo-1548013146-72d1d3a6ba29?w=400&h=300&fit=crop'
        ],
        [
            'name' => 'Pantai Cirebon',
            'category' => 'Pantai',
            'description' => 'Pantai indah dengan pasir putih dan ombak yang cocok untuk wisata keluarga.',
            'rating' => 4.2,
            'reviews' => 520,
            'address' => 'Jl. Pantai, Cirebon',
            'hours' => '06:00 - 18:00',
            'phone' => '0231-205555',
            'website' => 'pantaicirebon.id',
            'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=400&h=300&fit=crop'
        ],
    ],
    'PKL' => [ // Pekalongan
        [
            'name' => 'Batik Pekalongan Museum',
            'category' => 'Seni & Budaya',
            'description' => 'Museum batik dengan koleksi batik terlengkap dan galeri produksi batik tradisional.',
            'rating' => 4.4,
            'reviews' => 480,
            'address' => 'Jl. Gatot Subroto, Pekalongan',
            'hours' => '09:00 - 16:00',
            'phone' => '0285-412575',
            'website' => 'batikmuseum.id',
            'image' => 'https://images.unsplash.com/photo-1578926078328-123f5474f1e5?w=400&h=300&fit=crop'
        ],
        [
            'name' => 'Pasir Kencana Beach',
            'category' => 'Pantai',
            'description' => 'Pantai eksotis dengan pemandangan sunset yang spektakuler dan fasilitas lengkap.',
            'rating' => 4.3,
            'reviews' => 350,
            'address' => 'Jl. Pantai Utama, Pekalongan',
            'hours' => '07:00 - 19:00',
            'phone' => '0285-421111',
            'website' => 'pasirkencana.com',
            'image' => 'https://images.unsplash.com/photo-1505142468610-359e7d316be0?w=400&h=300&fit=crop'
        ],
    ],
    'SMG' => [ // Semarang
        [
            'name' => 'Candi Borobudur',
            'category' => 'Sejarah',
            'description' => 'Kompleks candi Budhis terbesar di dunia dengan arsitektur yang menakjubkan dan pemandangan alam yang menawan.',
            'rating' => 4.8,
            'reviews' => 2150,
            'address' => 'Jl. Borobudur, Magelang',
            'hours' => '06:00 - 17:00',
            'phone' => '0293-588252',
            'website' => 'borobudurtemple.com',
            'image' => 'https://images.unsplash.com/photo-1464207687429-7505649dae38?w=400&h=300&fit=crop'
        ],
        [
            'name' => 'Taman Laut Jepara',
            'category' => 'Pantai',
            'description' => 'Taman laut dengan kekayaan terumbu karang dan ikan tropis yang melimpah untuk diving dan snorkeling.',
            'rating' => 4.5,
            'reviews' => 780,
            'address' => 'Jl. Pantai Tanjung, Jepara',
            'hours' => '08:00 - 17:00',
            'phone' => '0291-591024',
            'website' => 'tamanlautjepara.com',
            'image' => 'https://images.unsplash.com/photo-1559827260-dc66d52bef19?w=400&h=300&fit=crop'
        ],
    ],
    'BJN' => [ // Bojonegoro
        [
            'name' => 'Taman Rekreasi Ledok Tukang',
            'category' => 'Rekreasi',
            'description' => 'Taman rekreasi dengan kolam renang, kolam ikan, dan fasilitas bermain untuk anak-anak.',
            'rating' => 4.1,
            'reviews' => 290,
            'address' => 'Jl. Ledok Tukang, Bojonegoro',
            'hours' => '09:00 - 18:00',
            'phone' => '0353-881234',
            'website' => 'tamanledok.com',
            'image' => 'https://images.unsplash.com/photo-1566073771259-6a8506099945?w=400&h=300&fit=crop'
        ],
        [
            'name' => 'Makam Sunan Drajat',
            'category' => 'Religi',
            'description' => 'Makam wali sunan yang dihormati dengan kompleks bangunan bersejarah dan spiritual.',
            'rating' => 4.4,
            'reviews' => 340,
            'address' => 'Jl. Makam Sunan Drajat, Bojonegoro',
            'hours' => 'Buka 24 Jam',
            'phone' => '0353-891567',
            'website' => 'sunandrajat.id',
            'image' => 'https://images.unsplash.com/photo-1579224309935-d5fab922e0fa?w=400&h=300&fit=crop'
        ],
    ],
    'SBY-P' => [ // Surabaya Pasar Turi
        [
            'name' => 'Jembatan Merah Surabaya',
            'category' => 'Sejarah',
            'description' => 'Jembatan bersejarah yang menjadi simbol perlawanan melawan penjajah Belanda.',
            'rating' => 4.6,
            'reviews' => 1100,
            'address' => 'Jl. Tembaan, Surabaya',
            'hours' => '24 Jam',
            'phone' => '031-5343111',
            'website' => 'jembatanmerah.id',
            'image' => 'https://images.unsplash.com/photo-1599946347371-ac3edf25773e?w=400&h=300&fit=crop'
        ],
        [
            'name' => 'Taman Bungkul',
            'category' => 'Taman',
            'description' => 'Taman modern dengan fasilitas olahraga, area bermain, dan berbagai event budaya.',
            'rating' => 4.5,
            'reviews' => 890,
            'address' => 'Jl. Bungkul, Surabaya',
            'hours' => '06:00 - 22:00',
            'phone' => '031-5032222',
            'website' => 'tamanbungkul.com',
            'image' => 'https://images.unsplash.com/photo-1490079220285-f9b39646e059?w=400&h=300&fit=crop'
        ],
    ],
];

foreach ($tourismData as $stationCode => $places) {
    $station = Station::where('code', $stationCode)->first();
    
    if ($station) {
        // Clear existing tourism
        Tourism::where('station_id', $station->id)->delete();
        
        // Add new tourism data
        foreach ($places as $place) {
            Tourism::create([
                'station_id' => $station->id,
                'name' => $place['name'],
                'category' => $place['category'],
                'description' => $place['description'],
                'rating' => $place['rating'],
                'reviews' => $place['reviews'],
                'address' => $place['address'],
                'hours' => $place['hours'],
                'phone' => $place['phone'],
                'website' => $place['website'],
                'image' => $place['image'],
            ]);
            echo "Added tourism: {$place['name']} at {$station->name}\n";
        }
    } else {
        echo "Station not found: $stationCode\n";
    }
}

echo "\nAll tourism data inserted successfully!\n";
