<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MidtransTransaction extends Model
{
    use HasFactory;

    protected $table = 'midtrans_transactions';

    protected $fillable = [
        'order_id',
        'midtrans_transaction_id',
        'payment_type',
        'transaction_status',
        'transaction_time',
        'gross_amount',
        'signature_key',
        'raw_payload',
    ];

    protected $casts = [
        'raw_payload' => 'array',
        'transaction_time' => 'datetime',
    ];

    public function payment()
    {
        return $this->belongsTo(Payment::class, 'order_id', 'order_id');
    }
}
