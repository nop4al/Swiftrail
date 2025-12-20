<?php

namespace App\Services;

use App\Constants\TransitConstants;

/**
 * PromoService - Menangani semua logika bisnis untuk Promo
 */
class PromoService
{
    /**
     * Get semua promos
     */
    public function getAll(): array
    {
        return TransitConstants::PROMOS;
    }

    /**
     * Get promo berdasarkan ID
     */
    public function getById(int $id): ?array
    {
        $promos = TransitConstants::PROMOS;
        
        foreach ($promos as $promo) {
            if ($promo['id'] === $id) {
                return $promo;
            }
        }

        return null;
    }

    /**
     * Search promos berdasarkan criteria
     */
    public function search(array $filters): array
    {
        $promos = TransitConstants::PROMOS;

        if (!empty($filters['label'])) {
            $promos = array_filter($promos, function ($promo) use ($filters) {
                return str_contains(strtolower($promo['label']), strtolower($filters['label']));
            });
        }

        if (!empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $promos = array_filter($promos, function ($promo) use ($search) {
                return str_contains(strtolower($promo['name']), $search) ||
                       str_contains(strtolower($promo['description']), $search);
            });
        }

        return array_values($promos);
    }

    /**
     * Get total promos
     */
    public function count(): int
    {
        return count(TransitConstants::PROMOS);
    }
}
