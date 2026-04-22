<?php

namespace App\Models;

use Carbon\Carbon;

class TimeSlot
{
    public function __construct(
        public readonly string $id,
        public readonly ?Carbon $date = null,
        public readonly ?string $start_time = null,
        public readonly ?string $end_time = null,
        public readonly bool $is_available = true,
        public readonly int $max_bookings = 0,
    ) {}

    public static function fromRecord(array $record): self
    {
        $rawDate = $record['date'] ?? null;

        return new self(
            id: (string) ($record['id'] ?? ''),
            date: $rawDate ? Carbon::parse($rawDate) : null,
            start_time: $record['start_time'] ?? null,
            end_time: $record['end_time'] ?? null,
            is_available: (bool) ($record['is_available'] ?? true),
            max_bookings: (int) ($record['max_bookings'] ?? 0),
        );
    }
}
