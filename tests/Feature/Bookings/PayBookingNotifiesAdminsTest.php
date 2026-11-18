<?php

namespace Tests\Feature\Bookings;

use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayBookingNotifiesAdminsTest extends TestCase
{
    public function test_paid_booking_stays_pending_and_every_admin_is_notified(): void
    {
        Cache::put('pocketbase:superuser_token', 'super');
        Http::preventStrayRequests();

        $booking = [
            'id' => 'b1', 'user' => 'u1', 'status' => 'pending', 'payment_status' => 'unpaid', 'notes' => '',
            'expand' => [
                'pet' => ['id' => 'p1', 'name' => 'Danni'],
                'service' => ['id' => 's1', 'name' => 'Full Grooming'],
                'time_slot' => ['id' => 't1', 'date' => '2026-11-17 00:00:00.000Z', 'start_time' => '09:00'],
            ],
        ];

        Http::fake(fn (Request $request) => match (true) {
            str_contains($request->url(), '/cg_bookings/records/b1') => Http::response(
                $request->method() === 'PATCH' ? [...$booking, ...$request->data()] : $booking
            ),
            str_contains($request->url(), '/cg_users/records') => Http::response([
                'items' => [['id' => 'a1', 'role' => 'admin'], ['id' => 'a2', 'role' => 'admin']],
            ]),
            str_contains($request->url(), '/cg_notifications/records') => Http::response(['id' => 'n-'.$request['user'], ...$request->data()]),
        });

        $this->actingAs(User::fromRecord(['id' => 'u1', 'name' => 'ARMAND'], 'token'))
            ->post('/book/b1/payment', ['payment_channel' => 'maybank2u'])
            ->assertRedirect(route('bookings.confirmation', 'b1'));

        Http::assertSent(fn (Request $r) => $r->method() === 'PATCH'
            && str_contains($r->url(), '/cg_bookings/records/b1')
            && $r['payment_status'] === 'paid'
            && ! array_key_exists('status', $r->data()));

        $notified = Http::recorded(fn (Request $r) => $r->method() === 'POST' && str_contains($r->url(), '/cg_notifications/records'))
            ->map(fn (array $pair) => $pair[0])
            ->values();

        $this->assertSame(['a1', 'a2'], $notified->map(fn (Request $r) => $r['user'])->all());
        $notified->each(function (Request $r) {
            $this->assertSame('booking_new', $r['type']);
            $this->assertSame('/admin/bookings/b1', $r['link']);
            $this->assertStringContainsString('Danni · Full Grooming · 17 Nov 09:00', $r['body']);
        });
    }
}
