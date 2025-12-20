<?php

namespace App\Http\Controllers\Api;

use App\Models\Train;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TrainManagementController extends ApiBaseController
{
    // GET /api/v1/admin/trains
    public function index()
    {
        $trains = Train::with('schedules')->get();
        return $this->success($trains, 'Trains retrieved');
    }

    // POST /api/v1/admin/trains
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:trains',
            'name' => 'required|string',
            'type' => 'required|string',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:active,inactive,maintenance'
        ]);

        try {
            $train = Train::create($validated);
            return $this->success($train, 'Train created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create train: ' . $e->getMessage(), null, 400);
        }
    }

    // GET /api/v1/admin/trains/{id}
    public function show($id)
    {
        $train = Train::with('schedules')->find($id);
        
        if (!$train) {
            return $this->error('Train not found', null, 404);
        }

        return $this->success($train, 'Train retrieved');
    }

    // PUT /api/v1/admin/trains/{id}
    public function update(Request $request, $id)
    {
        $train = Train::find($id);
        
        if (!$train) {
            return $this->error('Train not found', null, 404);
        }

        $validated = $request->validate([
            'code' => 'sometimes|required|string|unique:trains,code,' . $id,
            'name' => 'sometimes|required|string',
            'type' => 'sometimes|required|string',
            'capacity' => 'sometimes|required|integer|min:1',
            'status' => 'sometimes|required|in:active,inactive,maintenance'
        ]);

        try {
            $train->update($validated);
            return $this->success($train, 'Train updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update train: ' . $e->getMessage(), null, 400);
        }
    }

    // DELETE /api/v1/admin/trains/{id}
    public function destroy($id)
    {
        $train = Train::find($id);
        
        if (!$train) {
            return $this->error('Train not found', null, 404);
        }

        try {
            $train->delete();
            return $this->success(null, 'Train deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete train: ' . $e->getMessage(), null, 400);
        }
    }

    // GET /api/v1/admin/stations
    public function getStations()
    {
        $stations = Station::all();
        return $this->success($stations, 'Stations retrieved');
    }

    // POST /api/v1/admin/stations
    public function storeStation(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:stations',
            'name' => 'required|string',
            'city' => 'required|string'
        ]);

        try {
            $station = Station::create($validated);
            return $this->success($station, 'Station created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create station: ' . $e->getMessage(), null, 400);
        }
    }

    // DELETE /api/v1/admin/stations/{id}
    public function destroyStation($id)
    {
        $station = Station::find($id);
        
        if (!$station) {
            return $this->error('Station not found', null, 404);
        }

        try {
            $station->delete();
            return $this->success(null, 'Station deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete station: ' . $e->getMessage(), null, 400);
        }
    }
}
