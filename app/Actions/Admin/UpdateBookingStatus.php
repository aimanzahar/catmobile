<?php

namespace App\Actions\Admin;

use App\Actions\Notifications\CreateNotification;
use App\Models\Booking;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class UpdateBookingStatus
{
    public function __construct(
        private readonly PocketBaseClient $client,
        private readonly CreateNotification $createNotification,
    ) {}

    public function handle(string $bookingId, string $newStatus): Booking
    {
        $superToken = $this->client->superuserToken();

        $current = $this->client->getRecord('cg_bookings', $bookingId, $superToken, 'pet,service,time_slot,user,cg_taxi_requests_via_booking');

        $oldStatus = (string) ($current['status'] ?? 'pending');

        if ($oldStatus === $newStatus) {
            return Booking::fromRecord($current);
        }

        $updated = $this->client->updateRecord('cg_bookings', $bookingId, [
            'status' => $newStatus,
        ], $superToken);

        $expanded = $this->client->getRecord('cg_bookings', $bookingId, $superToken, 'pet,service,time_slot,user,cg_taxi_requests_via_booking');
        $booking = Booking::fromRecord($expanded);

        $this->dispatchCustomerNotification($booking, $newStatus);

        return $booking;
    }

    private function dispatchCustomerNotification(Booking $booking, string $newStatus): void
    {
        if ($booking->userId === null || $booking->userId === '') {
            return;
        }

        [$type, $title, $body] = match ($newStatus) {
            'confirmed' => [
                'booking_confirmed',
                'Booking confirmed',
                'Your booking has been confirmed by the shop.',
            ],
            'in_progress' => [
                'booking_in_progress',
                'Grooming in progress',
                'Your cat is being groomed right now.',
            ],
            'completed' => [
                'booking_completed',
                'All done! 🎉',
                'Your cat is freshly groomed and ready for pickup.',
            ],
            'cancelled' => [
                'booking_cancelled',
                'Booking cancelled',
                'Your booking has been cancelled by the shop.',
            ],
            default => [null, null, null],
        };

        if ($type === null) {
            return;
        }

        try {
            $this->createNotification->handle(
                userId: $booking->userId,
                type: $type,
                title: $title,
                body: $body,
                link: '/book/'.$booking->id,
                relatedId: $booking->id,
            );
        } catch (\Throwable $e) {
            Log::warning('Failed to dispatch notification for booking status change', [
                'booking' => $booking->id,
                'status' => $newStatus,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
