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
        if (!Schema::hasTable('routes')) {
            Schema::create('routes', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->foreignId('departure_station_id')->constrained('stations')->onDelete('cascade');
                $table->foreignId('arrival_station_id')->constrained('stations')->onDelete('cascade');
                $table->integer('distance')->nullable();
                $table->string('duration')->nullable();
                $table->decimal('base_price', 12, 2)->default(0);
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
            });
        }
    }

        /**
         * Reverse the migrations.
         */
        public function down(): void
        {
                Schema::disableForeignKeyConstraints();
                Schema::dropIfExists('routes');
                Schema::enableForeignKeyConstraints();
        }
    };