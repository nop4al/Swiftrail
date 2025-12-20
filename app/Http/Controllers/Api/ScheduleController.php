<?php

namespace App\Http\Controllers\Api;

use App\Models\Schedule;
use App\Models\Train;
use App\Models\Route;
use Illuminate\Http\Request;

class ScheduleController extends ApiBaseController
{
    // GET /api/v1/admin/schedules
    public function index()
    {
        $schedules = Schedule::with(['train', 'route'])->get();
        return $this->success($schedules, 'Schedules retrieved');
    }

    // POST /api/v1/admin/schedules
    public function store(Request $request)
    {
        $validated = $request->validate([
            'train_id' => 'required|exists:trains,id',
            'route_id' => 'required|exists:routes,id',
            'departure_time' => 'required|date_format:H:i',
            'arrival_time' => 'required|date_format:H:i',
            'days' => 'required|string',
            'status' => 'required|in:active,inactive'
        ]);

        try {
            $schedule = Schedule::create($validated);
            $schedule->load(['train', 'route']);
            return $this->success($schedule, 'Schedule created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create schedule: ' . $e->getMessage(), null, 400);
        }
    }

    // GET /api/v1/admin/schedules/{id}
    public function show($id)
    {
        $schedule = Schedule::with(['train', 'route'])->find($id);
        
        if (!$schedule) {
            return $this->error('Schedule not found', null, 404);
        }

        return $this->success($schedule, 'Schedule retrieved');
    }

    // PUT /api/v1/admin/schedules/{id}
    public function update(Request $request, $id)
    {
        $schedule = Schedule::find($id);
        
        if (!$schedule) {
            return $this->error('Schedule not found', null, 404);
        }

        $validated = $request->validate([
            'train_id' => 'sometimes|required|exists:trains,id',
            'route_id' => 'sometimes|required|exists:routes,id',
            'departure_time' => 'sometimes|required|date_format:H:i',
            'arrival_time' => 'sometimes|required|date_format:H:i',
            'days' => 'sometimes|required|string',
            'status' => 'sometimes|required|in:active,inactive'
        ]);

        try {
            $schedule->update($validated);
            $schedule->load(['train', 'route']);
            return $this->success($schedule, 'Schedule updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update schedule: ' . $e->getMessage(), null, 400);
        }
    }

    // DELETE /api/v1/admin/schedules/{id}
    public function destroy($id)
    {
        $schedule = Schedule::find($id);
        
        if (!$schedule) {
            return $this->error('Schedule not found', null, 404);
        }

        try {
            $schedule->delete();
            return $this->success(null, 'Schedule deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete schedule: ' . $e->getMessage(), null, 400);
        }
    }

    // GET /api/v1/admin/trains/{trainId}/schedules
    public function getTrainSchedules($trainId)
    {
        $train = Train::find($trainId);
        
        if (!$train) {
            return $this->error('Train not found', null, 404);
        }

        $schedules = Schedule::where('train_id', $trainId)->with('route')->get();
        return $this->success($schedules, 'Train schedules retrieved');
    }

    // POST /api/v1/admin/trains/{trainId}/schedules
    public function addScheduleToTrain(Request $request, $trainId)
    {
        $train = Train::find($trainId);
        
        if (!$train) {
            return $this->error('Train not found', null, 404);
        }

        $validated = $request->validate([
            'route_id' => 'required|exists:routes,id',
            'departure_time' => 'required|date_format:H:i',
            'arrival_time' => 'required|date_format:H:i',
            'days' => 'required|string',
            'status' => 'required|in:active,inactive'
        ]);

        try {
            $schedule = Schedule::create([
                'train_id' => $trainId,
                'route_id' => $validated['route_id'],
                'departure_time' => $validated['departure_time'],
                'arrival_time' => $validated['arrival_time'],
                'days' => $validated['days'],
                'status' => $validated['status']
            ]);
            
            $schedule->load(['train', 'route']);
            return $this->success($schedule, 'Schedule added to train successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to add schedule: ' . $e->getMessage(), null, 400);
        }
    }

    // DELETE /api/v1/admin/trains/{trainId}/schedules/{scheduleId}
    public function removeScheduleFromTrain($trainId, $scheduleId)
    {
        $schedule = Schedule::where('id', $scheduleId)
                            ->where('train_id', $trainId)
                            ->first();
        
        if (!$schedule) {
            return $this->error('Schedule not found for this train', null, 404);
        }

        try {
            $schedule->delete();
            return $this->success(null, 'Schedule removed from train successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to remove schedule: ' . $e->getMessage(), null, 400);
        }
    }
}
