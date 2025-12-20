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
        $user = auth()->user();
        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $bookings = Booking::where('user_id', $user->id)
            ->with(['schedule.train', 'schedule.route.departure_station', 'schedule.route.arrival_station'])
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
                    'bookingCode' => $booking->booking_code,
                    'from' => $route->departure_station->name,
                    'to' => $route->arrival_station->name,
                    'departureTime' => $schedule->departure_time,
                    'arrivalTime' => $schedule->arrival_time,
                    'departureDate' => $schedule->created_at,
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
            // Find by id, booking_code, or ticket_number
            $booking = Booking::where(function ($query) use ($id) {
                $query->where('id', $id)
                    ->orWhere('booking_code', $id)
                    ->orWhere('ticket_number', $id);
            })
            ->with(['schedule.train', 'schedule.fromStation', 'schedule.toStation'])
            ->first();

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            $user = auth()->user();
            if ($user && $user->id !== $booking->user_id && $user->role !== 'admin') {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized'
                ], 403);
            }

            $schedule = $booking->schedule;
            $train = $schedule->train;

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $booking->id,
                    'booking_code' => $booking->booking_code,
                    'ticket_number' => $booking->ticket_number,
                    'qr_code' => $booking->qr_code,
                    'passenger_name' => $booking->passenger_name,
                    'nik' => $booking->nik,
                    'passenger_type' => $booking->passenger_type,
                    'seat_number' => $booking->seat_number,
                    'class' => $booking->class,
                    'price' => $booking->price,
                    'status' => $booking->status,
                    'created_at' => $booking->created_at,
                    'train' => [
                        'id' => $train->id,
                        'name' => $train->name,
                        'number' => $train->train_number ?? 'N/A',
                        'type' => $train->type ?? 'N/A'
                    ],
                    'schedule' => [
                        'id' => $schedule->id,
                        'departure_time' => $schedule->departure_time,
                        'arrival_time' => $schedule->arrival_time,
                        'travel_date' => $schedule->travel_date,
                        'available_seats' => $schedule->available_seats ?? 0,
                        'price' => $schedule->price
                    ],
                    'from_station' => [
                        'id' => $schedule->fromStation->id ?? null,
                        'name' => $schedule->fromStation->name ?? 'N/A',
                        'code' => $schedule->fromStation->code ?? 'N/A'
                    ],
                    'to_station' => [
                        'id' => $schedule->toStation->id ?? null,
                        'name' => $schedule->toStation->name ?? 'N/A',
                        'code' => $schedule->toStation->code ?? 'N/A'
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
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
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Booking $booking)
    {
        //
    }
}
