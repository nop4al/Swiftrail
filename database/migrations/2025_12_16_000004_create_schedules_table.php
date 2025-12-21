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
        if (!Schema::hasTable('schedules')) {
            Schema::create('schedules', function (Blueprint $table) {
                $table->id();
                $table->foreignId('train_id')->constrained('trains')->onDelete('cascade');
                $table->foreignId('route_id')->constrained('routes')->onDelete('cascade');
                $table->time('departure_time');
                $table->time('arrival_time');
                $table->string('days')->default(''); // e.g., 'MON,WED,FRI' or empty for every day
                $table->enum('status', ['active', 'inactive', 'sold-out'])->default('active');
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
                Schema::dropIfExists('schedules');
                Schema::enableForeignKeyConstraints();
        }
    };
