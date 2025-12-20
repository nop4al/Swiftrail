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
        if (!Schema::hasTable('swift_pay_transactions')) {
            Schema::create('swift_pay_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('wallet_id')->constrained('swift_pay_wallets')->onDelete('cascade');
                $table->foreignId('booking_id')->nullable()->constrained()->onDelete('set null')->comment('Referensi booking jika dari pemesanan');
                $table->string('transaction_id')->unique()->comment('ID transaksi unik');
                $table->enum('type', ['topup', 'payment', 'refund', 'adjustment'])->comment('Jenis transaksi');
                $table->enum('status', ['pending', 'success', 'failed', 'cancelled'])->default('pending')->comment('Status transaksi');
                $table->decimal('amount', 15, 2)->comment('Jumlah transaksi');
                $table->decimal('balance_before', 15, 2)->comment('Saldo sebelum transaksi');
                $table->decimal('balance_after', 15, 2)->comment('Saldo setelah transaksi');
                $table->string('payment_method')->nullable()->comment('Metode pembayaran untuk topup (bank, card, etc)');
                $table->string('bank_name')->nullable()->comment('Bank yang digunakan');
                $table->string('reference_number')->nullable()->comment('Nomor referensi pembayaran');
                $table->text('description')->nullable()->comment('Deskripsi transaksi');
                $table->text('notes')->nullable()->comment('Catatan internal');
                $table->timestamp('completed_at')->nullable()->comment('Waktu transaksi selesai');
                $table->timestamps();
                
                $table->index('wallet_id');
                $table->index('booking_id');
                $table->index('type');
                $table->index('status');
                $table->index('created_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('swift_pay_transactions');
    }
};
