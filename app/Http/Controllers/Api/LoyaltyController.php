<?php

namespace App\Http\Controllers\Api;

use App\Models\UserLoyalty;
use App\Models\Reward;
use App\Models\UserLoyaltyReward;
use Illuminate\Http\Request;

class LoyaltyController extends ApiBaseController
{
    /**
     * Get complete loyalty information (unified endpoint)
     * GET /api/v1/loyalty
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            
            // Get user loyalty
            $userLoyalty = UserLoyalty::with('tier')
                ->where('user_id', $user->id)
                ->first();

            if (!$userLoyalty) {
                $userLoyalty = UserLoyalty::create([
                    'user_id' => $user->id,
                    'tier_id' => 1,
                    'total_points' => 0,
                ]);
                $userLoyalty->load('tier');
            }

            // Get transactions
            $transactions = \App\Models\PointTransaction::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->limit(10)
                ->get();

            // Get available rewards
            $rewards = Reward::where('is_active', true)
                ->orderBy('points_required')
                ->get();

            // Get my claimed rewards
            $myRewards = UserLoyaltyReward::with('reward')
                ->where('user_id', $user->id)
                ->orderBy('claimed_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Loyalty data retrieved',
                'data' => [
                    'loyalty' => [
                        'total_points' => $userLoyalty->total_points,
                        'points_this_month' => $userLoyalty->points_this_month,
                        'tier' => [
                            'id' => $userLoyalty->tier->id,
                            'name' => $userLoyalty->tier->name,
                            'min_points' => $userLoyalty->tier->min_points,
                            'benefit_multiplier' => (float)$userLoyalty->tier->benefit_multiplier,
                            'description' => $userLoyalty->tier->description,
                        ],
                        'tier_updated_at' => $userLoyalty->tier_updated_at,
                    ],
                    'transactions' => $transactions->map(function($transaction) {
                        return [
                            'id' => $transaction->id,
                            'points_change' => $transaction->points_change,
                            'type' => $transaction->type,
                            'description' => $transaction->description,
                            'reference_type' => $transaction->reference_type,
                            'reference_id' => $transaction->reference_id,
                            'created_at' => $transaction->created_at,
                        ];
                    }),
                    'rewards' => $rewards->map(function($reward) {
                        return [
                            'id' => $reward->id,
                            'name' => $reward->name,
                            'description' => $reward->description,
                            'points_required' => $reward->points_required,
                            'reward_type' => $reward->reward_type,
                            'reward_details' => $reward->reward_details,
                            'stock' => $reward->stock,
                            'is_active' => $reward->is_active,
                        ];
                    }),
                    'my_rewards' => $myRewards->map(function($myReward) {
                        return [
                            'id' => $myReward->id,
                            'reward' => [
                                'id' => $myReward->reward->id,
                                'name' => $myReward->reward->name,
                                'reward_type' => $myReward->reward->reward_type,
                            ],
                            'claimed_at' => $myReward->claimed_at,
                            'expired_at' => $myReward->expired_at,
                            'used' => $myReward->used,
                            'used_at' => $myReward->used_at,
                        ];
                    }),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve loyalty data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Claim a reward
     * POST /api/v1/loyalty/claim-reward
     */
    public function claimReward(Request $request)
    {
        try {
            $validated = $request->validate([
                'reward_id' => 'required|exists:loyalty_rewards,id',
            ]);

            $user = $request->user();
            $reward = Reward::findOrFail($validated['reward_id']);
            
            $userLoyalty = UserLoyalty::where('user_id', $user->id)->first();

            if (!$userLoyalty || $userLoyalty->total_points < $reward->points_required) {
                return response()->json([
                    'success' => false,
                    'message' => 'Poin tidak cukup untuk mengklaim reward ini',
                    'required_points' => $reward->points_required,
                    'current_points' => $userLoyalty->total_points ?? 0,
                ], 400);
            }

            // Check stock
            if ($reward->stock !== -1) {
                $claimedCount = UserLoyaltyReward::where('reward_id', $reward->id)
                    ->where('used', false)
                    ->count();

                if ($claimedCount >= $reward->stock) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Stok reward sudah habis',
                    ], 400);
                }
            }

            // Redeem points
            UserLoyalty::redeemPoints($user->id, $reward->points_required, $reward->id, "Claim: {$reward->name}");

            // Create user reward record
            $userReward = UserLoyaltyReward::create([
                'user_id' => $user->id,
                'reward_id' => $reward->id,
                'claimed_at' => now(),
                'expired_at' => now()->addDays(30), // 30 days validity
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reward berhasil diklaim',
                'data' => [
                    'reward_code' => 'REWARD' . $userReward->id,
                    'reward_name' => $reward->name,
                    'reward_details' => $reward->reward_details,
                    'claimed_at' => $userReward->claimed_at,
                    'expired_at' => $userReward->expired_at,
                ]
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to claim reward',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
