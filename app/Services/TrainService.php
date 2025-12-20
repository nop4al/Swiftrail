<?php

namespace App\Services;

use App\Constants\TransitConstants;

/**
 * TrainService - Menangani semua logika bisnis untuk Kereta
 */
class TrainService
{
    /**
     * Get semua kereta
     */
    public function getAll(): array
    {
        return TransitConstants::TRAINS;
    }

    /**
     * Get kereta berdasarkan ID
     */
    public function getById(int $id): ?array
    {
        $trains = TransitConstants::TRAINS;

        foreach ($trains as $train) {
            if ($train['id'] === $id) {
                return $this->enrichTrainData($train);
            }
        }

        return null;
    }

    /**
     * Search kereta berdasarkan kriteria
     */
    public function search(array $criteria): array
    {
        $trains = TransitConstants::TRAINS;
        $results = [];

        foreach ($trains as $train) {
            // Filter berdasarkan berbagai kriteria
            $matches = true;

            // Filter berdasarkan class jika ada
            if (!empty($criteria['class']) && $train['class'] !== $criteria['class']) {
                $matches = false;
            }

            // Filter berdasarkan minimum harga
            if (!empty($criteria['min_price']) && $train['price'] < $criteria['min_price']) {
                $matches = false;
            }

            // Filter berdasarkan maksimum harga
            if (!empty($criteria['max_price']) && $train['price'] > $criteria['max_price']) {
                $matches = false;
            }

            // Filter berdasarkan ketersediaan kursi
            if (!empty($criteria['min_seats']) && $train['seats_available'] < $criteria['min_seats']) {
                $matches = false;
            }

            if ($matches) {
                $results[] = $train;
            }
        }

        return $results;
    }

    /**
     * Get kereta berdasarkan class
     */
    public function getByClass(string $class): array
    {
        $trains = TransitConstants::TRAINS;

        return array_filter($trains, function ($train) use ($class) {
            return strtolower($train['class']) === strtolower($class);
        });
    }

    /**
     * Calculate total price untuk multiple passengers
     */
    public function calculateTotal(int $trainId, int $passengers): ?array
    {
        $train = $this->getById($trainId);

        if (!$train) {
            return null;
        }

        $pricePerPassenger = $train['price'];
        $totalPrice = $pricePerPassenger * $passengers;

        return [
            'train_id' => $trainId,
            'train_name' => $train['name'],
            'price_per_passenger' => $pricePerPassenger,
            'passengers' => $passengers,
            'total_price' => $totalPrice,
            'currency' => 'IDR'
        ];
    }

    /**
     * Enrich train data dengan informasi tambahan
     */
    private function enrichTrainData(array $train): array
    {
        return array_merge($train, [
            'coaches' => 8,
            'total_seats' => 280,
            'status' => 'Available'
        ]);
    }

    /**
     * Get total kereta
     */
    public function count(): int
    {
        return count(TransitConstants::TRAINS);
    }
}
