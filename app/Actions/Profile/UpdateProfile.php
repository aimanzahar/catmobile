<?php

namespace App\Actions\Profile;

use App\Auth\PocketBaseGuard;
use App\Models\User;
use App\Services\PocketBase\PocketBaseClient;
use RuntimeException;

class UpdateProfile
{
    public function __construct(private readonly PocketBaseClient $client) {}

    public function handle(User $user, array $attributes): User
    {
        if ($user->pocketbase_token === null) {
            throw new RuntimeException('Authenticated user is missing a PocketBase token.');
        }

        $record = $this->client->updateRecord(
            PocketBaseGuard::USERS_COLLECTION,
            $user->id,
            $attributes,
            $user->pocketbase_token,
        );

        return User::fromRecord($record, $user->pocketbase_token);
    }
}
