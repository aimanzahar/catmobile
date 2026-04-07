<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_landing_page_renders_seeded_services(): void
    {
        $this->seed(DatabaseSeeder::class);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('Basic Grooming');
        $response->assertSee('Full Grooming');
    }

    public function test_the_database_seeder_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('services', 4);
        $this->assertDatabaseCount('users', 1);
        $this->assertSame('test@example.com', User::first()?->email);
    }
}
