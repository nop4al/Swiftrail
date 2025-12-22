<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tracking;
use App\Models\Booking;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    /**
     * Get active booking tracking for current user
     */
    public function getActiveTracking(Request $request)
    {
        $user = $request->user();

        // Get active booking (status = confirmed or on_going)
        $activeBooking = Booking::where('user_id', $user->id)
            ->whereIn('status', ['confirmed', 'on_going'])
            ->with(['schedule.route.train', 'schedule.departureStation', 'schedule.arrivalStation'])
            ->latest('created_at')
            ->first();

        if (!$activeBooking) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada booking aktif',
                'data' => null
            ], 404);
        }

        // Get tracking data for this booking
        $tracking = Tracking::where('booking_id', $activeBooking->id)
            ->with(['schedule.route.train'])
            ->latest('updated_at')
            ->first();

        if (!$tracking) {
            return response()->json([
                'success' => false,
                'message' => 'Data tracking tidak ditemukan',
                'data' => null
            ], 404);
        }

        $train = $activeBooking->schedule->route->train;
        $schedule = $activeBooking->schedule;

        return response()->json([
            'success' => true,
            'data' => [
                'booking' => [
                    'id' => $activeBooking->id,
                    'reference_code' => $activeBooking->reference_code,
                    'status' => $activeBooking->status,
                ],
                'train' => [
                    'id' => $train->id,
                    'name' => $train->name,
                    'code' => $train->code,
                    'class' => $train->class,
                ],
                'schedule' => [
                    'id' => $schedule->id,
                    'departure_time' => $schedule->departure_time,
                    'arrival_time' => $schedule->arrival_time,
                    'departure_station' => [
                        'id' => $schedule->departureStation->id,
                        'name' => $schedule->departureStation->name,
                        'lat' => $schedule->departureStation->lat,
                        'lng' => $schedule->departureStation->lng,
                    ],
                    'arrival_station' => [
                        'id' => $schedule->arrivalStation->id,
                        'name' => $schedule->arrivalStation->name,
                        'lat' => $schedule->arrivalStation->lat,
                        'lng' => $schedule->arrivalStation->lng,
                    ],
                ],
                'tracking' => [
                    'id' => $tracking->id,
                    'current_station' => $tracking->current_station,
                    'status' => $tracking->status,
                    'lat' => (float)$tracking->lat,
                    'lng' => (float)$tracking->lng,
                    'delay_minutes' => $tracking->delay_minutes,
                    'notes' => $tracking->notes,
                    'updated_at' => $tracking->updated_at,
                ],
            ]
        ]);
    }

    /**
     * Get tracking history for a specific booking
     */
    public function getTrackingHistory(Request $request, $bookingId)
    {
        $user = $request->user();

        $booking = Booking::where('id', $bookingId)
            ->where('user_id', $user->id)
            ->first();

        if (!$booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking tidak ditemukan',
            ], 404);
        }

        $trackingHistory = Tracking::where('booking_id', $bookingId)
            ->orderBy('updated_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $trackingHistory
        ]);
    }

    /**
     * Update tracking status (Admin only)
     */
    public function updateTracking(Request $request, $trackingId)
    {
        $validated = $request->validate([
            'current_station' => 'required|string',
            'status' => 'required|in:departed,on_station,arrived,delayed,cancelled',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'delay_minutes' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
        ]);

        $tracking = Tracking::find($trackingId);

        if (!$tracking) {
            return response()->json([
                'success' => false,
                'message' => 'Tracking data tidak ditemukan',
            ], 404);
        }

        $tracking->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Tracking berhasil diperbarui',
            'data' => $tracking
        ]);
    }
}
