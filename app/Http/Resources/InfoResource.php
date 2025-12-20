<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InfoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'app_name' => $this['app_name'],
            'app_subtitle' => $this['app_subtitle'],
            'version' => $this['version'],
            'description' => $this['description'],
            'features' => $this['features'],
            'timestamp' => now()->toIso8601String(),
        ];
    }
}
