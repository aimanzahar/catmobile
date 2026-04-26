<?php

namespace App\Actions\Profile;

use App\Auth\PocketBaseGuard;
use App\Models\User;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class UpdateProfile
{
    public function __construct(private readonly PocketBaseClient $client) {}

    public function handle(User $user, array $attributes): User
    {
        if ($user->pocketbase_token === null) {
            throw new RuntimeException('Authenticated user is missing a PocketBase token.');
        }

        $files = [];
        if (isset($attributes['avatar']) && $attributes['avatar'] instanceof UploadedFile) {
            $files['avatar'] = $attributes['avatar'];
            unset($attributes['avatar']);
        } else {
            unset($attributes['avatar']);
        }

        $record = $this->client->updateRecord(
            PocketBaseGuard::USERS_COLLECTION,
            $user->id,
            $attributes,
            $user->pocketbase_token,
            $files,
        );

        return User::fromRecord($record, $user->pocketbase_token);
    }
}
