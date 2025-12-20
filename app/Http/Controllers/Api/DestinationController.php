<?php

namespace App\Http\Controllers\Api;

use App\Services\DestinationService;
use Illuminate\Http\Request;

class DestinationController extends ApiBaseController
{
    public function __construct(private DestinationService $destinationService)
    {
    }

    // GET /api/v1/destinations
    public function index()
    {
        $destinations = $this->destinationService->getAll();
        return $this->success($destinations, 'Destinations retrieved');
    }

    // GET /api/v1/destinations/{id}
    public function show($id)
    {
        $destination = $this->destinationService->getById($id);
        
        if (!$destination) {
            return $this->error('Destination not found', null, 404);
        }

        return $this->success($destination, 'Destination retrieved');
    }

    // POST /api/v1/destinations/search
    public function search(Request $request)
    {
        $q = $request->input('q');
        
        if (!$q) {
            return $this->error('Search query required', null, 400);
        }

        $destinations = $this->destinationService->search($q);
        return $this->success($destinations, 'Search results');
    }
}
