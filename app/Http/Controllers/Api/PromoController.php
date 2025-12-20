<?php

namespace App\Http\Controllers\Api;

use App\Services\PromoService;
use Illuminate\Http\Request;

class PromoController extends ApiBaseController
{
    public function __construct(private PromoService $promoService)
    {
    }

    // GET /api/v1/promos
    public function index()
    {
        $promos = $this->promoService->getAll();
        return $this->success($promos, 'Promos retrieved');
    }

    // GET /api/v1/promos/{id}
    public function show($id)
    {
        $promo = $this->promoService->getById($id);
        
        if (!$promo) {
            return $this->error('Promo not found', null, 404);
        }

        return $this->success($promo, 'Promo retrieved');
    }

    // POST /api/v1/promos/search
    public function search(Request $request)
    {
        $filters = [
            'label' => $request->input('label'),
            'search' => $request->input('search')
        ];

        $promos = $this->promoService->search($filters);
        return $this->success($promos, 'Search results');
    }
}
