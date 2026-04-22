<?php

namespace App\Actions\Pets;

use App\Models\Pet;
use App\Models\User;
use App\Services\PocketBase\PocketBaseClient;
use RuntimeException;

class UpdatePet
{
    public function __construct(private readonly PocketBaseClient $client) {}

    public function handle(User $user, Pet $pet, array $attributes): Pet
    {
        if ($user->pocketbase_token === null) {
            throw new RuntimeException('Authenticated user is missing a PocketBase token.');
        }

        $record = $this->client->updateRecord(
            'cg_pets',
            $pet->id,
            $attributes,
            $user->pocketbase_token,
        );

        return Pet::fromRecord($record);
    }
}
