<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_register_returns_a_token_and_user_payload(): void
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Api User',
            'email' => 'api-user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('user.email', 'api-user@example.com');
        $response->assertJsonStructure([
            'token',
            'user' => ['id', 'name', 'email'],
        ]);
        $this->assertDatabaseHas('personal_access_tokens', [
            'tokenable_type' => User::class,
        ]);
    }

    public function test_api_login_returns_a_token_and_user_payload(): void
    {
        User::factory()->create([
            'name' => 'Api Login User',
            'email' => 'login-api@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login-api@example.com',
            'password' => 'password123',
        ]);

        $response->assertOk();
        $response->assertJsonPath('user.email', 'login-api@example.com');
        $response->assertJsonStructure([
            'token',
            'user' => ['id', 'name', 'email'],
        ]);
    }

    public function test_api_protected_endpoints_require_authentication(): void
    {
        $this->getJson('/api/me')->assertUnauthorized();
        $this->getJson('/api/dashboard')->assertUnauthorized();
        $this->postJson('/api/logout')->assertUnauthorized();
    }

    public function test_api_logout_revokes_the_current_token(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('mobile')->plainTextToken;

        $response = $this->withToken($token)->postJson('/api/logout');

        $response->assertOk();
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_api_login_rejects_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'login-api@example.com',
            'password' => Hash::make('password123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'login-api@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertUnauthorized();
        $response->assertJsonValidationErrors('email');
    }
}
