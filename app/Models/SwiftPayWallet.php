<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SwiftPayWallet extends Model
{
    use HasFactory;

    protected $table = 'swift_pay_wallets';

    protected $fillable = [
        'user_id',
        'wallet_number',
        'balance',
        'total_topup',
        'total_spend',
        'status',
        'verified_at',
        'last_transaction_at',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_topup' => 'decimal:2',
        'total_spend' => 'decimal:2',
        'verified_at' => 'datetime',
        'last_transaction_at' => 'datetime',
    ];

    /**
     * Relationship dengan User
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relationship dengan Transactions
     */
    public function transactions()
    {
        return $this->hasMany(SwiftPayTransaction::class, 'wallet_id');
    }

    /**
     * Cek apakah wallet aktif
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Topup saldo
     */
    public function addBalance($amount, $description = null)
    {
        $this->balance += $amount;
        $this->total_topup += $amount;
        $this->save();

        return SwiftPayTransaction::create([
            'wallet_id' => $this->id,
            'transaction_id' => 'TOPUP-' . time() . '-' . rand(1000, 9999),
            'type' => 'topup',
            'status' => 'success',
            'amount' => $amount,
            'balance_before' => $this->balance - $amount,
            'balance_after' => $this->balance,
            'description' => $description,
            'completed_at' => now(),
        ]);
    }

    /**
     * Kurangi saldo (untuk pembayaran)
     */
    public function deductBalance($amount, $bookingId = null, $description = null)
    {
        if ($this->balance < $amount) {
            throw new \Exception('Saldo tidak cukup');
        }

        $balanceBefore = $this->balance;
        $this->balance -= $amount;
        $this->total_spend += $amount;
        $this->last_transaction_at = now();
        $this->save();

        return SwiftPayTransaction::create([
            'wallet_id' => $this->id,
            'booking_id' => $bookingId,
            'transaction_id' => 'PAY-' . time() . '-' . rand(1000, 9999),
            'type' => 'payment',
            'status' => 'success',
            'amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $this->balance,
            'description' => $description,
            'completed_at' => now(),
        ]);
    }

    /**
     * Generate nomor dompet unik
     */
    public static function generateWalletNumber()
    {
        do {
            $number = 'SP' . date('Ymd') . rand(100000, 999999);
        } while (self::where('wallet_number', $number)->exists());

        return $number;
    }
}
