<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'user' => new UserResource($this['user']),
            'pets' => PetResource::collection($this['pets']),
            'upcoming_bookings' => BookingResource::collection($this['upcoming_bookings']),
            'booking_history' => BookingResource::collection($this['booking_history']),
        ];
    }
}
