<?php

namespace App\Models;

class Booking
{
    public function __construct(
        public readonly string $id,
        public readonly string $status = 'pending',
        public readonly string $payment_method = 'cash',
        public readonly string $payment_status = 'unpaid',
        public readonly float $total_price = 0.0,
        public readonly ?string $notes = null,
        public readonly ?Pet $pet = null,
        public readonly ?Service $service = null,
        public readonly ?TimeSlot $timeSlot = null,
        public readonly ?TaxiRequest $taxiRequest = null,
    ) {}

    public static function fromRecord(array $record): self
    {
        $expand = $record['expand'] ?? [];
        $pet = isset($expand['pet']) && is_array($expand['pet']) ? Pet::fromRecord($expand['pet']) : null;
        $service = isset($expand['service']) && is_array($expand['service']) ? Service::fromRecord($expand['service']) : null;
        $timeSlot = isset($expand['time_slot']) && is_array($expand['time_slot']) ? TimeSlot::fromRecord($expand['time_slot']) : null;

        $taxi = $expand['cg_taxi_requests_via_booking'] ?? null;
        $taxiRequest = null;
        if (is_array($taxi) && isset($taxi[0]) && is_array($taxi[0])) {
            $taxiRequest = TaxiRequest::fromRecord($taxi[0]);
        }

        return new self(
            id: (string) ($record['id'] ?? ''),
            status: (string) ($record['status'] ?? 'pending'),
            payment_method: (string) ($record['payment_method'] ?? 'cash'),
            payment_status: (string) ($record['payment_status'] ?? 'unpaid'),
            total_price: (float) ($record['total_price'] ?? 0),
            notes: $record['notes'] ?? null,
            pet: $pet,
            service: $service,
            timeSlot: $timeSlot,
            taxiRequest: $taxiRequest,
        );
    }
}
