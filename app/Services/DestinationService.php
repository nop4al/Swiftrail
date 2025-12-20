<?php

namespace App\Services;

use App\Constants\TransitConstants;

/**
 * DestinationService - Menangani semua logika bisnis untuk Destinasi
 */
class DestinationService
{
    /**
     * Get semua destinasi
     */
    public function getAll(): array
    {
        return TransitConstants::DESTINATIONS;
    }

    /**
     * Get destinasi berdasarkan ID
     */
    public function getById(int $id): ?array
    {
        $destinations = TransitConstants::DESTINATIONS;
        
        foreach ($destinations as $destination) {
            if ($destination['id'] === $id) {
                return $destination;
            }
        }

        return null;
    }

    /**
     * Get destinasi berdasarkan nama kota
     */
    public function getByName(string $name): ?array
    {
        $destinations = TransitConstants::DESTINATIONS;
        $name = strtolower($name);

        foreach ($destinations as $destination) {
            if (strtolower($destination['name']) === $name) {
                return $destination;
            }
        }

        return null;
    }

    /**
     * Search destinasi
     */
    public function search(string $query): array
    {
        $destinations = TransitConstants::DESTINATIONS;
        $query = strtolower($query);

        return array_filter($destinations, function ($destination) use ($query) {
            return str_contains(strtolower($destination['name']), $query) ||
                   str_contains(strtolower($destination['subtitle']), $query) ||
                   str_contains(strtolower($destination['description']), $query);
        });
    }

    /**
     * Get total destinasi
     */
    public function count(): int
    {
        return count(TransitConstants::DESTINATIONS);
    }
}
