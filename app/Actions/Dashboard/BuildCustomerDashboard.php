<?php

namespace App\Actions\Dashboard;

use App\Models\Booking;
use App\Models\Pet;
use App\Models\User;
use App\Services\PocketBase\PocketBaseClient;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;

class BuildCustomerDashboard
{
    public function __construct(private readonly PocketBaseClient $client) {}

    public function handle(User $user): array
    {
        if ($user->pocketbase_token === null) {
            throw new RuntimeException('Authenticated user is missing a PocketBase token.');
        }

        $token = $user->pocketbase_token;

        $petsResponse = $this->client->listRecords('cg_pets', $token, [
            'filter' => "user='{$user->id}'",
            'sort' => 'name',
            'perPage' => 200,
        ]);
        $pets = collect($petsResponse['items'] ?? [])
            ->map(fn (array $record) => Pet::fromRecord($record))
            ->values();

        $bookingsResponse = $this->client->listRecords('cg_bookings', $token, [
            'filter' => "user='{$user->id}'",
            'expand' => 'pet,service,time_slot,cg_taxi_requests_via_booking',
            'perPage' => 200,
        ]);
        $bookings = collect($bookingsResponse['items'] ?? [])
            ->map(fn (array $record) => Booking::fromRecord($record));

        $now = Carbon::now();

        $upcoming = $bookings
            ->filter(fn (Booking $booking) => $this->isUpcoming($booking, $now))
            ->sortBy(fn (Booking $booking) => $this->slotStartsAt($booking)?->timestamp ?? PHP_INT_MAX)
            ->values();

        $history = $bookings
            ->reject(fn (Booking $booking) => $this->isUpcoming($booking, $now))
            ->sortByDesc(fn (Booking $booking) => $this->slotStartsAt($booking)?->timestamp ?? 0)
            ->values();

        return [
            'user' => $user,
            'pets' => $pets,
            'upcoming_bookings' => $upcoming,
            'booking_history' => $history,
        ];
    }

    private function isUpcoming(Booking $booking, CarbonInterface $now): bool
    {
        $slotStartsAt = $this->slotStartsAt($booking);

        return in_array($booking->status, ['pending', 'confirmed', 'in_progress'], true)
            && $slotStartsAt !== null
            && $slotStartsAt->greaterThanOrEqualTo($now);
    }

    private function slotStartsAt(Booking $booking): ?Carbon
    {
        $timeSlot = $booking->timeSlot;
        if ($timeSlot === null || $timeSlot->date === null) {
            return null;
        }

        return Carbon::parse(sprintf(
            '%s %s',
            $timeSlot->date->toDateString(),
            $timeSlot->start_time ?? '00:00',
        ));
    }
}
