<?php

namespace App\Actions\Bookings;

use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Support\Carbon;

class ListAvailableTimeSlots
{
    public function __construct(private readonly PocketBaseClient $client) {}

    /**
     * @return array<int, string> List of available start_time strings (HH:MM) for the given date.
     */
    public function handle(string $date): array
    {
        $configured = (array) config('booking.slots', []);
        $today = Carbon::today();
        $target = Carbon::parse($date)->startOfDay();

        $minLeadMinutes = (int) config('booking.min_lead_minutes', 60);
        $now = Carbon::now();

        $taken = $this->takenSlotStartTimes($date);

        return array_values(array_filter($configured, function (string $slot) use ($target, $today, $now, $minLeadMinutes, $taken) {
            if (in_array($slot, $taken, true)) {
                return false;
            }

            if ($target->isSameDay($today)) {
                $slotAt = Carbon::parse($target->toDateString().' '.$slot);
                if ($slotAt->lessThan($now->copy()->addMinutes($minLeadMinutes))) {
                    return false;
                }
            }

            return true;
        }));
    }

    /**
     * @return array<int, string>
     */
    private function takenSlotStartTimes(string $date): array
    {
        $superToken = $this->client->superuserToken();

        $slotsResponse = $this->client->listRecords('cg_time_slots', $superToken, [
            'filter' => "date~'{$date}'",
            'perPage' => 200,
        ]);

        $slotsByStart = [];
        foreach ($slotsResponse['items'] ?? [] as $slot) {
            $slotsByStart[(string) $slot['id']] = (string) ($slot['start_time'] ?? '');
        }

        if ($slotsByStart === []) {
            return [];
        }

        $slotIds = array_keys($slotsByStart);
        $slotIdFilter = implode('||', array_map(fn (string $id) => "time_slot='{$id}'", $slotIds));

        $bookingsResponse = $this->client->listRecords('cg_bookings', $superToken, [
            'filter' => "({$slotIdFilter}) && status!='cancelled'",
            'perPage' => 200,
        ]);

        $taken = [];
        foreach ($bookingsResponse['items'] ?? [] as $booking) {
            $slotId = (string) ($booking['time_slot'] ?? '');
            if (isset($slotsByStart[$slotId])) {
                $taken[] = $slotsByStart[$slotId];
            }
        }

        return array_values(array_unique($taken));
    }
}
