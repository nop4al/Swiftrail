<?php

namespace App\Services;

use App\Constants\TransitConstants;

/**
 * InfoService - Menangani informasi aplikasi
 */
class InfoService
{
    /**
     * Get app info
     */
    public function getAppInfo(): array
    {
        return TransitConstants::APP_INFO;
    }

    /**
     * Get app version
     */
    public function getVersion(): string
    {
        return TransitConstants::APP_INFO['version'];
    }

    /**
     * Get app name
     */
    public function getAppName(): string
    {
        return TransitConstants::APP_INFO['app_name'];
    }

    /**
     * Get features
     */
    public function getFeatures(): array
    {
        return TransitConstants::APP_INFO['features'];
    }
}
