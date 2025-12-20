<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'booking_code',
        'ticket_number',
        'user_id',
        'schedule_id',
        'passenger_name',
        'nik',
        'passenger_type',
        'seat_number',
        'class',
        'price',
        'qr_code',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }
}
