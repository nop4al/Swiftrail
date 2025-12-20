<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add latitude and longitude columns to stations table if they don't exist
        if (!Schema::hasColumns('stations', ['latitude', 'longitude'])) {
            Schema::table('stations', function (Blueprint $table) {
                $table->decimal('latitude', 10, 8)->nullable()->after('city');
                $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
            });
        }
    }

    public function down(): void
    {
        Schema::table('stations', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
