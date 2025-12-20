<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi untuk API SwiftRail
    |
    */

    'version' => '1.0.0',

    'name' => 'SwiftRail API',

    'prefix' => 'api',

    'version_prefix' => 'v1',

    // Rate limiting
    'rate_limit' => [
        'enabled' => true,
        'requests_per_minute' => 60,
        'requests_per_hour' => 1000,
    ],

    // Pagination
    'pagination' => [
        'default_per_page' => 10,
        'max_per_page' => 100,
    ],

    // Response format
    'response' => [
        'include_timestamp' => true,
        'include_api_version' => true,
        'include_request_id' => true,
    ],

    // CORS
    'cors' => [
        'enabled' => true,
        'allowed_origins' => [
            'localhost',
            'localhost:3000',
            'localhost:5173',
        ],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization'],
    ],
];
