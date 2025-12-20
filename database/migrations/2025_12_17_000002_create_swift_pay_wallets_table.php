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
        if (!Schema::hasTable('swift_pay_wallets')) {
            Schema::create('swift_pay_wallets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('wallet_number')->unique()->comment('Nomor dompet SwiftPay');
                $table->decimal('balance', 15, 2)->default(0)->comment('Saldo dompet');
                $table->decimal('total_topup', 15, 2)->default(0)->comment('Total pengisian');
                $table->decimal('total_spend', 15, 2)->default(0)->comment('Total pengeluaran');
                $table->string('status')->default('active')->comment('Status: active, suspended, closed');
                $table->timestamp('verified_at')->nullable()->comment('Waktu verifikasi');
                $table->timestamp('last_transaction_at')->nullable()->comment('Transaksi terakhir');
                $table->timestamps();
                
                $table->index('user_id');
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swift_pay_wallets');
    }
};
