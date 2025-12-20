<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLoyaltyReward extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'reward_id',
        'claimed_at',
        'expired_at',
        'used',
        'used_at',
    ];

    protected $casts = [
        'claimed_at' => 'datetime',
        'expired_at' => 'datetime',
        'used_at' => 'datetime',
        'used' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reward()
    {
        return $this->belongsTo(Reward::class, 'reward_id');
    }
}
