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
        if (!Schema::hasTable('user_loyalty')) {
            Schema::create('user_loyalty', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
                $table->integer('total_points')->default(0);
                $table->foreignId('tier_id')->nullable()->constrained('membership_tiers')->onDelete('set null');
                $table->integer('points_this_month')->default(0);
                $table->timestamp('tier_updated_at')->nullable();
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
        Schema::dropIfExists('user_loyalty');
        Schema::enableForeignKeyConstraints();
    }
};
