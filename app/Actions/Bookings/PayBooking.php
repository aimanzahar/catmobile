<?php

namespace App\Actions\Bookings;

use App\Actions\Notifications\CreateNotification;
use App\Models\Booking;
use App\Models\User;
use App\Services\PocketBase\Exceptions\PocketBaseNotFoundException;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PayBooking
{
    public function __construct(
        private readonly PocketBaseClient $client,
        private readonly CreateNotification $createNotification,
    ) {}

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

        // Status stays 'pending': the shop confirms it from the admin side.
        $updated = $this->client->updateRecord('cg_bookings', $bookingId, [
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

        $booking = Booking::fromRecord($expanded);

        $this->createNotification->notifyAdmins(
            type: 'booking_new',
            title: 'New booking from '.$user->name,
            body: $booking->summary().' (paid, waiting for your confirmation)',
            link: '/admin/bookings/'.$booking->id,
            relatedId: $booking->id,
        );

        return $booking;
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
