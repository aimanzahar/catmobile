<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,
            'total_price' => $this->total_price,
            'notes' => $this->notes,
            'pet' => $this->pet ? [
                'id' => $this->pet->id,
                'name' => $this->pet->name,
            ] : null,
            'service' => $this->service ? [
                'id' => $this->service->id,
                'name' => $this->service->name,
                'slug' => $this->service->slug,
                'price' => $this->service->price,
                'duration_minutes' => $this->service->duration_minutes,
            ] : null,
            'time_slot' => $this->timeSlot ? [
                'id' => $this->timeSlot->id,
                'date' => $this->timeSlot->date?->toDateString(),
                'start_time' => $this->timeSlot->start_time,
                'end_time' => $this->timeSlot->end_time,
            ] : null,
            'taxi_request' => $this->taxiRequest ? [
                'id' => $this->taxiRequest->id,
                'pickup_address' => $this->taxiRequest->pickup_address,
                'status' => $this->taxiRequest->status,
                'scheduled_at' => $this->taxiRequest->scheduled_at?->toIso8601String(),
            ] : null,
        ];
    }
}
