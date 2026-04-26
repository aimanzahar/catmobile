<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PetResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'breed' => $this->breed,
            'age' => $this->age,
            'weight' => $this->weight,
            'special_notes' => $this->special_notes,
            'image' => $this->image,
            'image_url' => $this->resource->imageUrl(null),
            'image_thumb_url' => $this->resource->imageUrl('100x100'),
        ];
    }
}
