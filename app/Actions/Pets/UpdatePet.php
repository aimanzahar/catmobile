<?php

namespace App\Actions\Pets;

use App\Models\Pet;
use App\Models\User;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class UpdatePet
{
    public function __construct(private readonly PocketBaseClient $client) {}

    public function handle(User $user, Pet $pet, array $attributes): Pet
    {
        if ($user->pocketbase_token === null) {
            throw new RuntimeException('Authenticated user is missing a PocketBase token.');
        }

        $files = [];
        if (isset($attributes['image']) && $attributes['image'] instanceof UploadedFile) {
            $files['image'] = $attributes['image'];
            unset($attributes['image']);
        } else {
            unset($attributes['image']);
        }

        $record = $this->client->updateRecord(
            'cg_pets',
            $pet->id,
            $attributes,
            $user->pocketbase_token,
            $files,
        );

        return Pet::fromRecord($record);
    }
}
