<?php

namespace App\Http\Controllers\Api;

use App\Services\StationService;
use Illuminate\Http\Request;

class StationController extends ApiBaseController
{
    public function __construct(private StationService $stationService)
    {
    }

    // GET /api/v1/stations
    public function index()
    {
        $stations = $this->stationService->getAll();
        return $this->success($stations, 'Stations retrieved');
    }

    // GET /api/v1/stations/{code}
    public function show($code)
    {
        $station = $this->stationService->getByCode($code);
        
        if (!$station) {
            return $this->error('Station not found', null, 404);
        }

        return $this->success($station, 'Station retrieved');
    }

    // POST /api/v1/stations/search
    public function search(Request $request)
    {
        $q = $request->input('q');
        
        if (!$q) {
            return $this->error('Search query required', null, 400);
        }

        $stations = $this->stationService->search($q);
        return $this->success($stations, 'Search results');
    }

    // GET /api/v1/stations/city/{city}
    public function getByCity($city)
    {
        $stations = $this->stationService->getByCity($city);
        
        if (empty($stations)) {
            return $this->error('Stations not found', null, 404);
        }

        return $this->success($stations, 'Stations retrieved');
    }
}
