<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tourism extends Model
{
    protected $table = 'tourism';

    protected $fillable = [
        'station_id',
        'name',
        'category',
        'description',
        'image',
        'rating',
        'reviews',
        'address',
        'hours',
        'phone',
        'website',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'rating' => 'float',
        'reviews' => 'integer',
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }
}
