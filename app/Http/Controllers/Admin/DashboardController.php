<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly PocketBaseClient $client) {}

    public function index(): View
    {
        $superToken = $this->client->superuserToken();

        $today = Carbon::today()->toDateString();

        $allResp = $this->client->listRecords('cg_bookings', $superToken, [
            'sort' => '-id',
            'expand' => 'pet,service,time_slot,user',
            'perPage' => 200,
        ]);

        $bookings = collect($allResp['items'] ?? [])
            ->map(fn (array $r) => Booking::fromRecord($r));

        $counts = [
            'pending' => $bookings->where('status', 'pending')->count(),
            'confirmed' => $bookings->where('status', 'confirmed')->count(),
            'in_progress' => $bookings->where('status', 'in_progress')->count(),
            'completed' => $bookings->where('status', 'completed')->count(),
            'cancelled' => $bookings->where('status', 'cancelled')->count(),
        ];

        $todayBookings = $bookings->filter(function (Booking $b) use ($today) {
            $date = $b->timeSlot?->date?->toDateString();
            return $date === $today && $b->status !== 'cancelled';
        })->sortBy(fn (Booking $b) => $b->timeSlot?->start_time ?? '')->values();

        return view('admin.dashboard', [
            'counts' => $counts,
            'todayBookings' => $todayBookings,
            'totalBookings' => $bookings->count(),
            'activeSection' => 'admin-dashboard',
        ]);
    }
}
