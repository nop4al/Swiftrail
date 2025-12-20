<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserLoyalty extends Model
{
    use HasFactory;

    protected $table = 'user_loyalty';
    
    protected $fillable = [
        'user_id',
        'total_points',
        'tier_id',
        'points_this_month',
        'tier_updated_at',
    ];

    protected $casts = [
        'tier_updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tier()
    {
        return $this->belongsTo(MembershipTier::class, 'tier_id');
    }

    public function transactions()
    {
        return $this->hasMany(PointTransaction::class, 'user_id', 'user_id');
    }

    public function rewards()
    {
        return $this->hasMany(UserLoyaltyReward::class, 'user_id', 'user_id');
    }

    /**
     * Add points to user loyalty
     */
    public static function addPoints($userId, $points, $type = 'earn', $description = '', $referenceType = null, $referenceId = null)
    {
        $userLoyalty = self::firstOrCreate(
            ['user_id' => $userId],
            ['tier_id' => 1, 'total_points' => 0]
        );

        // Get tier multiplier
        $tierMultiplier = $userLoyalty->tier->benefit_multiplier ?? 1.0;
        $finalPoints = (int)($points * $tierMultiplier);

        // Update loyalty balance
        $userLoyalty->increment('total_points', $finalPoints);
        $userLoyalty->increment('points_this_month', $finalPoints);

        // Create transaction record
        PointTransaction::create([
            'user_id' => $userId,
            'points_change' => $finalPoints,
            'type' => $type,
            'description' => $description,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
        ]);

        // Update tier if points reached
        $userLoyalty->updateTier();

        return $userLoyalty;
    }

    /**
     * Redeem points from user loyalty
     */
    public static function redeemPoints($userId, $points, $rewardId, $description = '')
    {
        $userLoyalty = self::where('user_id', $userId)->first();

        if (!$userLoyalty || $userLoyalty->total_points < $points) {
            return null;
        }

        $userLoyalty->decrement('total_points', $points);

        PointTransaction::create([
            'user_id' => $userId,
            'points_change' => -$points,
            'type' => 'redeem',
            'description' => $description,
            'reference_type' => 'reward',
            'reference_id' => $rewardId,
        ]);

        return $userLoyalty;
    }

    /**
     * Update user tier based on points
     */
    public function updateTier()
    {
        $newTier = MembershipTier::where('min_points', '<=', $this->total_points)
            ->orderBy('min_points', 'desc')
            ->first();

        if ($newTier && $newTier->id !== $this->tier_id) {
            $this->update([
                'tier_id' => $newTier->id,
                'tier_updated_at' => now(),
            ]);
        }
    }
}
