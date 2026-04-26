<?php

namespace App\Actions\Bookings;

use App\Models\Booking;
use App\Models\Service;
use App\Models\User;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class CreateBooking
{
    public function __construct(private readonly PocketBaseClient $client) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(User $user, Service $service, array $payload): Booking
    {
        if ($user->pocketbase_token === null) {
            throw new RuntimeException('Authenticated user is missing a PocketBase token.');
        }

        $configuredSlots = (array) config('booking.slots', []);
        $startTime = (string) $payload['start_time'];
        $bookingDate = (string) $payload['booking_date'];

        if (! in_array($startTime, $configuredSlots, true)) {
            throw ValidationException::withMessages(['start_time' => 'That time is not available.']);
        }

        $endTime = $this->computeEndTime($startTime, $service->duration_minutes);
        $superToken = $this->client->superuserToken();

        $timeSlotId = $this->findOrCreateTimeSlot($superToken, $bookingDate, $startTime, $endTime);
        $this->assertSlotIsFree($superToken, $timeSlotId);

        $petId = $this->resolvePetId($user, $payload);

        $taxiEnabled = (bool) ($payload['taxi_enabled'] ?? false);
        $taxiFee = $taxiEnabled ? (float) config('booking.taxi_fee', 15.0) : 0.0;
        $totalPrice = round($service->price + $taxiFee, 2);

        $bookingRecord = $this->client->createRecord('cg_bookings', [
            'user' => $user->id,
            'pet' => $petId,
            'service' => $service->id,
            'time_slot' => $timeSlotId,
            'status' => 'pending',
            'payment_method' => 'online',
            'payment_status' => 'unpaid',
            'total_price' => $totalPrice,
            'notes' => (string) ($payload['notes'] ?? ''),
        ], $user->pocketbase_token);

        if ($taxiEnabled) {
            $this->client->createRecord('cg_taxi_requests', [
                'booking' => $bookingRecord['id'],
                'pickup_address' => (string) ($payload['pickup_address'] ?? ''),
                'status' => 'pending',
                'scheduled_at' => Carbon::parse($bookingDate.' '.$startTime)->toIso8601String(),
            ], $user->pocketbase_token);
        }

        $expanded = $this->client->getRecord(
            'cg_bookings',
            (string) $bookingRecord['id'],
            $user->pocketbase_token,
            'pet,service,time_slot,cg_taxi_requests_via_booking',
        );

        return Booking::fromRecord($expanded);
    }

    private function computeEndTime(string $startTime, int $durationMinutes): string
    {
        $minutes = max(30, $durationMinutes ?: 60);
        return Carbon::parse('2000-01-01 '.$startTime)->addMinutes($minutes)->format('H:i');
    }

    private function findOrCreateTimeSlot(string $superToken, string $date, string $startTime, string $endTime): string
    {
        $existing = $this->client->listRecords('cg_time_slots', $superToken, [
            'filter' => "date~'{$date}' && start_time='{$startTime}'",
            'perPage' => 1,
        ]);

        if (! empty($existing['items'])) {
            return (string) $existing['items'][0]['id'];
        }

        $created = $this->client->createRecord('cg_time_slots', [
            'date' => $date,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'is_available' => true,
            'max_bookings' => 1,
        ], $superToken);

        return (string) $created['id'];
    }

    private function assertSlotIsFree(string $superToken, string $timeSlotId): void
    {
        $bookings = $this->client->listRecords('cg_bookings', $superToken, [
            'filter' => "time_slot='{$timeSlotId}' && status!='cancelled'",
            'perPage' => 1,
        ]);

        if (! empty($bookings['items'])) {
            throw ValidationException::withMessages([
                'start_time' => 'That time slot was just booked. Please pick another.',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolvePetId(User $user, array $payload): string
    {
        $existingPetId = (string) ($payload['pet_id'] ?? '');
        if ($existingPetId !== '') {
            $pet = $this->client->getRecord('cg_pets', $existingPetId, $user->pocketbase_token);
            if (($pet['user'] ?? null) !== $user->id) {
                throw ValidationException::withMessages(['pet_id' => 'Invalid pet selection.']);
            }
            return (string) $pet['id'];
        }

        $newPetName = trim((string) ($payload['new_pet_name'] ?? ''));
        if ($newPetName === '') {
            throw ValidationException::withMessages(['pet_id' => 'Please choose a pet or add a new one.']);
        }

        $created = $this->client->createRecord('cg_pets', array_filter([
            'user' => $user->id,
            'name' => $newPetName,
            'breed' => $payload['new_pet_breed'] ?? null,
            'age' => isset($payload['new_pet_age']) && $payload['new_pet_age'] !== '' ? (int) $payload['new_pet_age'] : null,
            'special_notes' => $payload['new_pet_notes'] ?? null,
        ], static fn ($v) => $v !== null && $v !== ''), $user->pocketbase_token);

        return (string) $created['id'];
    }
}
