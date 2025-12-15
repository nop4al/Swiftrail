<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\User;
use App\Models\Schedule;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BookingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Pastikan ada user dan schedule
        $user = User::first();
        $schedule = Schedule::first();

        if (!$user || !$schedule) {
            echo "Pastikan user dan schedule sudah ada di database\n";
            return;
        }

        // Create sample bookings
        Booking::create([
            'booking_code' => 'SWR-202501-AB5001',
            'ticket_number' => 'TKT-20250001',
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
            'passenger_name' => 'Budi Santoso',
            'nik' => '3175081990101001',
            'passenger_type' => 'Dewasa',
            'seat_number' => '01A',
            'class' => 'Eksekutif',
            'price' => 450000,
            'qr_code' => 'SWR-202501-AB5001|TKT-20250001|01A|14:30|20-01-2025',
            'status' => 'Aktif'
        ]);

        Booking::create([
            'booking_code' => 'SWR-202501-EX3002',
            'ticket_number' => 'TKT-20250002',
            'user_id' => $user->id,
            'schedule_id' => $schedule->id,
            'passenger_name' => 'Siti Nurhaliza',
            'nik' => '3201892000050002',
            'passenger_type' => 'Dewasa',
            'seat_number' => '12B',
            'class' => 'Bisnis',
            'price' => 280000,
            'qr_code' => 'SWR-202501-EX3002|TKT-20250002|12B|06:00|10-12-2024',
            'status' => 'Selesai'
        ]);
    }
}
