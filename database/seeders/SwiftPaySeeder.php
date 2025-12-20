<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\SwiftPayWallet;
use Illuminate\Database\Seeder;

class SwiftPaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all existing users and create wallets for them
        $users = User::all();

        foreach ($users as $user) {
            // Check if wallet already exists
            if (!SwiftPayWallet::where('user_id', $user->id)->exists()) {
                SwiftPayWallet::create([
                    'user_id' => $user->id,
                    'wallet_number' => SwiftPayWallet::generateWalletNumber(),
                    'balance' => 0,
                    'total_topup' => 0,
                    'total_spend' => 0,
                    'status' => 'active',
                    'verified_at' => now(),
                ]);
            }
        }

        $this->command->info('SwiftPay wallets created for all users!');
    }
}
