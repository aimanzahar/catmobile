<?php

namespace App\Actions\Dashboard;

use App\Models\Booking;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class BuildCustomerDashboard
{
    public function handle(User $user): array
    {
        $bookings = $user->bookings()
            ->with([
                'pet:id,name',
                'service:id,name,slug,price,duration_minutes',
                'timeSlot:id,date,start_time,end_time',
                'taxiRequest:id,booking_id,pickup_address,status,scheduled_at',
            ])
            ->get();

        $now = now();

        $upcomingBookings = $bookings
            ->filter(fn (Booking $booking) => $this->isUpcoming($booking, $now))
            ->sortBy(fn (Booking $booking) => $this->slotStartsAt($booking)?->timestamp ?? PHP_INT_MAX)
            ->values();

        $bookingHistory = $bookings
            ->reject(fn (Booking $booking) => $this->isUpcoming($booking, $now))
            ->sortByDesc(fn (Booking $booking) => $this->slotStartsAt($booking)?->timestamp ?? 0)
            ->values();

        return [
            'user' => $user->loadMissing('pets'),
            'pets' => $user->pets()->orderBy('name')->get(),
            'upcoming_bookings' => $upcomingBookings,
            'booking_history' => $bookingHistory,
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
        if (! $booking->relationLoaded('timeSlot') || $booking->timeSlot === null) {
            return null;
        }

        return Carbon::parse(sprintf(
            '%s %s',
            $booking->timeSlot->date->toDateString(),
            $booking->timeSlot->start_time,
        ));
    }
}
