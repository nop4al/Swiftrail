<?php

namespace App\Services;

use App\Constants\TransitConstants;

/**
 * StationService - Menangani semua logika bisnis untuk Stasiun
 */
class StationService
{
    /**
     * Get semua stasiun
     */
    public function getAll(): array
    {
        return TransitConstants::STATIONS;
    }

    /**
     * Get stasiun berdasarkan kode
     */
    public function getByCode(string $code): ?array
    {
        $stations = TransitConstants::STATIONS;
        $code = strtoupper($code);

        foreach ($stations as $station) {
            if ($station['code'] === $code) {
                return $this->enrichStationData($station);
            }
        }

        return null;
    }

    /**
     * Get stasiun berdasarkan kota
     */
    public function getByCity(string $city): array
    {
        $stations = TransitConstants::STATIONS;
        $city = strtolower($city);

        return array_filter($stations, function ($station) use ($city) {
            return strtolower($station['city']) === $city;
        });
    }

    /**
     * Search stasiun
     */
    public function search(string $query): array
    {
        $stations = TransitConstants::STATIONS;
        $query = strtolower($query);

        return array_filter($stations, function ($station) use ($query) {
            return str_contains(strtolower($station['name']), $query) ||
                   str_contains(strtolower($station['code']), $query) ||
                   str_contains(strtolower($station['city']), $query);
        });
    }

    /**
     * Enrich station data dengan informasi tambahan
     */
    private function enrichStationData(array $station): array
    {
        return array_merge($station, [
            'address' => 'Jl. Merdeka Utara, ' . $station['city'],
            'phone' => '+62-21-XXXXX',
            'facilities' => [
                'Toilet',
                'Warung makan',
                'Parkir',
                'ATM',
                'Loket tiket'
            ]
        ]);
    }

    /**
     * Get total stasiun
     */
    public function count(): int
    {
        return count(TransitConstants::STATIONS);
    }
}
