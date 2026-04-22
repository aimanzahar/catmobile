<?php

namespace App\Actions\Pets;

use App\Models\Pet;
use App\Models\User;
use App\Services\PocketBase\PocketBaseClient;
use RuntimeException;

class CreatePet
{
    public function __construct(private readonly PocketBaseClient $client) {}

    public function handle(User $user, array $attributes): Pet
    {
        if ($user->pocketbase_token === null) {
            throw new RuntimeException('Authenticated user is missing a PocketBase token.');
        }

        $record = $this->client->createRecord(
            'cg_pets',
            array_merge($attributes, ['user' => $user->id]),
            $user->pocketbase_token,
        );

        return Pet::fromRecord($record);
    }
}
