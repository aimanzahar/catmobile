<?php

namespace App\Actions\Profile;

use App\Auth\PocketBaseGuard;
use App\Models\User;
use App\Services\PocketBase\PocketBaseClient;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class UpdateProfile
{
    public function __construct(private readonly PocketBaseClient $client) {}

    public function handle(User $user, array $attributes): User
    {
        if ($user->pocketbase_token === null) {
            throw new RuntimeException('Authenticated user is missing a PocketBase token.');
        }

        unset($attributes['avatar_native_path']);

        $files = [];
        if (isset($attributes['avatar']) && $attributes['avatar'] instanceof UploadedFile) {
            $files['avatar'] = $attributes['avatar'];
            unset($attributes['avatar']);
            Log::info('[UpdateProfile] forwarding avatar to PocketBase', [
                'name' => $files['avatar']->getClientOriginalName(),
                'size' => $files['avatar']->getSize(),
            ]);
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
