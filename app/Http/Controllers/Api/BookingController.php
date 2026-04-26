<?php

namespace App\Http\Controllers\Api;

use App\Actions\Bookings\CancelBooking;
use App\Actions\Bookings\CreateBooking;
use App\Actions\Bookings\PayBooking;
use App\Http\Controllers\Controller;
use App\Http\Requests\Booking\PayBookingRequest;
use App\Http\Requests\Booking\StoreBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Service;
use App\Services\PocketBase\Exceptions\PocketBaseNotFoundException;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookingController extends Controller
{
    public function __construct(private readonly PocketBaseClient $client) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();
        $response = $this->client->listRecords('cg_bookings', $user->pocketbase_token, [
            'filter' => "user='{$user->id}'",
            'expand' => 'pet,service,time_slot,cg_taxi_requests_via_booking',
            'perPage' => 200,
        ]);

        $bookings = collect($response['items'] ?? [])
            ->map(fn (array $record) => Booking::fromRecord($record));

        return BookingResource::collection($bookings);
    }

    public function store(StoreBookingRequest $request, CreateBooking $createBooking): JsonResponse
    {
        $service = $this->findServiceBySlug((string) $request->validated('service_slug'));
        $booking = $createBooking->handle($request->user(), $service, $request->validated());

        return response()->json(new BookingResource($booking), 201);
    }

    public function show(Request $request, string $booking): BookingResource
    {
        return new BookingResource($this->loadOwnedBooking($request, $booking));
    }

    public function pay(PayBookingRequest $request, string $booking, PayBooking $payBooking): BookingResource
    {
        $updated = $payBooking->handle($request->user(), $booking, (string) $request->validated('payment_channel'));

        return new BookingResource($updated);
    }

    public function cancel(Request $request, string $booking, CancelBooking $cancelBooking): BookingResource
    {
        $updated = $cancelBooking->handle($request->user(), $booking);

        return new BookingResource($updated);
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
