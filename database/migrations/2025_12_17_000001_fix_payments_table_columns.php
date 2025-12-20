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

        Schema::table('payments', function (Blueprint $table) {
            if (!Schema::hasColumn('payments', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('order_id');
            }
            if (!Schema::hasColumn('payments', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('transaction_id');
            }
            if (!Schema::hasColumn('payments', 'amount')) {
                $table->bigInteger('amount')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('payments', 'tax')) {
                $table->bigInteger('tax')->nullable()->after('amount');
            }
            if (!Schema::hasColumn('payments', 'total')) {
                $table->bigInteger('total')->nullable()->after('tax');
            }
            if (!Schema::hasColumn('payments', 'status')) {
                $table->string('status')->default('created')->after('total');
            }
            if (!Schema::hasColumn('payments', 'midtrans_token')) {
                $table->string('midtrans_token')->nullable()->after('status');
            }
            if (!Schema::hasColumn('payments', 'midtrans_redirect_url')) {
                $table->string('midtrans_redirect_url')->nullable()->after('midtrans_token');
            }
            if (!Schema::hasColumn('payments', 'raw_response')) {
                $table->json('raw_response')->nullable()->after('midtrans_redirect_url');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('payments')) {
            return;
        }

        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'raw_response')) {
                $table->dropColumn('raw_response');
            }
            if (Schema::hasColumn('payments', 'midtrans_redirect_url')) {
                $table->dropColumn('midtrans_redirect_url');
            }
            if (Schema::hasColumn('payments', 'midtrans_token')) {
                $table->dropColumn('midtrans_token');
            }
            if (Schema::hasColumn('payments', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('payments', 'total')) {
                $table->dropColumn('total');
            }
            if (Schema::hasColumn('payments', 'tax')) {
                $table->dropColumn('tax');
            }
            if (Schema::hasColumn('payments', 'amount')) {
                $table->dropColumn('amount');
            }
            if (Schema::hasColumn('payments', 'user_id')) {
                $table->dropColumn('user_id');
            }
            if (Schema::hasColumn('payments', 'transaction_id')) {
                $table->dropColumn('transaction_id');
            }
        });
    }
};
