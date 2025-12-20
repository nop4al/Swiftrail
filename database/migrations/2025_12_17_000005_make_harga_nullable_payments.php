<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('payments')) {
            return;
        }

        // Make harga nullable if it exists
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'harga')) {
                $table->decimal('harga', 12, 2)->nullable()->change();
            }
            if (Schema::hasColumn('payments', 'tanggal')) {
                $table->dateTime('tanggal')->nullable()->change();
            }
            if (Schema::hasColumn('payments', 'snap_token')) {
                $table->string('snap_token')->nullable()->change();
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'harga')) {
                $table->decimal('harga', 12, 2)->change();
            }
        });
    }
};
