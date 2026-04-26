<?php

namespace App\Http\Controllers;

use App\Actions\Bookings\CreateBooking;
use App\Actions\Bookings\ListAvailableTimeSlots;
use App\Actions\Bookings\PayBooking;
use App\Http\Requests\Booking\PayBookingRequest;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Models\Booking;
use App\Models\Pet;
use App\Models\Service;
use App\Services\PocketBase\Exceptions\PocketBaseNotFoundException;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookingController extends Controller
{
    public function __construct(private readonly PocketBaseClient $client) {}

    public function index(Request $request): View
    {
        $services = $this->fetchActiveServices();

        return view('bookings.index', [
            'services' => $services,
            'activeSection' => 'overview',
        ]);
    }

    public function create(Request $request, string $service): View
    {
        $serviceModel = $this->findServiceBySlug($service);
        $pets = $this->fetchUserPets($request);

        return view('bookings.create', [
            'service' => $serviceModel,
            'pets' => $pets,
            'maxDate' => Carbon::today()->addDays((int) config('booking.max_advance_days', 30))->toDateString(),
            'minDate' => Carbon::today()->toDateString(),
            'taxiFee' => (float) config('booking.taxi_fee', 15.0),
            'activeSection' => 'overview',
        ]);
    }

    public function store(StoreBookingRequest $request, CreateBooking $createBooking): RedirectResponse
    {
        $service = $this->findServiceBySlug((string) $request->validated('service_slug'));

        $booking = $createBooking->handle($request->user(), $service, $request->validated());

        return redirect()->route('bookings.payment', $booking->id);
    }

    public function payment(Request $request, string $booking): View
    {
        $bookingModel = $this->loadOwnedBooking($request, $booking);

        return view('bookings.payment', [
            'booking' => $bookingModel,
            'channels' => (array) config('booking.payment_channels', []),
            'activeSection' => 'overview',
        ]);
    }

    public function processPayment(PayBookingRequest $request, string $booking, PayBooking $payBooking): RedirectResponse
    {
        $payBooking->handle($request->user(), $booking, (string) $request->validated('payment_channel'));

        return redirect()->route('bookings.confirmation', $booking);
    }

    public function cancelPayment(Request $request, string $booking): RedirectResponse
    {
        $this->loadOwnedBooking($request, $booking);

        return redirect()->route('dashboard')
            ->with('status', 'Payment cancelled. Your booking is still pending — pay anytime from the dashboard.');
    }

    public function confirmation(Request $request, string $booking): View
    {
        $bookingModel = $this->loadOwnedBooking($request, $booking);

        return view('bookings.confirmation', [
            'booking' => $bookingModel,
            'activeSection' => 'overview',
        ]);
    }

    private function fetchActiveServices()
    {
        $response = $this->client->listRecords('cg_services', null, [
            'filter' => 'is_active=true',
            'sort' => 'sort_order',
            'perPage' => 200,
        ]);

        return collect($response['items'] ?? [])
            ->map(fn (array $record) => Service::fromRecord($record));
    }

    private function findServiceBySlug(string $slug): Service
    {
        $response = $this->client->listRecords('cg_services', null, [
            'filter' => "slug='{$slug}' && is_active=true",
            'perPage' => 1,
        ]);

        $items = $response['items'] ?? [];
        if (empty($items)) {
            throw new NotFoundHttpException();
        }

        return Service::fromRecord($items[0]);
    }

    private function fetchUserPets(Request $request)
    {
        $user = $request->user();
        $response = $this->client->listRecords('cg_pets', $user->pocketbase_token, [
            'filter' => "user='{$user->id}'",
            'sort' => 'name',
            'perPage' => 200,
        ]);

        return collect($response['items'] ?? [])
            ->map(fn (array $record) => Pet::fromRecord($record))
            ->values();
    }

    private function loadOwnedBooking(Request $request, string $bookingId): Booking
    {
        $user = $request->user();

        try {
            $record = $this->client->getRecord(
                'cg_bookings',
                $bookingId,
                $user->pocketbase_token,
                'pet,service,time_slot,cg_taxi_requests_via_booking',
            );
        } catch (PocketBaseNotFoundException) {
            throw new NotFoundHttpException();
        }

        if (($record['user'] ?? null) !== $user->id) {
            throw new NotFoundHttpException();
        }

        return Booking::fromRecord($record);
    }
}
