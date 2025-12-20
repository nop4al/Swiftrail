<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add missing stations if they don't exist
        DB::table('stations')->insertOrIgnore([
            ['code' => 'GMB', 'name' => 'Stasiun Gambir', 'city' => 'Jakarta', 'active' => true],
        ]);
        
        DB::table('stations')->insertOrIgnore([
            ['code' => 'SBY-P', 'name' => 'Stasiun Pasar Turi Surabaya', 'city' => 'Surabaya', 'active' => true],
        ]);

        // Get station IDs
        $gmbStation = DB::table('stations')->where('code', 'GMB')->first();
        $sbypStation = DB::table('stations')->where('code', 'SBY-P')->first();

        // Add missing route if it doesn't exist
        if ($gmbStation && $sbypStation) {
            DB::table('routes')->insertOrIgnore([
                'code' => 'GMB-SBY-P',
                'departure_station_id' => $gmbStation->id,
                'arrival_station_id' => $sbypStation->id,
                'distance' => 780,
                'duration' => '5 jam 30 menit',
                'base_price' => 480000,
                'status' => 'active',
            ]);

            // Get train IDs that should have this route
            $trains = DB::table('trains')->limit(2)->get();
            
            // Add schedules for this route
            foreach ($trains as $train) {
                $route = DB::table('routes')->where('code', 'GMB-SBY-P')->first();
                
                DB::table('schedules')->insertOrIgnore([
                    'train_id' => $train->id,
                    'route_id' => $route->id,
                    'departure_time' => '06:00',
                    'arrival_time' => '11:30',
                    'days' => 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                    'status' => 'active',
                ]);
                
                DB::table('schedules')->insertOrIgnore([
                    'train_id' => $train->id,
                    'route_id' => $route->id,
                    'departure_time' => '14:00',
                    'arrival_time' => '19:30',
                    'days' => 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
                    'status' => 'active',
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete added data in reverse order
        DB::table('schedules')->where('departure_time', '06:00')->whereHas('route', function($q) {
            $q->where('code', 'GMB-SBY-P');
        })->delete();
        
        DB::table('schedules')->where('departure_time', '14:00')->whereHas('route', function($q) {
            $q->where('code', 'GMB-SBY-P');
        })->delete();
        
        DB::table('routes')->where('code', 'GMB-SBY-P')->delete();
        DB::table('stations')->where('code', 'GMB')->delete();
        DB::table('stations')->where('code', 'SBY-P')->delete();
    }
};
