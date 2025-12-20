<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SwiftPayTransaction extends Model
{
    use HasFactory;

    protected $table = 'swift_pay_transactions';

    protected $fillable = [
        'wallet_id',
        'booking_id',
        'transaction_id',
        'type',
        'status',
        'amount',
        'balance_before',
        'balance_after',
        'payment_method',
        'bank_name',
        'reference_number',
        'description',
        'notes',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
        'completed_at' => 'datetime',
    ];

    /**
     * Relationship dengan Wallet
     */
    public function wallet()
    {
        return $this->belongsTo(SwiftPayWallet::class, 'wallet_id');
    }

    /**
     * Relationship dengan Booking
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Scope: Filter by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope: Filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Filter by date range
     */
    public function scopeDateBetween($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope: Get successful transactions
     */
    public function scopeSuccessful($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope: Get pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Cek apakah transaksi berhasil
     */
    public function isSuccessful()
    {
        return $this->status === 'success';
    }

    /**
     * Cek apakah transaksi pending
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }
}
