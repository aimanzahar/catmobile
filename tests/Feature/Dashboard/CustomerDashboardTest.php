<?php

namespace Tests\Feature\Dashboard;

use App\Models\Booking;
use App\Models\Pet;
use App\Models\Service;
use App\Models\TaxiRequest;
use App\Models\TimeSlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_user_dashboard_shows_empty_states(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('No upcoming bookings yet');
        $response->assertSee('No booking history yet');
        $response->assertSee('No pets added yet');
    }

    public function test_dashboard_only_shows_authenticated_users_data_and_splits_upcoming_from_history(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $visiblePet = Pet::create([
            'user_id' => $user->id,
            'name' => 'Milo',
        ]);
        $hiddenPet = Pet::create([
            'user_id' => $otherUser->id,
            'name' => 'Ghost',
        ]);

        $service = $this->createService();

        $upcomingSlot = TimeSlot::create([
            'date' => now()->addDay()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'is_available' => true,
            'max_bookings' => 3,
        ]);

        $historySlot = TimeSlot::create([
            'date' => now()->subDay()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'is_available' => true,
            'max_bookings' => 3,
        ]);

        $upcomingBooking = Booking::create([
            'user_id' => $user->id,
            'pet_id' => $visiblePet->id,
            'service_id' => $service->id,
            'time_slot_id' => $upcomingSlot->id,
            'status' => 'confirmed',
            'payment_method' => 'cash',
            'payment_status' => 'unpaid',
            'total_price' => 30,
        ]);

        Booking::create([
            'user_id' => $user->id,
            'pet_id' => $visiblePet->id,
            'service_id' => $service->id,
            'time_slot_id' => $historySlot->id,
            'status' => 'completed',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'total_price' => 30,
        ]);

        TaxiRequest::create([
            'booking_id' => $upcomingBooking->id,
            'pickup_address' => '123 Cat Street',
            'status' => 'approved',
        ]);

        Booking::create([
            'user_id' => $otherUser->id,
            'pet_id' => $hiddenPet->id,
            'service_id' => $service->id,
            'time_slot_id' => $upcomingSlot->id,
            'status' => 'confirmed',
            'payment_method' => 'cash',
            'payment_status' => 'unpaid',
            'total_price' => 30,
        ]);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSee('Milo');
        $response->assertSee('Basic Grooming');
        $response->assertSee('Upcoming Bookings');
        $response->assertSee('Booking History');
        $response->assertDontSee('Ghost');
    }

    public function test_user_can_manage_their_own_pets_and_profile_from_the_web_dashboard(): void
    {
        $user = User::factory()->create([
            'name' => 'Before Name',
            'email' => 'before@example.com',
        ]);
        $pet = Pet::create([
            'user_id' => $user->id,
            'name' => 'Luna',
        ]);

        $this->actingAs($user)->post('/pets', [
            'name' => 'Neko',
            'breed' => 'Persian',
            'age' => 3,
            'weight' => 4.25,
            'special_notes' => 'Needs gentle brushing',
        ])->assertRedirect('/dashboard');

        $this->assertDatabaseHas('pets', [
            'user_id' => $user->id,
            'name' => 'Neko',
        ]);

        $this->actingAs($user)->patch("/pets/{$pet->id}", [
            'name' => 'Luna Updated',
            'breed' => 'British Shorthair',
            'age' => 4,
            'weight' => 4.75,
            'special_notes' => 'Updated notes',
        ])->assertRedirect('/dashboard');

        $this->assertDatabaseHas('pets', [
            'id' => $pet->id,
            'name' => 'Luna Updated',
        ]);

        $this->actingAs($user)->patch('/profile', [
            'name' => 'After Name',
            'email' => 'after@example.com',
        ])->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'After Name',
            'email' => 'after@example.com',
        ]);

        $this->actingAs($user)->delete("/pets/{$pet->id}")
            ->assertRedirect('/dashboard');

        $this->assertDatabaseMissing('pets', [
            'id' => $pet->id,
        ]);
    }

    public function test_api_dashboard_and_profile_endpoints_are_scoped_to_the_authenticated_user(): void
    {
        $user = User::factory()->create([
            'name' => 'Owner One',
            'email' => 'owner-one@example.com',
        ]);
        $otherUser = User::factory()->create();

        $pet = Pet::create([
            'user_id' => $user->id,
            'name' => 'Pixel',
        ]);
        Pet::create([
            'user_id' => $otherUser->id,
            'name' => 'Secret',
        ]);

        $service = $this->createService();
        $slot = TimeSlot::create([
            'date' => now()->addDay()->toDateString(),
            'start_time' => '11:00:00',
            'end_time' => '12:00:00',
            'is_available' => true,
            'max_bookings' => 3,
        ]);

        Booking::create([
            'user_id' => $user->id,
            'pet_id' => $pet->id,
            'service_id' => $service->id,
            'time_slot_id' => $slot->id,
            'status' => 'pending',
            'payment_method' => 'cash',
            'payment_status' => 'unpaid',
            'total_price' => 30,
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        $dashboardResponse = $this->withToken($token)->getJson('/api/dashboard');

        $dashboardResponse->assertOk();
        $dashboardResponse->assertJsonPath('user.email', 'owner-one@example.com');
        $dashboardResponse->assertJsonCount(1, 'pets');
        $dashboardResponse->assertJsonCount(1, 'upcoming_bookings');
        $dashboardResponse->assertJsonMissing(['name' => 'Secret']);

        $this->withToken($token)->patchJson('/api/profile', [
            'name' => 'Owner One Updated',
            'email' => 'owner-one-updated@example.com',
        ])->assertOk();

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'owner-one-updated@example.com',
        ]);
    }

    private function createService(): Service
    {
        return Service::create([
            'name' => 'Basic Grooming',
            'slug' => 'basic-grooming-'.Str::lower(Str::random(6)),
            'description' => 'Test service',
            'price' => 30,
            'duration_minutes' => 45,
            'icon' => 'scissors',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }
}
