<?php

namespace App\Http\Controllers\Api;

use App\Services\TrainService;
use Illuminate\Http\Request;

class TrainController extends ApiBaseController
{
    public function __construct(private TrainService $trainService)
    {
    }

    // GET /api/v1/trains
    public function index()
    {
        $trains = $this->trainService->getAll();
        return $this->success($trains, 'Trains retrieved');
    }

    // GET /api/v1/trains/{id}
    public function show($id)
    {
        $train = $this->trainService->getById($id);
        
        if (!$train) {
            return $this->error('Train not found', null, 404);
        }

        return $this->success($train, 'Train retrieved');
    }

    // POST /api/v1/trains/search
    public function search(Request $request)
    {
        $filters = [
            'class' => $request->input('class'),
            'min_price' => $request->input('min_price'),
            'max_price' => $request->input('max_price'),
        ];

        $trains = $this->trainService->search($filters);
        return $this->success($trains, 'Search results');
    }

    // GET /api/v1/trains/class/{class}
    public function getByClass($class)
    {
        $trains = $this->trainService->getByClass($class);
        
        if (empty($trains)) {
            return $this->error('Trains not found', null, 404);
        }

        return $this->success($trains, 'Trains retrieved');
    }

    // POST /api/v1/trains/{id}/calculate-price
    public function calculatePrice($id, Request $request)
    {
        $train = $this->trainService->getById($id);
        
        if (!$train) {
            return $this->error('Train not found', null, 404);
        }

        $passengers = $request->input('passengers', 1);
        $result = $this->trainService->calculateTotal($id, $passengers);
        return $this->success($result, 'Price calculated');
    }
}
