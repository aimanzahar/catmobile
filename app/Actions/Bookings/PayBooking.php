<?php

namespace App\Actions\Bookings;

use App\Models\Booking;
use App\Models\User;
use App\Services\PocketBase\Exceptions\PocketBaseNotFoundException;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PayBooking
{
    public function __construct(private readonly PocketBaseClient $client) {}

    public function handle(User $user, string $bookingId, string $channelCode): Booking
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

        if (($record['payment_status'] ?? null) === 'paid') {
            throw ValidationException::withMessages(['payment' => 'This booking is already paid.']);
        }

        $channelLabel = $this->resolveChannelLabel($channelCode);
        $existingNotes = trim((string) ($record['notes'] ?? ''));
        $paymentNote = "[Paid via {$channelLabel}]";
        $notes = $existingNotes === '' ? $paymentNote : $existingNotes."\n".$paymentNote;

        $updated = $this->client->updateRecord('cg_bookings', $bookingId, [
            'status' => 'confirmed',
            'payment_method' => 'online',
            'payment_status' => 'paid',
            'notes' => $notes,
        ], $user->pocketbase_token);

        $expanded = $this->client->getRecord(
            'cg_bookings',
            (string) $updated['id'],
            $user->pocketbase_token,
            'pet,service,time_slot,cg_taxi_requests_via_booking',
        );

        return Booking::fromRecord($expanded);
    }

    private function resolveChannelLabel(string $code): string
    {
        $channels = (array) config('booking.payment_channels', []);
        foreach ($channels as $group) {
            foreach ((array) $group as $channel) {
                if (($channel['code'] ?? null) === $code) {
                    return (string) $channel['label'];
                }
            }
        }

        throw ValidationException::withMessages(['payment_channel' => 'Unsupported payment channel.']);
    }
}
