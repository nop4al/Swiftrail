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
        if (!Schema::hasTable('user_loyalty_rewards')) {
            Schema::create('user_loyalty_rewards', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('reward_id')->constrained('rewards')->onDelete('cascade');
                $table->enum('status', ['redeemed', 'pending', 'used', 'expired'])->default('pending');
                $table->timestamp('redeemed_at')->nullable();
                $table->timestamp('expires_at')->nullable();
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
        Schema::dropIfExists('user_loyalty_rewards');
        Schema::enableForeignKeyConstraints();
    }
};
