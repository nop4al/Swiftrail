<?php

namespace App\Http\Controllers\Api;

use App\Models\Train;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TrainTrackingController extends Controller
{
    /**
     * Get train tracking data with stops and tourism
     */
    public function show(string $train_code)
    {
        try {
            $train = Train::where('code', $train_code)
                ->with('stops.station')
                ->firstOrFail();

            $stops = $train->stops()->orderBy('sequence')->get();

            if ($stops->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No stops found for this train'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'name' => $train->name,
                    'code' => $train->code,
                    'number' => $train->code,
                    'type' => $train->type,
                    'capacity' => $train->capacity,
                    'from' => $stops->first()->station->name,
                    'to' => $stops->last()->station->name,
                    'currentKm' => 220.481,
                    'totalKm' => 800,
                    'speed' => 120,
                    'occupancy' => 75,
                    'delayMinutes' => 0,
                    'startTime' => $stops->first()->departure_time ?? $stops->first()->arrival_time,
                    'endTime' => $stops->last()->arrival_time ?? $stops->last()->departure_time,
                    'stops' => $stops->map(function ($stop) {
                        // Load tourism data separately
                        $tourism = DB::table('tourism')
                            ->where('station_id', $stop->station->id)
                            ->get()
                            ->map(function ($place) {
                                return [
                                    'id' => (int)$place->id,
                                    'name' => (string)$place->name,
                                    'category' => (string)$place->category,
                                    'description' => (string)$place->description,
                                    'image' => (string)$place->image,
                                    'rating' => (float)$place->rating,
                                    'reviews' => (int)$place->reviews,
                                    'address' => (string)$place->address,
                                    'hours' => (string)$place->hours,
                                    'phone' => (string)$place->phone,
                                    'website' => (string)$place->website,
                                ];
                            })
                            ->toArray();

                        return [
                            'sequence' => (int)$stop->sequence,
                            'station' => [
                                'id' => (int)$stop->station->id,
                                'name' => (string)$stop->station->name,
                                'code' => (string)$stop->station->code,
                                'city' => (string)$stop->station->city,
                                'latitude' => (float)$stop->station->latitude,
                                'longitude' => (float)$stop->station->longitude,
                                'tourism' => $tourism,
                            ],
                            'arrival_time' => (string)($stop->arrival_time ?? ''),
                            'departure_time' => (string)($stop->departure_time ?? ''),
                            'scheduledTime' => (string)($stop->arrival_time ?? $stop->departure_time ?? ''),
                        ];
                    })->values()->toArray(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
