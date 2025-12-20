<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    protected $table = 'loyalty_rewards';

    protected $fillable = [
        'name',
        'description',
        'points_required',
        'reward_type',
        'reward_details',
        'stock',
        'is_active',
    ];

    protected $casts = [
        'reward_details' => 'json',
        'is_active' => 'boolean',
    ];

    public function userRewards()
    {
        return $this->hasMany(UserLoyaltyReward::class, 'reward_id');
    }
}
