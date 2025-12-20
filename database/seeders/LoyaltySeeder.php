<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MembershipTier;
use App\Models\Reward;

class LoyaltySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create loyalty tiers
        $tiers = [
            [
                'name' => 'Silver',
                'min_points' => 0,
                'benefit_multiplier' => 1.0,
                'description' => 'Tier dasar untuk semua member baru',
            ],
            [
                'name' => 'Gold',
                'min_points' => 5000,
                'benefit_multiplier' => 1.5,
                'description' => 'Dapatkan bonus 50% lebih banyak poin',
            ],
            [
                'name' => 'Platinum',
                'min_points' => 15000,
                'benefit_multiplier' => 2.0,
                'description' => 'Dapatkan bonus 100% lebih banyak poin plus akses eksklusif',
            ],
        ];

        foreach ($tiers as $tier) {
            MembershipTier::firstOrCreate(
                ['name' => $tier['name']],
                $tier
            );
        }

        // Create loyalty rewards
        $rewards = [
            [
                'name' => 'Diskon 25.000',
                'description' => 'Diskon Rp 25.000 untuk perjalanan berikutnya',
                'points_required' => 1000,
                'reward_type' => 'discount',
                'reward_details' => json_encode(['discount_amount' => 25000, 'currency' => 'IDR']),
                'stock' => -1, // unlimited
                'is_active' => true,
            ],
            [
                'name' => 'Diskon 50.000',
                'description' => 'Diskon Rp 50.000 untuk perjalanan berikutnya',
                'points_required' => 2000,
                'reward_type' => 'discount',
                'reward_details' => json_encode(['discount_amount' => 50000, 'currency' => 'IDR']),
                'stock' => -1,
                'is_active' => true,
            ],
            [
                'name' => 'Diskon 100.000',
                'description' => 'Diskon Rp 100.000 untuk perjalanan berikutnya',
                'points_required' => 5000,
                'reward_type' => 'discount',
                'reward_details' => json_encode(['discount_amount' => 100000, 'currency' => 'IDR']),
                'stock' => -1,
                'is_active' => true,
            ],
            [
                'name' => 'Tiket Gratis 1x',
                'description' => 'Tiket gratis untuk 1 perjalanan (sesuai kategori)',
                'points_required' => 8000,
                'reward_type' => 'free_ticket',
                'reward_details' => json_encode(['trip_count' => 1, 'max_price' => 500000]),
                'stock' => 100,
                'is_active' => true,
            ],
            [
                'name' => 'Bonus 2000 Poin',
                'description' => 'Dapatkan bonus 2000 poin loyalitas',
                'points_required' => 3000,
                'reward_type' => 'points',
                'reward_details' => json_encode(['bonus_points' => 2000]),
                'stock' => -1,
                'is_active' => true,
            ],
            [
                'name' => 'Bonus 5000 Poin',
                'description' => 'Dapatkan bonus 5000 poin loyalitas',
                'points_required' => 10000,
                'reward_type' => 'points',
                'reward_details' => json_encode(['bonus_points' => 5000]),
                'stock' => -1,
                'is_active' => true,
            ],
        ];

        foreach ($rewards as $reward) {
            Reward::firstOrCreate(
                ['name' => $reward['name']],
                $reward
            );
        }
    }
}
