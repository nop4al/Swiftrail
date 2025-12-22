<?php

namespace App\Constants;

/**
 * Transit Constants - Konstanta untuk semua data transit
 */
class TransitConstants
{
    // Promo Data
    public const PROMOS = [
        [
            'id' => 1,
            'name' => 'Laboratorium Klinik PRAMITA',
            'label' => 'Diskon Spesial',
            'icon' => '🏥',
            'discount' => '25%',
            'shortDesc' => 'Diskon untuk pengguna SwiftRail',
            'image' => 'https://images.unsplash.com/photo-1576091160550-2173dba999ef?w=500&h=300&fit=crop',
            'description' => 'Dapatkan diskon spesial hingga 25% untuk semua layanan pemeriksaan kesehatan di Laboratorium Klinik PRAMITA. Promo ini berlaku untuk member SwiftRail yang aktif dan sudah terverifikasi.',
            'duration' => '15 Januari 2025',
            'terms' => [
                'Berlaku untuk member SwiftRail yang aktif',
                'Minimal pembelian tiket Rp 500.000',
                'Tidak dapat digabungkan dengan promo lain',
                'Diskon hanya berlaku untuk pemeriksaan laboratorium darah',
                'Promo tidak berlaku untuk layanan konsultasi dokter'
            ]
        ],
        [
            'id' => 2,
            'name' => 'IBIS YIA KULON PROGO',
            'label' => 'Diskon Flash',
            'icon' => '🏨',
            'discount' => '30%',
            'shortDesc' => 'Menginap lebih hemat',
            'image' => 'https://images.unsplash.com/photo-1631049307038-da0ec54d8c90?w=500&h=300&fit=crop',
            'description' => 'Nikmati diskon flash hingga 30% untuk menginap di IBIS YIA KULON PROGO di Yogyakarta. Hotel bintang 4 dengan fasilitas lengkap dan lokasi strategis.',
            'duration' => '31 Desember 2024',
            'terms' => [
                'Berlaku untuk pemesanan 3 malam atau lebih',
                'Check-in minimal 7 hari setelah pembelian tiket',
                'Hanya tersedia untuk tipe kamar standar',
                'Tidak termasuk sarapan premium',
                'Diskon tidak dapat ditransfer ke orang lain'
            ]
        ],
        [
            'id' => 3,
            'name' => 'Makanan di TERMINAL',
            'label' => 'Promo Gratis',
            'icon' => '🍽️',
            'discount' => 'Gratis',
            'shortDesc' => 'Voucher makan senilai Rp50k',
            'image' => 'https://images.unsplash.com/photo-1555939594-58d7cb561a1f?w=500&h=300&fit=crop',
            'description' => 'Dapatkan voucher makan gratis senilai Rp 50.000 untuk digunakan di  makanan di terminal keberangkatan Anda. Voucher dapat ditukar dengan berbagai pilihan kuliner.',
            'duration' => '30 Januari 2025',
            'terms' => [
                'Voucher berlaku untuk 1 kali transaksi',
                'Berlaku di semua outlet food court terminal utama',
                'Tidak dapat ditukar dengan uang tunai',
                'Berlaku 2 jam sebelum keberangkatan kereta',
                'Saldo voucher tidak bisa dikembalikan jika lebih sedikit dari total pembelian'
            ]
        ],
        [
            'id' => 4,
            'name' => 'Cashback untuk Semua Perjalanan',
            'label' => 'Cashback',
            'icon' => '💰',
            'discount' => '50K',
            'shortDesc' => 'Cashback setiap pembelian',
            'image' => 'https://images.unsplash.com/photo-1579621970563-430f63602022?w=500&h=300&fit=crop',
            'description' => 'Setiap pembelian tiket kereta melalui aplikasi SwiftRail, Anda akan mendapatkan cashback hingga Rp 50.000 yang langsung masuk ke akun Anda.',
            'duration' => '28 Februari 2025',
            'terms' => [
                'Cashback otomatis dikreditkan 24 jam setelah keberangkatan',
                'Berlaku untuk semua jenis kereta (antar kota, lokal, KRL, LRT)',
                'Minimum pembelian tiket Rp 200.000',
                'Maksimal cashback Rp 50.000 per transaksi',
                'Cashback dapat digunakan untuk pembelian tiket berikutnya'
            ]
        ]
    ];

    // Destination Data
    public const DESTINATIONS = [
        [
            'id' => 1,
            'name' => 'Jakarta',
            'subtitle' => 'Ibu Kota Negara',
            'icon' => '🏙️',
            'image' => 'https://images.unsplash.com/photo-1625399579846-40eb8e8f36e4?w=500&h=400&fit=crop',
            'description' => 'Jakarta adalah jantung Indonesia yang penuh dengan kehidupan modern. Kota ini menawarkan berbagai atraksi wisata dari museum bersejarah, pusat perbelanjaan modern, hingga kuliner kelas dunia.',
            'highlights' => [
                'Monumen Nasional (Monas) - Ikon kemerdekaan Indonesia',
                'Kota Tua - Pusat perdagangan bersejarah dari zaman Belanda',
                'Taman Mini Indonesia Indah - Miniatur budaya seluruh nusantara',
                'Grand Indonesia & Senayan City - Pusat perbelanjaan terbesar'
            ],
            'bestTime' => 'Mei - September',
            'transport' => 'Stasiun Gambir & Stasiun Kota'
        ],
        [
            'id' => 2,
            'name' => 'Bandung',
            'subtitle' => 'Kota Bunga',
            'icon' => '🌺',
            'image' => 'https://images.unsplash.com/photo-1549144945-e39de59ee7ac?w=500&h=400&fit=crop',
            'description' => 'Bandung adalah kota yang terkenal dengan suasana sejuk dan pemandangan alam yang indah. Banyak tempat wisata keluarga, outlet fashion, dan kuliner yang menarik.',
            'highlights' => [
                'Tangkuban Perahu - Gunung berapi yang masih aktif',
                'Kebun Strawberry - Pengalaman petik buah langsung',
                'Kawah Putih - Danau kawah dengan air berwarna putih unik',
                'Factory Outlet - Belanja branded items dengan harga murah'
            ],
            'bestTime' => 'Maret - Oktober',
            'transport' => 'Stasiun Bandung'
        ],
        [
            'id' => 3,
            'name' => 'Yogyakarta',
            'subtitle' => 'Warisan Budaya',
            'icon' => '🏛️',
            'image' => 'https://images.unsplash.com/photo-1552733266-e831426f2e2f?w=500&h=400&fit=crop',
            'description' => 'Yogyakarta adalah pusat seni dan budaya Jawa yang kaya akan sejarah. Kota ini menyimpan banyak candi bersejarah dan tradisi budaya yang masih kuat.',
            'highlights' => [
                'Candi Borobudur - Candi Buddha terbesar di dunia',
                'Candi Prambanan - Candi Hindu dengan arsitektur megah',
                'Keraton Yogyakarta - Istana kesultanan yang masih aktif',
                'Malioboro Street - Jalan perdagangan tradisional yang terkenal'
            ],
            'bestTime' => 'Juni - September',
            'transport' => 'Stasiun Yogyakarta'
        ],
        [
            'id' => 4,
            'name' => 'Surabaya',
            'subtitle' => 'Kota Pahlawan',
            'icon' => '⚔️',
            'image' => 'https://images.unsplash.com/photo-1595121603385-f2e4adf32e6b?w=500&h=400&fit=crop',
            'description' => 'Surabaya adalah kota pahlawan dengan sejarah perjuangan yang mendalam. Kota ini memiliki daya tarik budaya, museum bersejarah, dan kuliner khas yang lezat.',
            'highlights' => [
                'Monumen Kapal Selam - Museum kapal selam pertama di Asia',
                'Jembatan Suramadu - Jembatan gantung terpanjang Indonesia',
                'Taman Bungkul - Ruang publik interaktif dan edukatif',
                'Makanan Rawon & Soto Ayam - Kuliner khas Surabaya yang enak'
            ],
            'bestTime' => 'April - Oktober',
            'transport' => 'Stasiun Surabaya Gubeng'
        ]
    ];

    // Station Data
    public const STATIONS = [
        ['code' => 'GMB', 'name' => 'Gambir', 'city' => 'Jakarta'],
        ['code' => 'CRB', 'name' => 'Cirebon', 'city' => 'Cirebon'],
        ['code' => 'PKL', 'name' => 'Pekalongan', 'city' => 'Pekalongan'],
        ['code' => 'SMG', 'name' => 'Semarang Tawang', 'city' => 'Semarang'],
        ['code' => 'BJN', 'name' => 'Bojonegoro', 'city' => 'Bojonegoro'],
        ['code' => 'SBY', 'name' => 'Surabaya Pasar Turi', 'city' => 'Surabaya'],
    ];

    // Train Data
    public const TRAINS = [
        [
            'id' => 1,
            'name' => 'Argo Bromo',
            'number' => 'A1',
            'departure' => '06:00',
            'arrival' => '12:30',
            'duration' => '6h 30m',
            'price' => 350000,
            'seats_available' => 45,
            'class' => 'Eksekutif',
            'facilities' => ['WiFi gratis', 'Makanan & minuman', 'Kabin nyaman', 'Power outlet']
        ],
        [
            'id' => 2,
            'name' => 'Argo Raih Prabu',
            'number' => 'A2',
            'departure' => '08:00',
            'arrival' => '14:15',
            'duration' => '6h 15m',
            'price' => 320000,
            'seats_available' => 32,
            'class' => 'Bisnis',
            'facilities' => ['WiFi', 'Makanan', 'Kursi reclining']
        ],
        [
            'id' => 3,
            'name' => 'Ekspres Brantas',
            'number' => 'A3',
            'departure' => '14:00',
            'arrival' => '20:30',
            'duration' => '6h 30m',
            'price' => 280000,
            'seats_available' => 58,
            'class' => 'Ekonomi',
            'facilities' => ['Kursi nyaman', 'AC']
        ],
        [
            'id' => 4,
            'name' => 'Argo Wilis',
            'number' => 'A4',
            'departure' => '16:30',
            'arrival' => '23:00',
            'duration' => '6h 30m',
            'price' => 300000,
            'seats_available' => 12,
            'class' => 'Eksekutif',
            'facilities' => ['WiFi gratis', 'Makanan premium', 'Kabin deluxe', 'Power outlet', 'Entertainment']
        ]
    ];

    // Application Info
    public const APP_INFO = [
        'app_name' => 'SwiftRail',
        'app_subtitle' => 'SUPER APP',
        'version' => '1.0.0',
        'description' => 'Jelajahi Indonesia dengan mudah melalui satu aplikasi lengkap untuk semua kebutuhan transportasi',
        'features' => [
            'Kereta Api Antar Kota',
            'Kereta Lokal',
            'KRL',
            'LRT',
            'Layanan Bandara'
        ]
    ];
}
