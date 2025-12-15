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
    public function show(Booking $booking)
    {
        //
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
