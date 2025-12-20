<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this['id'],
            'name' => $this['name'],
            'label' => $this['label'],
            'icon' => $this['icon'],
            'discount' => $this['discount'],
            'shortDesc' => $this['shortDesc'],
            'image' => $this['image'],
            'description' => $this['description'],
            'duration' => $this['duration'],
            'terms' => $this['terms'] ?? [],
        ];
    }
}
