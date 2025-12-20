<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UpdateTrainScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds - Update with real schedule from CSV
     */
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear existing data
        DB::table('schedules')->truncate();
        DB::table('train_stops')->truncate();
        DB::table('trains')->truncate();

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Station IDs (from existing stations)
        $stationMap = [
            'Surabaya Pasar Turi' => 6,
            'Bojonegoro' => 5,
            'Semarang Tawang' => 4,
            'Pekalongan' => 3,
            'Cirebon' => 2,
            'Gambir' => 1,
        ];

        // KA1: Surabaya → Gambir (09:10 to 17:15)
        $ka1 = DB::table('trains')->insertGetId([
            'code' => 'ABA-KA1',
            'name' => 'Argo Bromo Anggrek KA1',
            'type' => 'Eksekutif',
            'capacity' => 120,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $ka1_stops = [
            ['station' => 'Surabaya Pasar Turi', 'seq' => 1, 'arrive' => null, 'depart' => '09:10:00'],
            ['station' => 'Bojonegoro', 'seq' => 2, 'arrive' => '10:16:00', 'depart' => '10:21:00'],
            ['station' => 'Semarang Tawang', 'seq' => 3, 'arrive' => '12:02:00', 'depart' => '12:17:00'],
            ['station' => 'Pekalongan', 'seq' => 4, 'arrive' => '13:17:00', 'depart' => '13:22:00'],
            ['station' => 'Cirebon', 'seq' => 5, 'arrive' => '14:40:00', 'depart' => '14:45:00'],
            ['station' => 'Gambir', 'seq' => 6, 'arrive' => '17:15:00', 'depart' => null],
        ];

        foreach ($ka1_stops as $stop) {
            DB::table('train_stops')->insert([
                'train_id' => $ka1,
                'station_id' => $stationMap[$stop['station']],
                'sequence' => $stop['seq'],
                'arrival_time' => $stop['arrive'],
                'departure_time' => $stop['depart'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // KA2: Gambir → Surabaya (08:20 to 16:25)
        $ka2 = DB::table('trains')->insertGetId([
            'code' => 'ABA-KA2',
            'name' => 'Argo Bromo Anggrek KA2',
            'type' => 'Eksekutif',
            'capacity' => 120,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $ka2_stops = [
            ['station' => 'Gambir', 'seq' => 1, 'arrive' => null, 'depart' => '08:20:00'],
            ['station' => 'Cirebon', 'seq' => 2, 'arrive' => '10:01:00', 'depart' => '10:46:00'],
            ['station' => 'Pekalongan', 'seq' => 3, 'arrive' => '12:02:00', 'depart' => '12:07:00'],
            ['station' => 'Semarang Tawang', 'seq' => 4, 'arrive' => '13:07:00', 'depart' => '13:12:00'],
            ['station' => 'Bojonegoro', 'seq' => 5, 'arrive' => '15:06:00', 'depart' => '15:11:00'],
            ['station' => 'Surabaya Pasar Turi', 'seq' => 6, 'arrive' => '16:25:00', 'depart' => null],
        ];

        foreach ($ka2_stops as $stop) {
            DB::table('train_stops')->insert([
                'train_id' => $ka2,
                'station_id' => $stationMap[$stop['station']],
                'sequence' => $stop['seq'],
                'arrival_time' => $stop['arrive'],
                'departure_time' => $stop['depart'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // KA3: Surabaya → Gambir (21:15 to 05:20 next day)
        $ka3 = DB::table('trains')->insertGetId([
            'code' => 'ABA-KA3',
            'name' => 'Argo Bromo Anggrek KA3',
            'type' => 'Eksekutif',
            'capacity' => 120,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $ka3_stops = [
            ['station' => 'Surabaya Pasar Turi', 'seq' => 1, 'arrive' => null, 'depart' => '21:15:00'],
            ['station' => 'Bojonegoro', 'seq' => 2, 'arrive' => '22:21:00', 'depart' => '22:26:00'],
            ['station' => 'Semarang Tawang', 'seq' => 3, 'arrive' => '00:17:00', 'depart' => '00:22:00'],
            ['station' => 'Pekalongan', 'seq' => 4, 'arrive' => '01:22:00', 'depart' => '01:27:00'],
            ['station' => 'Cirebon', 'seq' => 5, 'arrive' => '02:45:00', 'depart' => '02:50:00'],
            ['station' => 'Gambir', 'seq' => 6, 'arrive' => '05:20:00', 'depart' => null],
        ];

        foreach ($ka3_stops as $stop) {
            DB::table('train_stops')->insert([
                'train_id' => $ka3,
                'station_id' => $stationMap[$stop['station']],
                'sequence' => $stop['seq'],
                'arrival_time' => $stop['arrive'],
                'departure_time' => $stop['depart'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // KA4: Gambir → Surabaya (20:30 to 04:35 next day)
        $ka4 = DB::table('trains')->insertGetId([
            'code' => 'ABA-KA4',
            'name' => 'Argo Bromo Anggrek KA4',
            'type' => 'Eksekutif',
            'capacity' => 120,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $ka4_stops = [
            ['station' => 'Gambir', 'seq' => 1, 'arrive' => null, 'depart' => '20:30:00'],
            ['station' => 'Cirebon', 'seq' => 2, 'arrive' => '22:51:00', 'depart' => '22:56:00'],
            ['station' => 'Pekalongan', 'seq' => 3, 'arrive' => '00:12:00', 'depart' => '00:17:00'],
            ['station' => 'Semarang Tawang', 'seq' => 4, 'arrive' => '01:17:00', 'depart' => '01:22:00'],
            ['station' => 'Bojonegoro', 'seq' => 5, 'arrive' => '03:16:00', 'depart' => '03:21:00'],
            ['station' => 'Surabaya Pasar Turi', 'seq' => 6, 'arrive' => '04:35:00', 'depart' => null],
        ];

        foreach ($ka4_stops as $stop) {
            DB::table('train_stops')->insert([
                'train_id' => $ka4,
                'station_id' => $stationMap[$stop['station']],
                'sequence' => $stop['seq'],
                'arrival_time' => $stop['arrive'],
                'departure_time' => $stop['depart'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo "Train schedule updated with 4 real trains!\n";
    }
}
