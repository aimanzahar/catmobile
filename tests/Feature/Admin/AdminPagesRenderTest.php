<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminPagesRenderTest extends TestCase
{
    public function test_admin_dashboard_and_bookings_list_render_a_paid_pending_booking(): void
    {
        Cache::put('pocketbase:superuser_token', 'super');
        Http::preventStrayRequests();
        Http::fake([
            '*/api/collections/cg_bookings/records*' => Http::response(['items' => [[
                'id' => 'b1', 'status' => 'pending', 'payment_status' => 'paid', 'created' => '2026-11-18 02:24:37.217Z',
                'expand' => [
                    'pet' => ['id' => 'p1', 'name' => 'Danni'],
                    'service' => ['id' => 's1', 'name' => 'Full Grooming'],
                    'time_slot' => ['id' => 't1', 'date' => '2026-11-18 00:00:00.000Z', 'start_time' => '17:00'],
                    'user' => ['id' => 'u1', 'name' => 'ARMAND', 'email' => 'armand@example.com'],
                ],
            ]]]),
        ]);

        $admin = User::fromRecord(['id' => 'a1', 'name' => 'Shop Admin', 'role' => 'admin'], 'token');

        $this->actingAs($admin)->get('/admin')
            ->assertOk()
            ->assertSee(route('admin.bookings.index', ['status' => 'pending']), false);

        $this->actingAs($admin)->get('/admin/bookings?status=pending')
            ->assertOk()
            ->assertSee('Danni')
            ->assertSee('Paid');
    }
}
