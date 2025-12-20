<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'departure_station_id',
        'arrival_station_id',
        'distance',
        'duration',
        'base_price',
        'status',
    ];

    protected $casts = [
        'distance' => 'integer',
        'base_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function departureStation()
    {
        return $this->belongsTo(Station::class, 'departure_station_id');
    }

    public function arrivalStation()
    {
        return $this->belongsTo(Station::class, 'arrival_station_id');
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
