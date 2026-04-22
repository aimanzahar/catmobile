<?php

namespace App\Models;

use Carbon\Carbon;

class TaxiRequest
{
    public function __construct(
        public readonly string $id,
        public readonly string $pickup_address,
        public readonly string $status = 'pending',
        public readonly ?Carbon $scheduled_at = null,
    ) {}

    public static function fromRecord(array $record): self
    {
        $raw = $record['scheduled_at'] ?? null;

        return new self(
            id: (string) ($record['id'] ?? ''),
            pickup_address: (string) ($record['pickup_address'] ?? ''),
            status: (string) ($record['status'] ?? 'pending'),
            scheduled_at: $raw ? Carbon::parse($raw) : null,
        );
    }
}
