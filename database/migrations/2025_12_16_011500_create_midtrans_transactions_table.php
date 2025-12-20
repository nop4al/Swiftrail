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
        if (!Schema::hasTable('midtrans_transactions')) {
            Schema::create('midtrans_transactions', function (Blueprint $table) {
                $table->id();
                $table->string('order_id')->index();
                $table->string('midtrans_transaction_id')->nullable()->index();
                $table->string('payment_type')->nullable();
                $table->string('transaction_status')->nullable();
                $table->timestamp('transaction_time')->nullable();
                $table->bigInteger('gross_amount')->default(0);
                $table->string('signature_key')->nullable();
                $table->json('raw_payload')->nullable();
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
        Schema::dropIfExists('midtrans_transactions');
        Schema::enableForeignKeyConstraints();
    }
};
