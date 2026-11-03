<?php

namespace Tests\Feature\Pets;

use App\Models\User;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DeletePetTest extends TestCase
{
    public function test_pet_with_active_bookings_is_not_deleted(): void
    {
        Http::preventStrayRequests();
        Http::fake([
            '*/api/collections/cg_pets/records/p1' => Http::response(['id' => 'p1', 'user' => 'u1', 'name' => 'Milo']),
            '*/api/collections/cg_bookings/records*' => Http::response(['items' => [['id' => 'b1', 'status' => 'confirmed']]]),
        ]);

        $this->actingAs(User::fromRecord(['id' => 'u1', 'name' => 'Owner'], 'token'))
            ->delete('/pets/p1')
            ->assertRedirect(route('dashboard', ['section' => 'pets']))
            ->assertSessionHas('error');

        Http::assertNotSent(fn (Request $request) => $request->method() === 'DELETE');
    }
}
