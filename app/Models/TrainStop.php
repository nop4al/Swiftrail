<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrainStop extends Model
{
    protected $fillable = ['train_id', 'station_id', 'sequence', 'arrival_time', 'departure_time'];

    public function station()
    {
        return $this->belongsTo(Station::class);
    }

    public function train()
    {
        return $this->belongsTo(Train::class);
    }
}
