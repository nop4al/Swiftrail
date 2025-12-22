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
        Schema::create('tourism', function (Blueprint $table) {
            $table->id();
            $table->foreignId('station_id')->constrained('stations')->onDelete('cascade');
            $table->string('name');
            $table->string('category'); // Museum, Restoran, Hotel, Taman, dll
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('rating', 3, 1)->default(0);
            $table->integer('reviews')->default(0);
            $table->string('address')->nullable();
            $table->string('hours')->nullable(); // Opening hours
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tourism');
    }
};
