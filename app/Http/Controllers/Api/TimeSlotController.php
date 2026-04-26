<?php

namespace App\Http\Controllers\Api;

use App\Actions\Bookings\ListAvailableTimeSlots;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TimeSlotController extends Controller
{
    public function available(Request $request, ListAvailableTimeSlots $listAvailableTimeSlots): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $date = Carbon::parse((string) $request->query('date'))->toDateString();
        $slots = $listAvailableTimeSlots->handle($date);

        return response()->json([
            'date' => $date,
            'slots' => $slots,
        ]);
    }
}
