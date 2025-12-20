<?php

namespace App\Http\Controllers\Api;

use App\Models\Train;
use App\Models\Schedule;
use App\Models\Route;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TrainScheduleController extends Controller
{
    /* Get all available trains with schedules */
    public function getSchedules(Request $request): JsonResponse
    {
        try {
            $from = $request->query('from');
            $to = $request->query('to');
            $date = $request->query('date');
            $trainClass = $request->query('trainClass');

            $query = Schedule::with(['train', 'route.departureStation', 'route.arrivalStation'])
                ->where('status', '!=', 'cancelled');

            // Filter by route
            if ($from || $to) {
                $query->whereHas('route', function ($q) use ($from, $to) {
                    if ($from) {
                        $q->whereHas('departureStation', function ($sq) use ($from) {
                            $sq->where('code', 'like', "%{$from}%")
                                ->orWhere('name', 'like', "%{$from}%");
                        });
                    }
                    if ($to) {
                        $q->whereHas('arrivalStation', function ($sq) use ($to) {
                            $sq->where('code', 'like', "%{$to}%")
                                ->orWhere('name', 'like', "%{$to}%");
                        });
                    }
                });
            }

            // Filter by date (check if the schedule runs on the requested day of week)
            if ($date) {
                $dayOfWeek = date('l', strtotime($date)); // Get day name (e.g., 'Monday')
                $query->whereRaw("FIND_IN_SET(?, days)", [$dayOfWeek]);
            }

            $schedules = $query->get()->map(function ($schedule) {
                return [
                    'id' => $schedule->id,
                    'trainId' => $schedule->train_id,
                    'trainName' => $schedule->train->name,
                    'trainNumber' => $schedule->train->code,
                    'trainType' => $schedule->train->type,
                    'departureTime' => $schedule->departure_time,
                    'arrivalTime' => $schedule->arrival_time,
                    'duration' => $this->calculateDuration($schedule->departure_time, $schedule->arrival_time),
                    'fromStation' => $schedule->route->departureStation->name,
                    'fromStationCode' => $schedule->route->departureStation->code,
                    'toStation' => $schedule->route->arrivalStation->name,
                    'toStationCode' => $schedule->route->arrivalStation->code,
                    'basePrice' => $schedule->route->base_price,
                    'status' => $schedule->status,
                    'capacity' => $schedule->train->capacity,
                    'availableSeats' => $this->getAvailableSeats($schedule->id),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $schedules,
                'message' => 'Schedules retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving schedules: ' . $e->getMessage()
            ], 500);
        }
    }

    /* Get schedule details with seat info */
    public function getScheduleDetail($scheduleId): JsonResponse
    {
        try {
            $schedule = Schedule::with(['train', 'route.departureStation', 'route.arrivalStation'])->find($scheduleId);

            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'Schedule not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $schedule->id,
                    'trainId' => $schedule->train_id,
                    'trainName' => $schedule->train->name,
                    'trainNumber' => $schedule->train->code,
                    'trainType' => $schedule->train->type,
                    'departureTime' => $schedule->departure_time,
                    'arrivalTime' => $schedule->arrival_time,
                    'fromStation' => $schedule->route->departureStation->name,
                    'toStation' => $schedule->route->arrivalStation->name,
                    'basePrice' => $schedule->route->base_price,
                    'capacity' => $schedule->train->capacity,
                    'status' => $schedule->status,
                    'availableSeats' => $this->getAvailableSeats($schedule->id),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving schedule: ' . $e->getMessage()
            ], 500);
        }
    }

    /* Get available seats for a schedule */
    private function getAvailableSeats($scheduleId): int
    {
        $schedule = Schedule::find($scheduleId);
        $bookedSeats = \App\Models\Booking::where('schedule_id', $scheduleId)
            ->where('status', '!=', 'cancelled')
            ->count();
        
        return max(0, $schedule->train->capacity - $bookedSeats);
    }

    /* Calculate duration between two times */
    private function calculateDuration($departure, $arrival): string
    {
        $dep = \Carbon\Carbon::createFromFormat('H:i:s', $departure);
        $arr = \Carbon\Carbon::createFromFormat('H:i:s', $arrival);
        
        if ($arr->lessThan($dep)) {
            $arr->addDay();
        }
        
        $diff = $arr->diffInMinutes($dep);
        $hours = intval($diff / 60);
        $minutes = $diff % 60;
        
        return "{$hours}h {$minutes}m";
    }
}
