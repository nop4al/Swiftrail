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
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->string('order_id')->index();
                $table->string('transaction_id')->nullable()->index();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->bigInteger('amount')->default(0);
                $table->bigInteger('tax')->default(0);
                $table->bigInteger('total')->default(0);
                $table->string('status')->default('created');
                $table->string('midtrans_token')->nullable();
                $table->string('midtrans_redirect_url')->nullable();
                $table->json('raw_response')->nullable();
                $table->timestamps();

                // foreign key optional (depends on users table)
                // $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('payments');
        Schema::enableForeignKeyConstraints();
    }
};
