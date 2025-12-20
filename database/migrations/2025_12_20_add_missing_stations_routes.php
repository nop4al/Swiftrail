<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Skip - stations dan train_stops sudah dibuat oleh seeder
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to revert
    }
};
