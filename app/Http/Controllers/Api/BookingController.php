<?php

namespace App\Http\Controllers\Api;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\Train;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BookingController extends Controller
{
    /**
     * Get available trains for a route and date
     */
    public function getAvailableTrains(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'from_station' => 'required|string',
                'to_station' => 'required|string',
                'date' => 'required|date'
            ]);

            $fromStation = trim($request->from_station);
            $toStation = trim($request->to_station);
            $date = $request->date;

            // Get all trains with their stops
            $allTrains = Train::with('stops.station')->get();

            $filteredTrains = [];

            foreach ($allTrains as $train) {
                if (!$train->stops || $train->stops->count() === 0) {
                    continue;
                }

                // Sort stops by sequence
                $stops = $train->stops->sortBy('sequence');
                $stationNames = $stops->pluck('station.name')->map(fn($name) => trim($name))->toArray();

                // Find station indices with flexible matching
                $fromIdx = null;
                $toIdx = null;
                
                foreach ($stationNames as $idx => $stationName) {
                    // Match if one contains the other, or if key words match
                    $fromMatch = $this->stationNameMatch($stationName, $fromStation);
                    $toMatch = $this->stationNameMatch($stationName, $toStation);
                    
                    if ($fromMatch) {
                        $fromIdx = $idx;
                    }
                    if ($toMatch) {
                        $toIdx = $idx;
                    }
                }

                // Check if route matches
                if ($fromIdx !== null && $toIdx !== null && $fromIdx < $toIdx) {
                    $fromStop = $stops->values()->get($fromIdx);
                    $toStop = $stops->values()->get($toIdx);

                    if ($fromStop && $toStop) {
                        // Calculate total available seats (simplified - assume 75% capacity is available)
                        $availableSeats = intval($train->capacity * 0.75);
                        
                        $filteredTrains[] = [
                            'train_id' => $train->id,
                            'train_name' => $train->name,
                            'train_code' => $train->code,
                            'departure' => $fromStop->departure_time,
                            'arrival' => $toStop->arrival_time,
                            'duration' => $this->calculateDuration($fromStop->departure_time, $toStop->arrival_time),
                            'from_station' => $fromStation,
                            'to_station' => $toStation,
                            'train_type' => $train->type,
                            'capacity' => $train->capacity,
                            'seats_available' => $availableSeats,
                            'min_price' => 250000,
                            'max_price' => 450000,
                            'classes' => [
                                ['type' => 'economy', 'name' => 'Economy', 'price' => 250000, 'available' => intval($availableSeats * 0.5)],
                                ['type' => 'business', 'name' => 'Business', 'price' => 350000, 'available' => intval($availableSeats * 0.3)],
                                ['type' => 'executive', 'name' => 'Executive', 'price' => 450000, 'available' => intval($availableSeats * 0.2)],
                            ]
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => $filteredTrains
            ]);
        } catch (\Exception $e) {
            Log::error('BookingController.getAvailableTrains error:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /*Get available seats for a schedule*/
    public function getAvailableSeats($scheduleId): JsonResponse
    {
        try {
            $schedule = Schedule::with('train')->find($scheduleId);

            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Schedule not found'
                ], 404);
            }

            // Get booked seats
            $bookedSeats = Booking::where('schedule_id', $scheduleId)
                ->where('status', '!=', 'cancelled')
                ->pluck('seat_number')
                ->toArray();

            // Generate all seats based on train type
            $allSeats = $this->generateSeats($schedule->train->type, $schedule->train->capacity);

            // Separate available and booked
            $available = array_diff($allSeats, $bookedSeats);
            $booked = array_intersect($allSeats, $bookedSeats);

            return response()->json([
                'success' => true,
                'data' => [
                    'scheduleId' => $scheduleId,
                    'trainType' => $schedule->train->type,
                    'totalSeats' => count($allSeats),
                    'availableSeats' => count($available),
                    'bookedSeats' => count($booked),
                    'seatMap' => [
                        'available' => array_values($available),
                        'booked' => array_values($booked),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving seats: ' . $e->getMessage()
            ], 500);
        }
    }

    /* Create a booking */
    public function createBooking(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'schedule_id' => 'required|exists:schedules,id',
                'user_id' => 'required|exists:users,id',
                'passenger_name' => 'required|string',
                'nik' => 'nullable|string',
                'passenger_type' => 'required|in:Dewasa,Anak,Bayi',
                'seat_number' => 'required|string',
                'class' => 'required|string',
                'price' => 'required|numeric',
            ]);

            // Check if seat is already booked
            $existingBooking = Booking::where('schedule_id', $validated['schedule_id'])
                ->where('seat_number', $validated['seat_number'])
                ->where('status', '!=', 'cancelled')
                ->first();

            if ($existingBooking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Seat already booked'
                ], 409);
            }

            // Generate booking code
            $bookingCode = 'BK' . date('YmdHis') . random_int(1000, 9999);

            $booking = Booking::create([
                'booking_code' => $bookingCode,
                'user_id' => $validated['user_id'],
                'schedule_id' => $validated['schedule_id'],
                'passenger_name' => $validated['passenger_name'],
                'nik' => $validated['nik'],
                'passenger_type' => $validated['passenger_type'],
                'seat_number' => $validated['seat_number'],
                'class' => $validated['class'],
                'price' => $validated['price'],
                'status' => 'pending',
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $booking->id,
                    'bookingCode' => $booking->booking_code,
                    'status' => $booking->status,
                    'passengerName' => $booking->passenger_name,
                    'seatNumber' => $booking->seat_number,
                    'price' => $booking->price,
                    'createdAt' => $booking->created_at,
                ],
                'message' => 'Booking created successfully'
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /* Get booking details */
    public function getBooking($bookingId): JsonResponse
    {
        try {
            $booking = Booking::with(['user', 'schedule.train', 'schedule.route'])->find($bookingId);

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $booking->id,
                    'bookingCode' => $booking->booking_code,
                    'ticketNumber' => $booking->ticket_number,
                    'passengerName' => $booking->passenger_name,
                    'passengerType' => $booking->passenger_type,
                    'seatNumber' => $booking->seat_number,
                    'class' => $booking->class,
                    'price' => $booking->price,
                    'status' => $booking->status,
                    'trainName' => $booking->schedule->train->name,
                    'departureTime' => $booking->schedule->departure_time,
                    'arrivalTime' => $booking->schedule->arrival_time,
                    'fromStation' => $booking->schedule->route->departureStation->name,
                    'toStation' => $booking->schedule->route->arrivalStation->name,
                    'createdAt' => $booking->created_at,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /* Cancel booking */
    public function cancelBooking($bookingId): JsonResponse
    {
        try {
            $booking = Booking::find($bookingId);

            if (!$booking) {
                return response()->json([
                    'success' => false,
                    'message' => 'Booking not found'
                ], 404);
            }

            if ($booking->status === 'used' || $booking->status === 'cancelled') {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel this booking'
                ], 409);
            }

            $booking->update(['status' => 'cancelled']);

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling booking: ' . $e->getMessage()
            ], 500);
        }
    }

    /* Generate seat layout based on train type */
    private function generateSeats($trainType, $capacity): array
    {
        $seats = [];

        if ($trainType === 'compartment') {
            // Compartment: 2 seats per row (A, B)
            $rows = ceil($capacity / 2);
            for ($i = 1; $i <= $rows; $i++) {
                $seats[] = "{$i}A";
                $seats[] = "{$i}B";
            }
        } else {
            // Standard: 4 seats per row (A, B, C, D)
            $rows = ceil($capacity / 4);
            for ($i = 1; $i <= $rows; $i++) {
                $seats[] = "{$i}A";
                $seats[] = "{$i}B";
                $seats[] = "{$i}C";
                $seats[] = "{$i}D";
            }
        }

        return array_slice($seats, 0, $capacity);
    }

    /**
     * Helper: Calculate duration between times
     */
    private function calculateDuration($departure, $arrival)
    {
        try {
            $dep = \DateTime::createFromFormat('H:i:s', $departure);
            $arr = \DateTime::createFromFormat('H:i:s', $arrival);

            if (!$dep || !$arr) {
                return '5h 30m'; // Default fallback
            }

            if ($arr < $dep) {
                $arr->add(new \DateInterval('P1D'));
            }

            $interval = $dep->diff($arr);
            return $interval->h . 'h ' . $interval->i . 'm';
        } catch (\Exception $e) {
            return '5h 30m'; // Default fallback on error
        }
    }

    /**
     * Helper: Get price for class
     */
    private function getPriceForClass($class)
    {
        $prices = [
            'ekonomi' => 250000,
            'bisnis' => 350000,
            'eksekutif' => 450000
        ];

        return $prices[$class] ?? 250000;
    }

    /**
     * Helper: Match station names flexibly
     * Matches if one string contains the other, or if key words overlap (minimum 1 word)
     */
    private function stationNameMatch($dbName, $searchName)
    {
        // Direct substring match (case insensitive)
        if (stripos($dbName, $searchName) !== false || stripos($searchName, $dbName) !== false) {
            return true;
        }
        
        // Check if key words overlap
        $dbWords = array_filter(preg_split('/\s+/', strtolower($dbName)));
        $searchWords = array_filter(preg_split('/\s+/', strtolower($searchName)));
        
        if (empty($searchWords)) {
            return false;
        }
        
        // If at least 1 or more words match, consider it a match
        $matches = count(array_intersect($dbWords, $searchWords));
        return $matches >= 1;
    }
}
