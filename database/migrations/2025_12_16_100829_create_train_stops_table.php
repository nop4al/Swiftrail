<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('train_stops')) {
            Schema::create('train_stops', function (Blueprint $table) {
                $table->id();
                $table->foreignId('train_id')->constrained('trains')->cascadeOnDelete();
                $table->foreignId('station_id')->constrained('stations')->cascadeOnDelete();
                $table->unsignedInteger('sequence');
                $table->time('arrival_time')->nullable();
                $table->time('departure_time')->nullable();
                $table->timestamps();
                $table->unique(['train_id', 'sequence']);
                $table->index(['train_id', 'station_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('train_stops');
        Schema::enableForeignKeyConstraints();
    }
};
