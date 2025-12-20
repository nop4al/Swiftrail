<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipTier extends Model
{
    use HasFactory;

    protected $table = 'loyalty_tiers';

    protected $fillable = [
        'name',
        'min_points',
        'benefit_multiplier',
        'description',
    ];

    public function userLoyalties()
    {
        return $this->hasMany(UserLoyalty::class, 'tier_id');
    }
}
