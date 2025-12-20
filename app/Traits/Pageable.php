<?php

namespace App\Traits;

/**
 * Trait untuk handling pagination
 */
trait Pageable
{
    /**
     * Get pagination parameters dari request
     */
    public function getPaginationParams(): array
    {
        $page = max(1, (int) request('page', 1));
        $perPage = min((int) request('per_page', 10), 100);

        return [
            'page' => $page,
            'per_page' => $perPage,
            'offset' => ($page - 1) * $perPage,
        ];
    }

    /**
     * Generate pagination metadata
     */
    public function getPaginationMeta(int $total, int $page, int $perPage): array
    {
        return [
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage),
            'from' => (($page - 1) * $perPage) + 1,
            'to' => min($page * $perPage, $total),
        ];
    }
}
