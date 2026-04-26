<?php

namespace App\Actions\Bookings;

use App\Models\Booking;
use App\Models\User;
use App\Services\PocketBase\Exceptions\PocketBaseNotFoundException;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CancelBooking
{
    public function __construct(private readonly PocketBaseClient $client) {}

    public function handle(User $user, string $bookingId): Booking
    {
        if ($user->pocketbase_token === null) {
            throw new RuntimeException('Authenticated user is missing a PocketBase token.');
        }

        try {
            $record = $this->client->getRecord('cg_bookings', $bookingId, $user->pocketbase_token);
        } catch (PocketBaseNotFoundException) {
            throw new NotFoundHttpException();
        }

        if (($record['user'] ?? null) !== $user->id) {
            throw new NotFoundHttpException();
        }

        if (! in_array($record['status'] ?? '', ['pending', 'confirmed'], true)) {
            throw ValidationException::withMessages(['status' => 'Only pending or confirmed bookings can be cancelled.']);
        }

        $updated = $this->client->updateRecord('cg_bookings', $bookingId, [
            'status' => 'cancelled',
        ], $user->pocketbase_token);

        $expanded = $this->client->getRecord(
            'cg_bookings',
            (string) $updated['id'],
            $user->pocketbase_token,
            'pet,service,time_slot,cg_taxi_requests_via_booking',
        );

        return Booking::fromRecord($expanded);
    }
}
