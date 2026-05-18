<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Admin\UpdateBookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateBookingStatusRequest;
use App\Models\Booking;
use App\Services\PocketBase\Exceptions\PocketBaseNotFoundException;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookingController extends Controller
{
    public function __construct(private readonly PocketBaseClient $client) {}

    public function index(Request $request): View
    {
        $superToken = $this->client->superuserToken();

        $statusFilter = (string) $request->query('status', '');
        $allowedStatuses = ['pending', 'confirmed', 'in_progress', 'completed', 'cancelled'];

        $params = [
            'sort' => '-id',
            'expand' => 'pet,service,time_slot,user',
            'perPage' => 200,
        ];

        if ($statusFilter !== '' && in_array($statusFilter, $allowedStatuses, true)) {
            $params['filter'] = "status='{$statusFilter}'";
        }

        $response = $this->client->listRecords('cg_bookings', $superToken, $params);

        $bookings = collect($response['items'] ?? [])
            ->map(fn (array $r) => Booking::fromRecord($r));

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'statusFilter' => $statusFilter,
            'activeSection' => 'admin-bookings',
        ]);
    }

    public function show(string $booking): View
    {
        $superToken = $this->client->superuserToken();

        try {
            $record = $this->client->getRecord('cg_bookings', $booking, $superToken, 'pet,service,time_slot,user,cg_taxi_requests_via_booking');
        } catch (PocketBaseNotFoundException) {
            throw new NotFoundHttpException();
        }

        return view('admin.bookings.show', [
            'booking' => Booking::fromRecord($record),
            'activeSection' => 'admin-bookings',
        ]);
    }

    public function updateStatus(UpdateBookingStatusRequest $request, string $booking, UpdateBookingStatus $updater): RedirectResponse
    {
        $newStatus = (string) $request->validated('status');

        try {
            $updater->handle($booking, $newStatus);
        } catch (PocketBaseNotFoundException) {
            throw new NotFoundHttpException();
        }

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('status', "Status updated to {$newStatus}.");
    }
}
