<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    /* Get available payment methods */
    public function getPaymentMethods(): JsonResponse
    {
        try {
            $methods = [
                [
                    'id' => 'credit_card',
                    'name' => 'Credit Card',
                    'icon' => 'credit-card',
                    'enabled' => true,
                ],
                [
                    'id' => 'debit_card',
                    'name' => 'Debit Card',
                    'icon' => 'debit-card',
                    'enabled' => true,
                ],
                [
                    'id' => 'bank_transfer',
                    'name' => 'Bank Transfer',
                    'icon' => 'bank',
                    'enabled' => true,
                ],
                [
                    'id' => 'e_wallet',
                    'name' => 'E-Wallet',
                    'icon' => 'wallet',
                    'enabled' => true,
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $methods
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving payment methods: ' . $e->getMessage()
            ], 500);
        }
    }

    /* Process payment for a booking */
    public function processPayment(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'booking_id' => 'required|exists:bookings,id',
                'payment_method' => 'required|string',
                'amount' => 'required|numeric',
            ]);

            $booking = Booking::find($validated['booking_id']);

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            if ($booking->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking is not in pending status'
                ], 409);
            }

            // Validate amount
            if ($validated['amount'] != $booking->price) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment amount does not match booking price'
                ], 422);
            }

            // Here you would integrate with payment gateway (Stripe, Midtrans, etc.)
            // For now, we'll simulate successful payment

            // Generate ticket number
            $ticketNumber = 'TK' . date('YmdHis') . random_int(10000, 99999);

            // Update booking status
            $booking->update([
                'status' => 'confirmed',
                'ticket_number' => $ticketNumber,
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'transactionId' => 'TXN' . date('YmdHis'),
                    'bookingCode' => $booking->booking_code,
                    'ticketNumber' => $ticketNumber,
                    'status' => 'confirmed',
                    'amount' => $booking->price,
                    'paymentMethod' => $validated['payment_method'],
                    'paidAt' => now(),
                ],
                'message' => 'Payment processed successfully'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error processing payment: ' . $e->getMessage()
            ], 500);
        }
    }

    /* Get order confirmation details */
    public function getOrderConfirmation($bookingId): JsonResponse
    {
        try {
            $booking = Booking::with(['user', 'schedule.train', 'schedule.route.departureStation', 'schedule.route.arrivalStation'])
                ->find($bookingId);

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'bookingCode' => $booking->booking_code,
                    'ticketNumber' => $booking->ticket_number,
                    'passenger' => [
                        'name' => $booking->passenger_name,
                        'type' => $booking->passenger_type,
                        'nik' => $booking->nik,
                    ],
                    'journey' => [
                        'trainName' => $booking->schedule->train->name,
                        'trainNumber' => $booking->schedule->train->code,
                        'trainType' => $booking->schedule->train->type,
                        'departureTime' => $booking->schedule->departure_time,
                        'arrivalTime' => $booking->schedule->arrival_time,
                        'fromStation' => $booking->schedule->route->departureStation->name,
                        'toStation' => $booking->schedule->route->arrivalStation->name,
                    ],
                    'seat' => [
                        'seatNumber' => $booking->seat_number,
                        'class' => $booking->class,
                    ],
                    'pricing' => [
                        'price' => $booking->price,
                        'platformFee' => 0,
                        'total' => $booking->price,
                    ],
                    'status' => $booking->status,
                    'createdAt' => $booking->created_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving order confirmation: ' . $e->getMessage()
            ], 500);
        }
    }
}
