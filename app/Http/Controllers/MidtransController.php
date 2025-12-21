<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use App\Models\Payment;
use App\Models\SwiftPayWallet;
use App\Models\SwiftPayTransaction;

class MidtransController extends Controller
{
    /**
     * Create a Midtrans Snap transaction and return token/redirect_url.
     */
    public function create(Request $request)
    {
        $serverKey = env('MIDTRANS_SERVER_KEY', 'SB-Mid-server-Z1pOcCHZIJ9dz__2gAW9UNEx');

        $orderId = $request->input('order_id') ?? 'ORD-'.time();
        $grossAmount = (int) ($request->input('gross_amount') ?? 0);
        $customer = $request->input('customer', []);

        // basic validation
        if ($grossAmount <= 0) {
            return response()->json(['status' => 'error', 'message' => 'Invalid gross_amount'], 400);
        }

        // derive amount/tax if not provided
        $amount = $request->input('amount');
        $tax = $request->input('tax');
        if (is_null($amount) && $grossAmount > 0) {
            $amount = (int) round($grossAmount / 1.11);
            $tax = $grossAmount - $amount;
        }

        // Get user_id from authenticated user OR from request
        $userId = null;
        
        // Try to get from authenticated user first
        if ($request->user()) {
            $userId = $request->user()->id;
            logger()->info('User ID from authenticated user', ['user_id' => $userId]);
        } else {
            // Fall back to request input
            $userId = $request->input('user_id');
            logger()->info('User ID from request input', ['user_id' => $userId]);
        }
        
        // Ensure user_id is integer
        $userId = $userId ? (int) $userId : null;
        
        logger()->info('Payment creation', [
            'order_id' => $orderId,
            'user_id' => $userId,
            'amount' => $amount,
            'authenticated' => $request->user() ? true : false,
        ]);

        // Don't reuse existing payment - always create new for topup
        $payment = Payment::where('order_id', $orderId)->first();
        
        // Create new payment record if not exists
        if (!$payment) {
            $payment = new Payment();
            $payment->order_id = $orderId;
            $payment->user_id = $userId;
            $payment->amount = (int) $amount;
            $payment->tax = (int) $tax;
            $payment->total = $grossAmount; // total = gross_amount
            $payment->status = 'pending';
            $payment->save();
            
            logger()->info('Payment record created', [
                'order_id' => $orderId,
                'user_id' => $payment->user_id,
                'amount' => $amount,
                'total' => $grossAmount,
            ]);
        }

        $appUrl = rtrim(env('APP_URL', 'http://127.0.0.1:8000'), '/');
        
        $payload = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => [
                'first_name' => $customer['name'] ?? 'Customer',
                'email' => $customer['email'] ?? null,
            ],
            'callbacks' => [
                'finish' => $appUrl . '/api/v1/midtrans/finish',
                'error' => $appUrl . '/api/v1/midtrans/error',
                'pending' => $appUrl . '/api/v1/midtrans/pending',
            ],
        ];

        $url = 'https://app.sandbox.midtrans.com/snap/v1/transactions';

        try {
            logger()->info('Calling Midtrans API', [
                'payload' => $payload,
                'app_url' => $appUrl,
            ]);
            // Disable SSL verification for development environment
            $response = Http::withBasicAuth($serverKey, '')
                ->withoutVerifying()
                ->post($url, $payload);

            $body = $response->json();
            logger()->info('Midtrans API response', ['status' => $response->status(), 'body' => $body]);

            // Try to save payment record if it exists, but don't fail if it doesn't
            if ($payment) {
                try {
                    if (Schema::hasColumn('payments', 'midtrans_token')) {
                        $payment->midtrans_token = $body['token'] ?? null;
                    }
                    if (Schema::hasColumn('payments', 'midtrans_redirect_url')) {
                        $payment->midtrans_redirect_url = $body['redirect_url'] ?? null;
                    }
                    if (Schema::hasColumn('payments', 'raw_response')) {
                        $payment->raw_response = $body ?? ['status' => $response->status(), 'body' => $response->body()];
                    }
                    if (Schema::hasColumn('payments', 'status')) {
                        $payment->status = $response->successful() ? 'pending' : 'failed';
                    }
                    $payment->save();
                    logger()->info('Payment record updated', ['id' => $payment->id]);
                } catch (\Exception $e) {
                    logger()->warning('Failed to update payment record: '.$e->getMessage());
                }
            }

            if ($response->successful()) {
                $token = $body['token'] ?? null;
                $redirectUrl = $body['redirect_url'] ?? null;
                
                // If no redirect_url, build it manually from token
                if ($token && !$redirectUrl) {
                    $redirectUrl = 'https://app.sandbox.midtrans.com/snap/v1/transactions/' . $token . '/redirect';
                }
                
                logger()->info('Payment created', [
                    'order_id' => $orderId,
                    'token' => $token,
                    'redirect_url' => $redirectUrl,
                ]);
                
                return response()->json([
                    'status' => 'ok',
                    'data' => $body,
                    'token' => $token,
                    'redirect_url' => $redirectUrl,
                ]);
            }

            logger()->error('Midtrans API returned error', ['status' => $response->status(), 'body' => $response->body()]);

            return response()->json([
                'status' => 'error',
                'message' => 'Midtrans API error',
                'body' => $response->body(),
            ], 502);
        } catch (\Exception $e) {
            logger()->error('Midtrans request failed: '.$e->getMessage(), ['exception' => $e]);

            return response()->json([
                'status' => 'error',
                'message' => 'External request failed',
                'detail' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Public: return payment details by order id (used by frontend as unauthenticated fallback)
     */
    public function getPayment($orderId)
    {
        try {
            $payment = Payment::where('order_id', $orderId)->first();
            if (!$payment) {
                return response()->json(['status' => 'error', 'message' => 'Payment not found'], 404);
            }

            $amount = $payment->amount ?? 0;
            $tax = $payment->tax ?? (int) round($amount * 0.11);
            $total = $payment->total ?? ($amount + $tax);

            return response()->json([
                'status' => 'ok',
                'data' => [
                    'order_id' => $payment->order_id,
                    'amount' => (int) $amount,
                    'tax' => (int) $tax,
                    'total' => (int) $total,
                    'status' => $payment->status ?? null,
                ],
            ]);
        } catch (\Exception $e) {
            logger()->error('getPayment failed: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => 'Server error'], 500);
        }
    }

    /**
     * Handle Midtrans finish callback
     */
    public function finish(Request $request)
    {
        $orderId = $request->query('order_id');
        $statusCode = $request->query('status_code');
        $transactionStatus = $request->query('transaction_status');

        logger()->info('Midtrans finish callback', [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'transaction_status' => $transactionStatus,
        ]);

        // Check jika payment berhasil (status_code 200 = settlement/capture)
        if ($statusCode == 200 && in_array($transactionStatus, ['capture', 'settlement'])) {
            try {
                // Find payment by order_id
                $payment = Payment::where('order_id', $orderId)->first();
                
                logger()->info('Payment lookup result', [
                    'order_id' => $orderId,
                    'payment_found' => $payment ? true : false,
                    'payment_id' => $payment?->id,
                    'payment_user_id' => $payment?->user_id,
                    'payment_amount' => $payment?->amount,
                ]);
                
                if ($payment) {
                    $amount = $payment->amount ?? 0;
                    
                    logger()->info('Processing wallet update', [
                        'user_id' => $payment->user_id,
                        'amount' => $amount,
                        'has_user_id' => $payment->user_id ? true : false,
                    ]);
                    
                    // Update wallet balance
                    if ($payment->user_id) {
                        $wallet = SwiftPayWallet::where('user_id', $payment->user_id)->first();
                        
                        logger()->info('Wallet lookup result', [
                            'user_id' => $payment->user_id,
                            'wallet_found' => $wallet ? true : false,
                            'wallet_id' => $wallet?->id,
                            'old_balance' => $wallet?->balance,
                        ]);
                        
                        if ($wallet) {
                            $old_balance = $wallet->balance ?? 0;
                            $wallet->balance = $old_balance + $amount;
                            $wallet->save();
                            logger()->info('Wallet updated successfully', [
                                'user_id' => $payment->user_id,
                                'old_balance' => $old_balance,
                                'amount' => $amount,
                                'new_balance' => $wallet->balance,
                            ]);
                            // Create a SwiftPayTransaction record for the topup
                            try {
                                SwiftPayTransaction::create([
                                    'wallet_id' => $wallet->id,
                                    'transaction_id' => 'TOPUP-' . time() . '-' . rand(1000, 9999),
                                    'type' => 'topup',
                                    'status' => 'success',
                                    'amount' => $amount,
                                    'balance_before' => $old_balance,
                                    'balance_after' => $wallet->balance,
                                    'payment_method' => 'midtrans',
                                    'reference_number' => $payment->order_id,
                                    'description' => 'Top up via Midtrans',
                                    'completed_at' => now(),
                                ]);
                            } catch (\Exception $e) {
                                logger()->warning('Failed to create SwiftPayTransaction: ' . $e->getMessage());
                            }
                        } else {
                            logger()->warning('Wallet not found', [
                                'user_id' => $payment->user_id,
                            ]);
                        }
                    } else {
                        logger()->warning('Payment has no user_id', [
                            'order_id' => $orderId,
                            'payment_id' => $payment->id,
                        ]);
                    }
                    
                    // Update payment status
                    $payment->status = 'success';
                    $payment->save();
                    logger()->info('Payment status updated to success', ['order_id' => $orderId]);
                } else {
                    logger()->warning('Payment not found for order_id', ['order_id' => $orderId]);
                }
            } catch (\Exception $e) {
                logger()->error('Failed to update wallet on finish', [
                    'error' => $e->getMessage(),
                    'order_id' => $orderId,
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        // Redirect ke halaman sukses
        return redirect('/swiftpay?status=success&order_id=' . $orderId);
    }

    /**
     * Handle Midtrans error callback
     */
    public function error(Request $request)
    {
        $orderId = $request->query('order_id');
        $statusCode = $request->query('status_code');

        logger()->warning('Midtrans error callback', [
            'order_id' => $orderId,
            'status_code' => $statusCode,
        ]);

        // Redirect ke halaman error
        return redirect('/swiftpay?status=error&order_id=' . $orderId);
    }

    /**
     * Handle Midtrans pending callback
     */
    public function pending(Request $request)
    {
        $orderId = $request->query('order_id');
        $statusCode = $request->query('status_code');

        logger()->warning('Midtrans pending callback', [
            'order_id' => $orderId,
            'status_code' => $statusCode,
        ]);

        // Redirect ke halaman pending
        return redirect('/swiftpay?status=pending&order_id=' . $orderId);
    }
}
