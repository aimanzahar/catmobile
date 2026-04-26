<?php

namespace App\Actions\Pets;

use App\Models\Pet;
use App\Models\User;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CreatePet
{
    public function __construct(private readonly PocketBaseClient $client) {}

    public function handle(User $user, array $attributes): Pet
    {
        if ($user->pocketbase_token === null) {
            throw new RuntimeException('Authenticated user is missing a PocketBase token.');
        }

        unset($attributes['image_native_path']);

        $files = [];
        if (isset($attributes['image']) && $attributes['image'] instanceof UploadedFile) {
            $files['image'] = $attributes['image'];
            unset($attributes['image']);
            Log::info('[CreatePet] forwarding image to PocketBase', [
                'name' => $files['image']->getClientOriginalName(),
                'size' => $files['image']->getSize(),
            ]);
        } else {
            unset($attributes['image']);
        }

        $record = $this->client->createRecord(
            'cg_pets',
            array_merge($attributes, ['user' => $user->id]),
            $user->pocketbase_token,
            $files,
        );

        return Pet::fromRecord($record);
    }
}
