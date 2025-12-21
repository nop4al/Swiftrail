<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $bookings = Booking::where('user_id', $user->id)
                ->with(['schedule.train', 'schedule.route.departureStation', 'schedule.route.arrivalStation'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($booking) {
                    $schedule = $booking->schedule;
                    $train = $schedule->train;
                    $route = $schedule->route;
                    
                    return [
                        'id' => $booking->id,
                        'ticketNumber' => $booking->ticket_number,
                        'trainName' => $train->name,
                        'trainCode' => $train->code,
                        'bookingCode' => $booking->booking_code,
                        'from' => $route->departureStation->name,
                        'to' => $route->arrivalStation->name,
                        'departureTime' => $schedule->departure_time,
                        'arrivalTime' => $schedule->arrival_time,
                        'departureDate' => $schedule->departure_date ?? now()->toDateString(),
                        'duration' => $route->duration ?? '2 jam',
                        'passengerName' => $booking->passenger_name,
                        'nik' => $booking->nik,
                        'passengerType' => $booking->passenger_type,
                        'seatNumber' => $booking->seat_number,
                        'class' => $booking->class,
                        'coach' => substr($booking->seat_number, 0, 1),
                        'seat' => substr($booking->seat_number, 1),
                        'price' => $booking->price,
                        'qrCode' => $booking->qr_code,
                        'status' => $booking->status,
                        'createdAt' => $booking->created_at
                    ];
                });

            return response()->json($bookings);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving bookings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $booking = Booking::findOrFail($id);
            
            $user = auth()->user();
            if ($user && $user->id !== $booking->user_id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $schedule = $booking->schedule;
            $train = $schedule ? $schedule->train : null;

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'status' => $booking->status,
                    'train' => $train ? [
                        'id' => $train->id,
                        'name' => $train->name,
                        'code' => $train->code
                    ] : null,
                    'schedule' => $schedule ? [
                        'id' => $schedule->id,
                        'departure_time' => $schedule->departure_time,
                        'arrival_time' => $schedule->arrival_time
                    ] : null
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Booking $booking)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Booking $booking)
    {
        try {
            $user = auth()->user();
            if (!$user || $booking->user_id !== $user->id) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $validated = $request->validate([
                'status' => 'required|in:pending,confirmed,used,cancelled'
            ]);

            $booking->update([
                'status' => $validated['status']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking status updated',
                'data' => $booking
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update booking status after payment (Confirm booking)
     */
    public function confirmPayment(Request $request, $bookingCode)
    {
        try {
            $user = auth()->user();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $booking = Booking::where('booking_code', $bookingCode)
                ->where('user_id', $user->id)
                ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            // Update status to confirmed and generate ticket number
            $ticketNumber = 'TKT-' . strtoupper($bookingCode) . '-' . time();
            $booking->update([
                'status' => 'confirmed',
                'ticket_number' => $ticketNumber
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Booking confirmed after payment',
                'data' => $booking
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error confirming booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        //
    }
}
