<?php

namespace App\Actions\Auth;

use App\Auth\PocketBaseGuard;
use App\Models\User;
use App\Services\PocketBase\Exceptions\PocketBaseAuthException;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Validation\ValidationException;

class AuthenticateUser
{
    public function __construct(private readonly PocketBaseClient $client) {}

    /**
     * @return array{user: User, token: string}
     */
    public function handle(array $credentials): array
    {
        try {
            $result = $this->client->authWithPassword(
                PocketBaseGuard::USERS_COLLECTION,
                $credentials['email'],
                $credentials['password'],
            );
        } catch (PocketBaseAuthException) {
            throw ValidationException::withMessages([
                'email' => __('The provided credentials are incorrect.'),
            ]);
        }

        $user = User::fromRecord($result['record'], $result['token']);

        return ['user' => $user, 'token' => $result['token']];
    }
}
