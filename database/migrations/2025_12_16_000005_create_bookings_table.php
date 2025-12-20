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
        if (!Schema::hasTable('bookings')) {
            Schema::create('bookings', function (Blueprint $table) {
                $table->id();
                $table->string('booking_code')->unique();
                $table->string('ticket_number')->unique()->nullable();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
                $table->string('passenger_name');
                $table->string('nik')->nullable();
                $table->enum('passenger_type', ['Dewasa', 'Anak', 'Bayi'])->default('Dewasa');
                $table->string('seat_number');
                $table->string('class'); // e.g., 'Eksekutif 1', 'Economy'
                $table->decimal('price', 12, 2);
                $table->string('qr_code')->nullable();
                $table->enum('status', ['pending', 'confirmed', 'cancelled', 'used'])->default('pending');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
