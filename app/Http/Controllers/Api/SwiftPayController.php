<?php

namespace App\Http\Controllers\Api;

use App\Models\SwiftPayWallet;
use App\Models\SwiftPayTransaction;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SwiftPayController extends \App\Http\Controllers\Controller
{
    /**
     * Get user's SwiftPay wallet
     */
    public function getWallet(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ], 401);
            }

            $wallet = SwiftPayWallet::where('user_id', $user->id)->first();

            if (!$wallet) {
                // Create wallet if doesn't exist
                $wallet = SwiftPayWallet::create([
                    'user_id' => $user->id,
                    'wallet_number' => SwiftPayWallet::generateWalletNumber(),
                    'balance' => 0,
                    'total_topup' => 0,
                    'total_spend' => 0,
                    'status' => 'active',
                    'verified_at' => now(),
                ]);
            }

            return response()->json([
                'status' => 'success',
                'data' => $wallet
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get wallet transactions
     */
    public function getTransactions(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ], 401);
            }

            $wallet = SwiftPayWallet::where('user_id', $user->id)->first();

            if (!$wallet) {
                return response()->json([
                    'status' => 'success',
                    'data' => [],
                    'total' => 0
                ]);
            }

            $type = $request->query('type');
            $status = $request->query('status');
            $limit = $request->query('limit', 20);

            $query = SwiftPayTransaction::where('wallet_id', $wallet->id);

            if ($type) {
                $query->where('type', $type);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $transactions = $query->latest()->paginate($limit);

            return response()->json([
                'status' => 'success',
                'data' => $transactions->items(),
                'pagination' => [
                    'total' => $transactions->total(),
                    'per_page' => $transactions->perPage(),
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Topup wallet balance
     */
    public function topup(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ], 401);
            }

            $request->validate([
                'amount' => 'required|numeric|min:10000',
                'payment_method' => 'required|in:bank,card,e_wallet',
                'bank_name' => 'nullable|string',
            ]);

            $wallet = SwiftPayWallet::where('user_id', $user->id)->first();

            if (!$wallet) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Wallet not found'
                ], 404);
            }

            if (!$wallet->isActive()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Wallet is not active'
                ], 403);
            }

            $transaction = SwiftPayTransaction::create([
                'wallet_id' => $wallet->id,
                'transaction_id' => 'TOPUP-' . time() . '-' . rand(1000, 9999),
                'type' => 'topup',
                'status' => 'success',
                'amount' => $request->amount,
                'balance_before' => $wallet->balance,
                'balance_after' => $wallet->balance + $request->amount,
                'payment_method' => $request->payment_method,
                'bank_name' => $request->bank_name,
                'description' => 'Top up saldo SwiftPay',
                'completed_at' => now(),
            ]);

            // Update wallet balance
            $wallet->balance += $request->amount;
            $wallet->total_topup += $request->amount;
            $wallet->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Top up successful',
                'data' => [
                    'transaction' => $transaction,
                    'new_balance' => $wallet->balance
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Make payment from wallet for booking
     */
    public function payment(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ], 401);
            }

            $request->validate([
                'booking_id' => 'required|exists:bookings,id',
                'amount' => 'required|numeric|min:0',
            ]);

            $wallet = SwiftPayWallet::where('user_id', $user->id)->first();

            if (!$wallet) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Wallet not found'
                ], 404);
            }

            if (!$wallet->isActive()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Wallet is not active'
                ], 403);
            }

            if ($wallet->balance < $request->amount) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Insufficient balance',
                    'current_balance' => $wallet->balance,
                    'required_amount' => $request->amount
                ], 402);
            }

            $transaction = SwiftPayTransaction::create([
                'wallet_id' => $wallet->id,
                'booking_id' => $request->booking_id,
                'transaction_id' => 'PAY-' . time() . '-' . rand(1000, 9999),
                'type' => 'payment',
                'status' => 'success',
                'amount' => $request->amount,
                'balance_before' => $wallet->balance,
                'balance_after' => $wallet->balance - $request->amount,
                'description' => 'Pembayaran pemesanan tiket kereta',
                'completed_at' => now(),
            ]);

            // Update wallet balance
            $wallet->balance -= $request->amount;
            $wallet->total_spend += $request->amount;
            $wallet->last_transaction_at = now();
            $wallet->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Payment successful',
                'data' => [
                    'transaction' => $transaction,
                    'new_balance' => $wallet->balance
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get wallet summary stats
     */
    public function getStats()
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ], 401);
            }

            $wallet = SwiftPayWallet::where('user_id', $user->id)->first();

            if (!$wallet) {
                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'balance' => 0,
                        'total_topup' => 0,
                        'total_spend' => 0,
                        'transaction_count' => 0,
                    ]
                ]);
            }

            $transactionCount = SwiftPayTransaction::where('wallet_id', $wallet->id)
                ->where('status', 'success')
                ->count();

            return response()->json([
                'status' => 'success',
                'data' => [
                    'balance' => $wallet->balance,
                    'total_topup' => $wallet->total_topup,
                    'total_spend' => $wallet->total_spend,
                    'transaction_count' => $transactionCount,
                    'wallet_status' => $wallet->status,
                    'verified_at' => $wallet->verified_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
