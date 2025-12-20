<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'city',
        'latitude',
        'longitude',
        'active',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function routes()
    {
        return $this->hasMany(Route::class, 'departure_station_id')
                    ->orHas('arrivals');
    }

    public function tourism()
    {
        return $this->hasMany(Tourism::class);
    }
}
